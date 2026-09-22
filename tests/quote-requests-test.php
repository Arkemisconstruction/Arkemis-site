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
}

$GLOBALS['remote_response'] = array( 'response' => array( 'code' => 202 ) );
$GLOBALS['remote_calls'] = array();

function add_action() {}
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

putenv( 'ARKEMIS_PLATFORM_API_TOKEN=test-token-not-a-real-secret' );
$request = array_merge( array( 'schema_version' => 1, 'request_id' => '11111111-1111-4111-8111-111111111111', 'organization_id' => 'forbidden' ), $values );
check( true === arkemis_core_send_quote( $request ), 'une réponse 2xx doit réussir' );
$first = $GLOBALS['remote_calls'][0];
check( 'https://plateforme.arkemis.ca/api/public/website-inquiries' === $first['url'], 'endpoint inattendu' );
check( 10 === $first['options']['timeout'], 'timeout HTTP inattendu' );
check( $request['request_id'] === $first['options']['headers']['Idempotency-Key'], 'clé d\'idempotence absente' );
check( false === strpos( $first['options']['body'], 'organization_id' ), 'un identifiant tenant ne doit jamais être envoyé' );
check( false === strpos( $first['options']['body'], 'test-token' ), 'le secret ne doit jamais être dans le JSON' );

arkemis_core_send_quote( $request );
check( $GLOBALS['remote_calls'][0]['options']['headers']['Idempotency-Key'] === $GLOBALS['remote_calls'][1]['options']['headers']['Idempotency-Key'], 'une relance doit conserver la clé d\'idempotence' );

$GLOBALS['remote_response'] = new WP_Error( 'http_request_failed', 'Operation timed out' );
check( is_wp_error( arkemis_core_send_quote( $request ) ), 'un timeout doit être retourné comme erreur' );
foreach ( array( 400, 422, 500, 503 ) as $status ) {
	$GLOBALS['remote_response'] = array( 'response' => array( 'code' => $status ) );
	check( is_wp_error( arkemis_core_send_quote( $request ) ), "HTTP {$status} doit être retourné comme erreur" );
}

echo "OK: validation, 2xx, timeout, 4xx/5xx, payload et idempotence\n";
