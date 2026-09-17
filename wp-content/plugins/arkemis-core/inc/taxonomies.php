<?php
defined( 'ABSPATH' ) || exit;

function arkemis_core_register_taxonomies() {
	// Classement interne des réalisations, distinct des Pages de services.
	register_taxonomy( 'arkemis_service', array( 'realisation' ), array(
		'labels' => array(
			'name'          => __( 'Services associés', 'arkemis-core' ),
			'singular_name' => __( 'Service associé', 'arkemis-core' ),
		),
		'public'            => false,
		'publicly_queryable'=> false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
	) );
}
add_action( 'init', 'arkemis_core_register_taxonomies' );
