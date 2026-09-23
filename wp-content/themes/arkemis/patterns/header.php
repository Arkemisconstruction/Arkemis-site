<?php
/**
 * Title: En-tête Arkemis
 * Slug: arkemis/header
 * Categories: header
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"arkemis-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group arkemis-header">
<!-- wp:group {"className":"arkemis-shell arkemis-header__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group arkemis-shell arkemis-header__inner">
<!-- wp:image {"sizeSlug":"full","linkDestination":"custom","className":"arkemis-logo"} -->
<figure class="wp-block-image size-full arkemis-logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/branding/arkemis_logo+Nom_MASTER.svg' ) ); ?>" alt="Arkemis — accueil" /></a></figure>
<!-- /wp:image -->
<!-- wp:navigation {"overlayMenu":"mobile","className":"arkemis-navigation","ariaLabel":"Navigation principale","layout":{"type":"flex","justifyContent":"right"}} -->
<!-- wp:navigation-link <?php echo wp_json_encode( array( 'label' => 'Services', 'url' => home_url( '/#services' ), 'kind' => 'custom' ) ); ?> /-->
<!-- wp:navigation-link <?php echo wp_json_encode( array( 'label' => 'Demander une soumission', 'url' => home_url( '/demander-une-soumission/' ), 'kind' => 'custom', 'className' => 'arkemis-nav-cta' ) ); ?> /-->
<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
