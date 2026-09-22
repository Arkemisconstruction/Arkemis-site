<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	wp_register_script( 'arkemis-quote-editor', get_theme_file_uri( 'assets/js/quote-editor.js' ), array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ), (string) filemtime( get_theme_file_path( 'assets/js/quote-editor.js' ) ), true );
	register_block_type( 'arkemis/quote-form', array(
		'api_version' => 3, 'editor_script' => 'arkemis-quote-editor',
		'render_callback' => function () {
			if ( ! function_exists( 'arkemis_core_quote_state' ) ) {
				return '<p>Pour votre demande, écrivez à <a href="mailto:info@arkemis.ca">info@arkemis.ca</a>.</p>';
			}
			ob_start();
			require __DIR__ . '/../parts/quote-form.php';
			return ob_get_clean();
		},
	) );
} );
