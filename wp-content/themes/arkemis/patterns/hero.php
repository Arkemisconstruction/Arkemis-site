<?php
/**
 * Title: Accueil — présentation Arkemis
 * Slug: arkemis/hero
 * Categories: featured
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"arkemis-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group arkemis-hero">
<!-- wp:group {"className":"arkemis-shell arkemis-hero__inner","layout":{"type":"default"}} -->
<div class="wp-block-group arkemis-shell arkemis-hero__inner">
<!-- wp:group {"className":"arkemis-hero__copy","layout":{"type":"default"}} -->
<div class="wp-block-group arkemis-hero__copy">
<!-- wp:heading {"level":1,"className":"arkemis-hero__title"} -->
<h1 class="wp-block-heading arkemis-hero__title"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/branding/arkemis_logo+Nom_MASTER.svg' ) ); ?>" alt="ARKEMIS" width="1536" height="1024" /></h1>
<!-- /wp:heading -->
<!-- wp:group {"className":"arkemis-hero__details","layout":{"type":"default"}} -->
<div class="wp-block-group arkemis-hero__details">
<!-- wp:paragraph {"className":"arkemis-hero__services"} -->
<p class="arkemis-hero__services">Terrassement · Béton architectural · Rénovation</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"arkemis-hero__statement"} -->
<p class="arkemis-hero__statement">Des ouvrages solides. Une finition qui se démarque.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"arkemis-button"} -->
<div class="wp-block-button arkemis-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/demander-une-soumission/' ) ); ?>">Demander une soumission</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"arkemis-button arkemis-button--outline"} -->
<div class="wp-block-button arkemis-button arkemis-button--outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/realisations/' ) ); ?>">Voir nos réalisations</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"arkemis-hero__image"} -->
<figure class="wp-block-image size-full arkemis-hero__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/projects/beton/IMG_7158.jpg' ) ); ?>" alt="Marches et allée en béton devant une maison en brique." /></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
