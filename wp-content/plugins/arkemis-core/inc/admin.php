<?php
defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_realisation', function () {
	add_meta_box(
		'arkemis-project-details',
		__( 'Informations sur la réalisation', 'arkemis-core' ),
		'arkemis_core_render_meta_box',
		'realisation',
		'normal',
		'default',
		array( '__block_editor_compatible_meta_box' => true )
	);
} );

function arkemis_core_render_meta_box( $post ) {
	wp_nonce_field( 'arkemis_save_realisation', 'arkemis_realisation_nonce' );
	echo '<p>' . esc_html__( 'Choisissez les services dans le panneau Services associés. Les images utilisent leurs identifiants dans la médiathèque ; un sélecteur visuel sera ajouté ultérieurement.', 'arkemis-core' ) . '</p>';
	foreach ( arkemis_core_meta_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
		echo '<p><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label><br>';
		if ( '_arkemis_summary' === $key ) {
			echo '<textarea class="widefat" rows="3" id="' . esc_attr( $key ) . '" name="arkemis_meta[' . esc_attr( $key ) . ']">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input class="widefat" type="text" id="' . esc_attr( $key ) . '" name="arkemis_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
		}
		echo '</p>';
	}
}

function arkemis_core_save_meta_box( $post_id ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['arkemis_realisation_nonce'] ) || ! is_string( $_POST['arkemis_realisation_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['arkemis_realisation_nonce'] ) ), 'arkemis_save_realisation' ) ) {
		return;
	}
	if ( ! isset( $_POST['arkemis_meta'] ) || ! is_array( $_POST['arkemis_meta'] ) ) {
		return;
	}
	$input = wp_unslash( $_POST['arkemis_meta'] );
	foreach ( arkemis_core_meta_fields() as $key => $field ) {
		// Un champ absent est conservé ; un champ vide est explicitement effacé.
		if ( ! array_key_exists( $key, $input ) || ! is_scalar( $input[ $key ] ) ) {
			continue;
		}
		if ( '' === trim( (string) $input[ $key ] ) ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, call_user_func( $field['sanitize'], $input[ $key ] ) );
		}
	}
}
add_action( 'save_post_realisation', 'arkemis_core_save_meta_box' );

add_filter( 'manage_realisation_posts_columns', function ( $columns ) {
	$columns['arkemis_city'] = __( 'Ville', 'arkemis-core' );
	return $columns;
} );

add_action( 'manage_realisation_posts_custom_column', function ( $column, $post_id ) {
	if ( 'arkemis_city' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_arkemis_city', true ) );
	}
}, 10, 2 );
