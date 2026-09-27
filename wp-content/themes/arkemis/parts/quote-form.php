<?php
defined( 'ABSPATH' ) || exit;
$state = arkemis_core_quote_state();
$values = $state['values'];
$errors = $state['errors'];
$turnstile_site_key = function_exists( 'arkemis_core_turnstile_site_key' ) ? arkemis_core_turnstile_site_key() : '';
if ( '' !== $turnstile_site_key ) {
	wp_enqueue_script( 'arkemis-turnstile-api', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
}
$labels = array( 'first_name' => 'Prénom', 'last_name' => 'Nom', 'phone' => 'Téléphone', 'email' => 'Courriel', 'city' => 'Ville / secteur', 'project_type' => 'Type de projet', 'budget' => 'Budget approximatif', 'description' => 'Description du projet', 'timeline' => 'Échéancier souhaité', 'contact_method' => 'Méthode de contact préférée' );
$choices = arkemis_core_quote_choices();
if ( $state['success'] ) : ?>
<section class="arkemis-quote-confirmation" id="demande-confirmation" tabindex="-1" autofocus aria-labelledby="demande-confirmation-title">
<h2 id="demande-confirmation-title">Votre demande a bien été reçue.</h2>
<p>Merci de nous avoir présenté votre projet. Nous pourrons communiquer avec vous à l’aide des coordonnées fournies.</p>
<a href="<?php echo esc_url( get_permalink() ); ?>">Présenter un autre projet</a>
</section>
<?php return; endif; ?>
<form class="arkemis-quote-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>#demande-formulaire" id="demande-formulaire" aria-labelledby="demande-form-title" data-turnstile-required="<?php echo '' !== $turnstile_site_key ? '1' : '0'; ?>" novalidate>
<h2 id="demande-form-title">Votre demande de soumission</h2>
<p class="arkemis-form-intro">Les champs marqués d’un astérisque (*) sont obligatoires. Indiquez un téléphone ou un courriel selon votre méthode de contact préférée.</p>
<?php if ( $errors ) : ?>
<div class="arkemis-form-errors" id="demande-erreurs" role="alert" tabindex="-1" autofocus>
<h3>Votre demande n’a pas été envoyée.</h3>
<?php if ( isset( $errors['form'] ) ) : ?><p><?php echo esc_html( $errors['form'] ); ?></p><?php endif; ?>
<?php if ( count( $errors ) > ( isset( $errors['form'] ) ? 1 : 0 ) ) : ?>
<p>Vérifiez les champs suivants :</p><ul>
<?php foreach ( $errors as $field => $error ) : if ( 'form' === $field ) { continue; } ?>
<li><a href="#quote-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $labels[ $field ] ); ?> : <?php echo esc_html( $error ); ?></a></li>
<?php endforeach; ?></ul>
<?php endif; ?></div>
<?php endif; ?>
<?php wp_nonce_field( 'arkemis_quote_submit', 'arkemis_quote_nonce', false ); ?>
<input type="hidden" name="arkemis_quote_submit" value="1" />
<input type="hidden" name="arkemis_quote_token" value="<?php echo esc_attr( arkemis_core_quote_form_token( $state ) ); ?>" />
<div class="arkemis-form-trap" aria-hidden="true"><label for="quote-website-honeypot">Laissez ce champ vide</label><input type="text" name="website_honeypot" id="quote-website-honeypot" tabindex="-1" autocomplete="off" /></div>
<div class="arkemis-form-grid">
<?php
$fields = array(
	'first_name' => array( 'type' => 'text', 'autocomplete' => 'given-name', 'max' => 80 ),
	'last_name' => array( 'type' => 'text', 'autocomplete' => 'family-name', 'max' => 80 ),
	'contact_method' => array( 'type' => 'select', 'wide' => true ),
	'phone' => array( 'type' => 'tel', 'autocomplete' => 'tel', 'max' => 40, 'optional' => true, 'hint' => 'Requis pour un appel ou un texto.' ),
	'email' => array( 'type' => 'email', 'autocomplete' => 'email', 'max' => 254, 'optional' => true, 'hint' => 'Requis pour une réponse par courriel.' ),
	'city' => array( 'type' => 'text', 'autocomplete' => 'address-level2', 'max' => 120, 'optional' => true, 'wide' => true ),
	'project_type' => array( 'type' => 'select', 'wide' => true ),
	'budget' => array( 'type' => 'select', 'wide' => true ),
	'description' => array( 'type' => 'textarea', 'wide' => true, 'hint' => 'De 10 à 5 000 caractères.' ),
	'timeline' => array( 'type' => 'select', 'wide' => true ),
);
foreach ( $fields as $field => $options ) :
	$id = 'quote-' . $field;
	$required = empty( $options['optional'] );
	$described = array();
	if ( isset( $options['hint'] ) ) { $described[] = $id . '-hint'; }
	if ( isset( $errors[ $field ] ) ) { $described[] = $id . '-error'; }
	$attributes = ' id="' . esc_attr( $id ) . '" name="' . esc_attr( $field ) . '"';
	$attributes .= $required ? ' required' : '';
	$attributes .= isset( $errors[ $field ] ) ? ' aria-invalid="true"' : '';
	$attributes .= $described ? ' aria-describedby="' . esc_attr( implode( ' ', $described ) ) . '"' : '';
?>
<div class="arkemis-form-field<?php echo ! empty( $options['wide'] ) ? ' arkemis-form-field--wide' : ''; ?>">
<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $labels[ $field ] ); ?><?php if ( $required ) : ?> <span aria-hidden="true">*</span><?php elseif ( 'city' === $field ) : ?> <span class="arkemis-form-optional">(facultatif)</span><?php endif; ?></label>
<?php if ( 'select' === $options['type'] ) : ?>
<select<?php echo $attributes; // Attributs construits et échappés ci-dessus. ?>>
<option value="">Choisir une option</option>
<?php foreach ( $choices[ $field ] as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>"<?php selected( $values[ $field ] ?? '', $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
</select>
<?php elseif ( 'textarea' === $options['type'] ) : ?>
<textarea<?php echo $attributes; ?> rows="7" maxlength="5000"><?php echo esc_textarea( $values[ $field ] ?? '' ); ?></textarea>
<?php else : ?>
<input<?php echo $attributes; ?> type="<?php echo esc_attr( $options['type'] ); ?>" autocomplete="<?php echo esc_attr( $options['autocomplete'] ); ?>" maxlength="<?php echo (int) $options['max']; ?>" value="<?php echo esc_attr( $values[ $field ] ?? '' ); ?>" />
<?php endif; ?>
<?php if ( isset( $options['hint'] ) ) : ?><p class="arkemis-field-hint" id="<?php echo esc_attr( $id ); ?>-hint"><?php echo esc_html( $options['hint'] ); ?></p><?php endif; ?>
<?php if ( isset( $errors[ $field ] ) ) : ?><p class="arkemis-field-error" id="<?php echo esc_attr( $id ); ?>-error"><?php echo esc_html( $errors[ $field ] ); ?></p><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php if ( '' !== $turnstile_site_key ) : ?>
<div class="arkemis-turnstile-wrap">
	<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $turnstile_site_key ); ?>" data-callback="arkemisQuoteTurnstileSuccess" data-expired-callback="arkemisQuoteTurnstileExpired" data-error-callback="arkemisQuoteTurnstileError" aria-label="Vérification de sécurité"></div>
	<p class="arkemis-turnstile-error" data-turnstile-error role="alert" tabindex="-1" hidden>Veuillez confirmer la vérification de sécurité avant d’envoyer votre demande.</p>
</div>
<?php endif; ?>
<button class="arkemis-form-submit" type="submit">Envoyer ma demande</button>
<p class="arkemis-field-hint">Pour connaître l’utilisation de vos renseignements, consultez notre <a href="<?php echo esc_url( home_url( '/politique-de-confidentialite/' ) ); ?>">politique de confidentialité</a>.</p>
</form>
