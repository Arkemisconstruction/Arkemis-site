<?php
defined( 'ABSPATH' ) || exit;

function arkemis_core_register_realisations() {
	register_post_type( 'realisation', array(
		'labels' => array(
			'name'          => __( 'Réalisations', 'arkemis-core' ),
			'singular_name' => __( 'Réalisation', 'arkemis-core' ),
			'add_new_item'  => __( 'Ajouter une réalisation', 'arkemis-core' ),
			'edit_item'     => __( 'Modifier la réalisation', 'arkemis-core' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => 'realisations',
		'rewrite'      => array( 'slug' => 'realisations', 'with_front' => false ),
		'menu_icon'    => 'dashicons-portfolio',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
	) );
}
add_action( 'init', 'arkemis_core_register_realisations' );
