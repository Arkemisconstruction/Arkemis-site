<?php
define( 'ABSPATH', __DIR__ );
define( 'OBJECT', 'OBJECT' );
define( 'MINUTE_IN_SECONDS', 60 );

class WP_Error {
	private $message;
	public function __construct( $message ) { $this->message = $message; }
	public function get_error_message() { return $this->message; }
}

class WP_Block_Patterns_Registry {
	private static $instance;
	public static function get_instance() { return self::$instance ??= new self(); }
	public function get_registered( $slug ) {
		return array( 'content' => '<!-- wp:group --><div>' . $slug . ( 'arkemis/contact' === $slug ? '<!-- wp:arkemis/quote-form /-->' : '' ) . '</div><!-- /wp:group -->' );
	}
}

$GLOBALS['pages'] = array();
$GLOBALS['meta'] = array();
$GLOBALS['options'] = array( 'page_on_front' => 0, 'show_on_front' => 'posts', 'wp_page_for_privacy_policy' => 0 );
$GLOBALS['next_id'] = 1;
$GLOBALS['trashed_pages'] = array();

function add_action() {}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_page_by_path( $slug ) { return $GLOBALS['pages'][ $slug ] ?? null; }
function get_posts( $args ) { return isset( $GLOBALS['trashed_pages'][ $args['meta_value'] ] ) ? array( $GLOBALS['trashed_pages'][ $args['meta_value'] ] ) : array(); }
function wp_insert_post( $data ) {
	$id = $GLOBALS['next_id']++;
	$GLOBALS['pages'][ $data['post_name'] ] = (object) array_merge( $data, array( 'ID' => $id ) );
	return $id;
}
function get_post_meta( $id, $key ) { return $GLOBALS['meta'][ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][ $id ][ $key ] = $value; return true; }
function get_option( $key, $default = false ) { return $GLOBALS['options'][ $key ] ?? $default; }
function update_option( $key, $value ) { $GLOBALS['options'][ $key ] = $value; return true; }

require dirname( __DIR__ ) . '/wp-content/plugins/arkemis-core/inc/site-initializer.php';

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

// Une page existante doit être conservée exactement telle quelle.
$GLOBALS['trashed_pages']['ancienne-page'] = (object) array( 'ID' => 49, 'post_name' => 'ancienne-page__trashed' );
check( 49 === arkemis_core_initializer_find_page( 'ancienne-page' )->ID, 'une Page en corbeille doit être détectée' );
$GLOBALS['pages']['contact'] = (object) array(
	'ID' => 50, 'post_name' => 'contact', 'post_title' => 'Nous joindre',
	'post_content' => '<p>Contenu administrateur à conserver.</p>', 'post_status' => 'draft',
);
$GLOBALS['meta'][50]['_wp_page_template'] = 'custom-existing-template';

$first = arkemis_core_initialize_site_pages( false );
check( 8 === count( $GLOBALS['pages'] ), 'huit Pages doivent exister, archive Réalisations exclue' );
check( ! isset( $GLOBALS['pages']['realisations'] ), 'l\'archive Réalisations ne doit jamais devenir une Page' );
check( 'Nous joindre' === $GLOBALS['pages']['contact']->post_title, 'le titre existant doit être conservé' );
check( '<p>Contenu administrateur à conserver.</p>' === $GLOBALS['pages']['contact']->post_content, 'le contenu existant doit être conservé' );
check( 'custom-existing-template' === $GLOBALS['meta'][50]['_wp_page_template'], 'le template existant doit rester intact sans confirmation' );

$quote = $GLOBALS['pages']['demander-une-soumission'];
check( false !== strpos( $quote->post_content, '<!-- wp:arkemis/quote-form /-->' ), 'la page de soumission doit contenir le bloc arkemis/quote-form' );
check( 'contact' === $GLOBALS['meta'][ $quote->ID ]['_wp_page_template'], 'la page de soumission doit utiliser le template contact' );
foreach ( array( 'terrassement-excavation', 'beton-architectural', 'renovation' ) as $slug ) {
	check( 'service' === $GLOBALS['meta'][ $GLOBALS['pages'][ $slug ]->ID ]['_wp_page_template'], "{$slug} doit utiliser le template service" );
}
check( $GLOBALS['pages']['accueil']->ID === $GLOBALS['options']['page_on_front'], 'Accueil doit devenir la page statique' );
check( 'page' === $GLOBALS['options']['show_on_front'], 'le site doit afficher une page statique' );
check( $GLOBALS['pages']['politique-de-confidentialite']->ID === $GLOBALS['options']['wp_page_for_privacy_policy'], 'la politique doit être configurée dans WordPress' );

// Une seconde exécution ne doit rien dupliquer ou remplacer.
$snapshot = serialize( $GLOBALS['pages'] );
$second = arkemis_core_initialize_site_pages( false );
check( $snapshot === serialize( $GLOBALS['pages'] ), 'la réexécution doit être idempotente' );
check( 8 === count( $GLOBALS['pages'] ), 'la réexécution ne doit créer aucun doublon' );

// L'option explicite peut corriger un template sans toucher au contenu.
$GLOBALS['meta'][ $quote->ID ]['_wp_page_template'] = 'ancien-template';
arkemis_core_initialize_site_pages( false );
check( 'ancien-template' === $GLOBALS['meta'][ $quote->ID ]['_wp_page_template'], 'le template existant doit rester sans confirmation' );
arkemis_core_initialize_site_pages( true );
check( 'contact' === $GLOBALS['meta'][ $quote->ID ]['_wp_page_template'], 'la confirmation doit permettre d\'affecter le bon template' );
check( false !== strpos( $quote->post_content, '<!-- wp:arkemis/quote-form /-->' ), 'la correction du template ne doit pas remplacer le contenu' );

echo "OK: pages minimales, archive réservée, idempotence, templates, soumission et conservation\n";
