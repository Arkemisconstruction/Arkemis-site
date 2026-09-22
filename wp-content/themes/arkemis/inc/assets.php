<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'arkemis-base',
		get_theme_file_uri( 'assets/css/base.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
	if ( is_page_template( 'contact' ) ) {
		wp_enqueue_style( 'arkemis-contact', get_theme_file_uri( 'assets/css/contact.css' ), array( 'arkemis-base' ), (string) filemtime( get_theme_file_path( 'assets/css/contact.css' ) ) );
		wp_enqueue_script( 'arkemis-contact', get_theme_file_uri( 'assets/js/contact.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/contact.js' ) ), true );
	}
} );
