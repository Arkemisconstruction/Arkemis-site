<?php
defined( 'ABSPATH' ) || exit;

function arkemis_core_site_pages() {
	return array(
		'accueil' => array(
			'title' => 'Accueil',
			'template' => 'front-page',
			'template_mode' => 'automatic',
		),
		'terrassement-excavation' => array(
			'title' => 'Terrassement et excavation',
			'template' => 'service',
			'pattern' => 'arkemis/service-terrassement-excavation',
		),
		'beton-architectural' => array(
			'title' => 'Béton architectural',
			'template' => 'service',
			'pattern' => 'arkemis/service-beton-architectural',
		),
		'renovation' => array(
			'title' => 'Rénovation',
			'template' => 'service',
			'pattern' => 'arkemis/service-renovation',
		),
		'a-propos' => array(
			'title' => 'À propos',
			'template' => 'page-a-propos',
			'template_mode' => 'automatic',
			'pattern' => 'arkemis/a-propos',
		),
		'contact' => array(
			'title' => 'Contact',
			'template' => 'default',
			'content' => '<!-- wp:paragraph --><p>Construction Arkemis inc.</p><!-- /wp:paragraph -->' . "\n" . '<!-- wp:paragraph --><p>Québec, QC</p><!-- /wp:paragraph -->' . "\n" . '<!-- wp:paragraph --><p><a href="mailto:info@arkemis.ca">info@arkemis.ca</a></p><!-- /wp:paragraph -->',
		),
		'demander-une-soumission' => array(
			'title' => 'Demander une soumission',
			'template' => 'contact',
			'pattern' => 'arkemis/contact',
		),
		'politique-de-confidentialite' => array(
			'title' => 'Politique de confidentialité',
			'template' => 'page-politique-de-confidentialite',
			'template_mode' => 'automatic',
			'pattern' => 'arkemis/confidentialite',
		),
	);
}

function arkemis_core_initializer_pattern_content( $slug ) {
	if ( empty( $slug ) || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return '';
	}
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return is_array( $pattern ) && isset( $pattern['content'] ) && is_string( $pattern['content'] ) ? $pattern['content'] : '';
}

function arkemis_core_initializer_find_page( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page ) {
		return $page;
	}
	$trashed = get_posts(
		array(
			'post_type' => 'page',
			'post_status' => 'trash',
			'posts_per_page' => 1,
			'meta_key' => '_wp_desired_post_slug',
			'meta_value' => $slug,
		)
	);
	return $trashed ? $trashed[0] : null;
}

function arkemis_core_initializer_template_status( $page_id, $definition, $allow_existing_update, $created ) {
	$template = $definition['template'];
	if ( 'automatic' === ( $definition['template_mode'] ?? '' ) ) {
		return array( 'status' => 'template affecté', 'message' => $template . ' (sélection automatique par WordPress)' );
	}
	if ( 'default' === $template ) {
		return array( 'status' => 'template affecté', 'message' => 'Modèle de page par défaut' );
	}
	if ( ! $created && ! $allow_existing_update ) {
		return array( 'status' => 'ignoré', 'message' => 'Template existant conservé' );
	}
	$current = get_post_meta( $page_id, '_wp_page_template', true );
	if ( $template !== $current ) {
		if ( false === update_post_meta( $page_id, '_wp_page_template', $template ) ) {
			return array( 'status' => 'erreur', 'message' => 'Impossible d’affecter le template ' . $template );
		}
	}
	return array( 'status' => 'template affecté', 'message' => $template );
}

