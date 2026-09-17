<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'arkemis-base',
		get_theme_file_uri( 'assets/css/base.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
} );
