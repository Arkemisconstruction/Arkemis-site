<?php
/**
 * Plugin Name: Arkemis Core
 * Description: Données et fonctionnalités durables du site Arkemis.
 * Version: 0.1.0
 * Author: Construction Arkemis inc.
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: arkemis-core
 * License: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;

foreach ( array( 'realisations', 'taxonomies', 'meta-fields', 'admin', 'quote-requests' ) as $module ) {
	require_once __DIR__ . '/inc/' . $module . '.php';
}

register_activation_hook( __FILE__, function () {
	arkemis_core_register_realisations();
	arkemis_core_register_taxonomies();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	unregister_taxonomy( 'arkemis_service' );
	unregister_post_type( 'realisation' );
	flush_rewrite_rules();
} );

// Aucune suppression de contenu, de métadonnées ou de termes à la désactivation.