function arkemis_core_initialize_site_pages( $allow_existing_template_updates = false ) {
	$report = array();
	$home_id = 0;

	foreach ( arkemis_core_site_pages() as $slug => $definition ) {
		$page = arkemis_core_initializer_find_page( $slug );
		$created = false;
		if ( $page ) {
			$page_id = (int) $page->ID;
			$page_status = 'déjà existante';
			$message = 'Page existante conservée sans modification de son titre ou de son contenu.';
		} else {
			$content = isset( $definition['content'] ) ? $definition['content'] : arkemis_core_initializer_pattern_content( $definition['pattern'] ?? '' );
			$page_id = wp_insert_post(
				array(
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_title' => $definition['title'],
					'post_name' => $slug,
					'post_content' => $content,
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				$report[] = array( 'title' => $definition['title'], 'slug' => $slug, 'page_status' => 'erreur', 'template_status' => 'ignoré', 'message' => $page_id->get_error_message() );
				continue;
			}
			$page_id = (int) $page_id;
			$created = true;
			$page_status = 'créée';
			$message = '' === $content && ! empty( $definition['pattern'] ) ? 'Page créée; pattern indisponible, contenu laissé vide.' : 'Page créée.';
		}

		$template = arkemis_core_initializer_template_status( $page_id, $definition, $allow_existing_template_updates, $created );
		$report[] = array(
			'title' => $definition['title'],
			'slug' => $slug,
			'page_id' => $page_id,
			'page_status' => $page_status,
			'template_status' => $template['status'],
			'message' => trim( $message . ' ' . $template['message'] ),
		);
		if ( 'accueil' === $slug ) {
			$home_id = $page_id;
		}
	}

	$report[] = array(
		'title' => 'Réalisations',
		'slug' => 'realisations',
		'page_status' => 'ignorée',
		'template_status' => 'template affecté',
		'message' => 'Route réservée à l’archive du type de contenu realisation.',
	);

	$front_page = (int) get_option( 'page_on_front', 0 );
	if ( $home_id && 0 === $front_page ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		$report[] = array( 'title' => 'Page d’accueil statique', 'slug' => '', 'page_status' => 'créée', 'template_status' => 'template affecté', 'message' => 'Accueil est maintenant la page d’accueil.' );
	} else {
		$report[] = array( 'title' => 'Page d’accueil statique', 'slug' => '', 'page_status' => 'ignorée', 'template_status' => 'ignoré', 'message' => $front_page ? 'Configuration existante conservée.' : 'Impossible de configurer l’accueil.' );
	}

	$privacy = arkemis_core_initializer_find_page( 'politique-de-confidentialite' );
	$privacy_page = (int) get_option( 'wp_page_for_privacy_policy', 0 );
	if ( $privacy && 0 === $privacy_page ) {
		update_option( 'wp_page_for_privacy_policy', (int) $privacy->ID );
		$report[] = array( 'title' => 'Page de confidentialité WordPress', 'slug' => '', 'page_status' => 'créée', 'template_status' => 'template affecté', 'message' => 'La politique Arkemis est maintenant la page de confidentialité.' );
	} else {
		$report[] = array( 'title' => 'Page de confidentialité WordPress', 'slug' => '', 'page_status' => 'ignorée', 'template_status' => 'ignoré', 'message' => $privacy_page ? 'Configuration existante conservée.' : 'Impossible de configurer la politique.' );
	}

	return $report;
}

function arkemis_core_initializer_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_transient( 'arkemis_core_initializer_report_' . get_current_user_id() );
	if ( false !== $report ) {
		delete_transient( 'arkemis_core_initializer_report_' . get_current_user_id() );
	}
	?>
	<div class="wrap">
		<h1>Initialiser les pages Arkemis</h1>
		<p>Cette action crée uniquement les pages manquantes. Elle ne remplace jamais le titre ni le contenu d’une page existante et ne crée pas de Page pour l’archive Réalisations.</p>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="arkemis_initialize_pages">
			<?php wp_nonce_field( 'arkemis_initialize_pages' ); ?>
			<label><input type="checkbox" name="update_existing_templates" value="1"> Affecter les templates Arkemis aux pages existantes correspondantes. Leur contenu restera inchangé.</label>
			<?php submit_button( 'Initialiser les pages Arkemis' ); ?>
		</form>
		<?php if ( is_array( $report ) ) : ?>
			<h2>Rapport de la dernière exécution</h2>
			<table class="widefat striped"><thead><tr><th>Destination</th><th>Page</th><th>Template</th><th>Détail</th></tr></thead><tbody>
			<?php foreach ( $report as $item ) : ?>
				<tr><td><?php echo esc_html( $item['title'] ); ?><?php echo $item['slug'] ? '<br><code>/' . esc_html( $item['slug'] ) . '/</code>' : ''; ?></td><td><?php echo esc_html( $item['page_status'] ); ?></td><td><?php echo esc_html( $item['template_status'] ); ?></td><td><?php echo esc_html( $item['message'] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody></table>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_menu', function () {
	add_management_page( 'Initialiser les pages Arkemis', 'Initialisation Arkemis', 'manage_options', 'arkemis-initializer', 'arkemis_core_initializer_admin_page' );
} );

add_action( 'admin_post_arkemis_initialize_pages', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.', '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'arkemis_initialize_pages' );
	$allow_templates = isset( $_POST['update_existing_templates'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['update_existing_templates'] ) );
	set_transient( 'arkemis_core_initializer_report_' . get_current_user_id(), arkemis_core_initialize_site_pages( $allow_templates ), 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'tools.php?page=arkemis-initializer' ), 303 );
	exit;
} );
