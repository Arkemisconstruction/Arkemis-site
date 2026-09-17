<?php
defined( 'ABSPATH' ) || exit;

/**
 * Le service est stocké dans arkemis_service, sans duplication en post_meta.
 * Description détaillée : post_content ; image principale : image mise en avant.
 */
function arkemis_core_meta_fields() {
	return array(
		'_arkemis_city' => array( 'label' => 'Ville', 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'_arkemis_sector' => array( 'label' => 'Secteur', 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'_arkemis_summary' => array( 'label' => 'Description courte', 'type' => 'string', 'sanitize' => 'sanitize_textarea_field' ),
		'_arkemis_year' => array( 'label' => 'Année des travaux (facultative)', 'type' => 'integer', 'sanitize' => 'arkemis_core_sanitize_year' ),
		'_arkemis_gallery' => array( 'label' => 'Galerie : identifiants des images, séparés par des virgules', 'type' => 'array', 'sanitize' => 'arkemis_core_sanitize_gallery' ),
		'_arkemis_before' => array( 'label' => 'Avant : identifiant de l’image', 'type' => 'integer', 'sanitize' => 'arkemis_core_sanitize_image' ),
		'_arkemis_after' => array( 'label' => 'Après : identifiant de l’image', 'type' => 'integer', 'sanitize' => 'arkemis_core_sanitize_image' ),
	);
}

function arkemis_core_sanitize_year( $value ) {
	$year = absint( $value );
	return $year >= 1900 && $year <= 2100 ? $year : 0;
}

function arkemis_core_sanitize_image( $value ) {
	$id = absint( $value );
	return $id && wp_attachment_is_image( $id ) ? $id : 0;
}

function arkemis_core_sanitize_gallery( $value ) {
	if ( ! is_array( $value ) ) {
		$value = preg_split( '/[\\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
	}
	return array_values( array_unique( array_filter( array_map( 'arkemis_core_sanitize_image', $value ) ) ) );
}

function arkemis_core_can_edit_meta( $allowed, $meta_key, $post_id ) {
	return current_user_can( 'edit_post', $post_id );
}

function arkemis_core_register_meta() {
	foreach ( arkemis_core_meta_fields() as $key => $field ) {
		$args = array(
			'type'              => $field['type'],
			'single'            => true,
			'sanitize_callback' => $field['sanitize'],
			'auth_callback'     => 'arkemis_core_can_edit_meta',
			'show_in_rest'      => true,
			'revisions_enabled'=> true,
		);
		if ( 'array' === $field['type'] ) {
			$args['show_in_rest'] = array( 'schema' => array(
				'type' => 'array', 'items' => array( 'type' => 'integer' ),
			) );
		}
		register_post_meta( 'realisation', $key, $args );
	}
}
add_action( 'init', 'arkemis_core_register_meta' );
