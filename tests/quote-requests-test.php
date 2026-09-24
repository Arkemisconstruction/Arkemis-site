<?php
define( 'ABSPATH', __DIR__ );
define( 'HOUR_IN_SECONDS', 3600 );

class WP_Error {
	private $code;
	private $message;
	private $data;
	public function __construct( $code, $message = '', $data = null ) {
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
	public function get_error_code() { return $this->code; }
	public function get_error_data() { return $this->data; }
}

$GLOBALS['remote_response'] = array( 'response' => array( 'code' => 202 ) );
$GLOBALS['remote_calls'] = array();
$GLOBALS['uuid_counter'] = 0;
$GLOBALS['quote_page'] = false;
$GLOBALS['nocache_calls'] = 0;
$GLOBALS['actions_fired'] = array();

function add_action() {}
function do_action( $hook, ...$args ) { $GLOBALS['actions_fired'][] = array( $hook, $args ); }
function is_page( $slug ) { return $GLOBALS['quote_page'] && 'demander-une-soumission' === $slug; }
function nocache_headers() { ++$GLOBALS['nocache_calls']; }
function wp_salt() { return 'test-salt'; }
function wp_generate_uuid4() {
	++$GLOBALS['uuid_counter'];
	return sprintf( '00000000-0000-4000-8000-%012d', $GLOBALS['uuid_counter'] );
}
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( $value ) ); }
function is_email( $value ) { return false !== filter_var( $value, FILTER_VALIDATE_EMAIL ); }
function wp_json_encode( $value ) { return json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); }
function wp_remote_post( $url, $options ) {
	$GLOBALS['remote_calls'][] = array( 'url' => $url, 'options' => $options );
	return $GLOBALS['remote_response'];
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_remote_retrieve_response_code( $response ) { return $response['response']['code'] ?? 0; }

require dirname( __DIR__ ) . '/wp-content/plugins/arkemis-core/inc/quote-requests.php';

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

$values = array(
	'first_name' => 'Marie', 'last_name' => 'Tremblay', 'phone' => '514 555-0101',
	'email' => 'marie@example.test', 'city' => 'Montréal', 'project_type' => 'renovation-interieure',
	'budget' => '20000-40000', 'description' => 'Rénover la cuisine complète.', 'timeline' => '1-3',
	'contact_method' => 'email',
);
$validation = arkemis_core_validate_quote( $values );
check( array() === $validation['errors'], 'un payload valide doit passer la validation' );
$invalid = $values;
$invalid['email'] = 'invalide';
$invalid['description'] = 'court';
check( isset( arkemis_core_validate_quote( $invalid )['errors']['email'] ), 'le courriel invalide doit être refusé' );
check( isset( arkemis_core_validate_quote( $invalid )['errors']['description'] ), 'la description trop courte doit être refusée' );

$token_a = arkemis_core_quote_form_token();
$token_b = arkemis_core_quote_form_token();
$token_c = arkemis_core_quote_form_token();
$id_a = arkemis_core_quote_token_id( $token_a );
$id_b = arkemis_core_quote_token_id( $token_b );
$id_c = arkemis_core_quote_token_id( $token_c );
check( $id_a && $id_b && $id_c, 'chaque chargement doit produire un identifiant valide' );
check( 3 === count( array_unique( array( $id_a, $id_b, $id_c ) ) ), 'les chargements A, B et C doivent avoir des identifiants différents' );
check( $token_b === arkemis_core_quote_form_token( array( 'quote_token' => $token_b ) ), 'une relance du POST B doit conserver le jeton B' );

$GLOBALS['quote_page'] = true;
arkemis_core_quote_disable_cache();
check( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE, 'la page de soumission doit définir DONOTCACHEPAGE' );
check( 1 === $GLOBALS['nocache_calls'], 'les en-têtes WordPress sans cache doivent être envoyés' );
check( in_array( 'litespeed_control_set_nocache', array_column( $GLOBALS['actions_fired'], 0 ), true ), 'LiteSpeed doit recevoir la consigne no-cache' );

putenv( 'ARKEMIS_PLATFORM_API_TOKEN=test-token-not-a-real-secret' );
$request = array_merge( array( 'schema_version' => 1, 'request_id' => '11111111-1111-4111-8111-111111111111', 'organization_id' => 'forbidden' ), $values );
check( true === arkemis_core_send_quote( $request ), 'une réponse 2xx doit réussir' );
$first = $GLOBALS['remote_calls'][0];
check( 'https://plateforme.arkemis.ca/api/public/website-inquiries' === $first['url'], 'endpoint inattendu' );
check( 10 === $first['options']['timeout'], 'timeout HTTP inattendu' );
$headers = $first['options']['headers'];
check( 'test-token-not-a-real-secret' === $headers['X-Arkemis-Website-Token'], 'header X-Arkemis-Website-Token absent' );
check( $request['request_id'] === $headers['X-Arkemis-Idempotency-Key'], 'header X-Arkemis-Idempotency-Key absent' );
check( ! isset( $headers['Authorization'], $headers['Idempotency-Key'], $headers['X-Arkemis-Request-ID'] ), 'anciens headers API encore présents' );
$body = json_decode( $first['options']['body'], true );
check( array( 'first_name', 'last_name', 'phone', 'email', 'city', 'project_type', 'message', 'source' ) === array_keys( $body ), 'clés du payload non conformes au contrat' );
check( 'WEBSITE' === $body['source'], 'source WEBSITE absente' );
check( 0 === strpos( $body['message'], $values['description'] ), 'description non mappée vers message' );
check( false !== strpos( $body['message'], 'Budget approximatif : 20 000 à 40 000 $' ), 'budget absent du message' );
check( false !== strpos( $body['message'], 'Échéancier souhaité : Dans les 1 à 3 mois' ), 'échéancier absent du message' );
check( false !== strpos( $body['message'], 'Méthode de contact préférée : Courriel' ), 'méthode de contact absente du message' );
foreach ( array( 'schema_version', 'request_id', 'budget', 'description', 'timeline', 'contact_method', 'organization_id' ) as $forbidden ) {
	check( ! array_key_exists( $forbidden, $body ), "champ API interdit encore présent : {$forbidden}" );
}
check( false === strpos( $first['options']['body'], 'test-token' ), 'le secret ne doit jamais être dans le JSON' );

arkemis_core_send_quote( $request );
check( $GLOBALS['remote_calls'][0]['options']['headers']['X-Arkemis-Idempotency-Key'] === $GLOBALS['remote_calls'][1]['options']['headers']['X-Arkemis-Idempotency-Key'], 'une relance doit conserver la clé d\'idempotence' );

$GLOBALS['remote_response'] = new WP_Error( 'http_request_failed', 'Operation timed out' );
check( is_wp_error( arkemis_core_send_quote( $request ) ), 'un timeout doit être retourné comme erreur' );
foreach ( array( 400, 422, 500, 503 ) as $status ) {
	$GLOBALS['remote_response'] = array( 'response' => array( 'code' => $status ) );
	$error = arkemis_core_send_quote( $request );
	check( is_wp_error( $error ), "HTTP {$status} doit être retourné comme erreur" );
	$message = arkemis_core_quote_error_message( $error );
	check( false !== strpos( $message, $status < 500 ? 'accepter votre demande' : 'temporairement indisponible' ), "message visiteur HTTP {$status} incorrect" );
}

echo "OK: validation, cache, IDs A/B/C, retry, 2xx, timeout, 4xx/5xx, payload et idempotence\n";
