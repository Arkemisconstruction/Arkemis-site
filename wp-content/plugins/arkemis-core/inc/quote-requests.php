<?php
defined( 'ABSPATH' ) || exit;

/** Contrat de demande indépendant du thème. */
function arkemis_core_quote_choices() {
	return array(
		'project_type' => array( 'terrassement-beton' => 'Terrassement / béton architectural', 'renovation-interieure' => 'Rénovation intérieure', 'renovation-exterieure' => 'Rénovation extérieure', 'autre' => 'Autre / je ne sais pas encore' ),
		'budget' => array( 'under-5000' => 'Moins de 5 000 $', '5000-10000' => '5 000 à 10 000 $', '10000-20000' => '10 000 à 20 000 $', '20000-40000' => '20 000 à 40 000 $', '40000-75000' => '40 000 à 75 000 $', '75000-plus' => '75 000 $ et plus', 'undetermined' => 'À déterminer' ),
		'timeline' => array( 'soon' => 'Dès que possible', '1-3' => 'Dans les 1 à 3 mois', '3-6' => 'Dans les 3 à 6 mois', 'later' => 'Plus tard / à déterminer' ),
		'contact_method' => array( 'phone' => 'Téléphone', 'email' => 'Courriel', 'sms' => 'Texto' ),
	);
}

function arkemis_core_quote_length( $value ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

function arkemis_core_validate_quote( $input ) {
	$values = array();
	$errors = array();
	foreach ( array( 'first_name', 'last_name', 'phone', 'email', 'city', 'project_type', 'budget', 'description', 'timeline', 'contact_method' ) as $field ) {
		$raw = $input[ $field ] ?? '';
		if ( ! is_string( $raw ) ) {
			$errors[ $field ] = 'Ce champ contient une valeur non valide.';
			$raw = '';
		}
		$raw = trim( $raw );
		$values[ $field ] = 'description' === $field ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( 'email' === $field && '' !== $raw && ( ! is_email( $raw ) || strlen( $raw ) > 254 ) ) {
			$errors['email'] = 'Entrez une adresse courriel valide.';
		}
	}
	foreach ( array( 'first_name' => 'prénom', 'last_name' => 'nom' ) as $field => $label ) {
		if ( ! preg_match( '/\p{L}/u', $values[ $field ] ) || arkemis_core_quote_length( $values[ $field ] ) > 80 ) {
			$errors[ $field ] = 'Entrez votre ' . $label . ' (80 caractères maximum).';
		}
	}
	if ( '' !== $values['phone'] ) {
		$digits = preg_replace( '/\D/', '', $values['phone'] );
		if ( ! preg_match( '/^[+0-9() .\-]+$/', $values['phone'] ) || strlen( $digits ) < 10 || strlen( $digits ) > 15 || strlen( $values['phone'] ) > 40 ) {
			$errors['phone'] = 'Entrez un numéro de téléphone valide, avec l’indicatif régional.';
		}
	}
	foreach ( arkemis_core_quote_choices() as $field => $choices ) {
		if ( ! array_key_exists( $values[ $field ], $choices ) ) {
			$errors[ $field ] = 'Choisissez une option dans cette liste.';
		}
	}
	if ( 'email' === $values['contact_method'] && '' === $values['email'] ) {
		$errors['email'] = 'Indiquez votre courriel pour recevoir une réponse par courriel.';
	}
	if ( in_array( $values['contact_method'], array( 'phone', 'sms' ), true ) && '' === $values['phone'] ) {
		$errors['phone'] = 'Indiquez votre téléphone pour recevoir un appel ou un texto.';
	}
	if ( '' === $values['phone'] && '' === $values['email'] ) {
		$errors['phone'] = 'Indiquez un téléphone ou un courriel selon votre méthode de contact.';
		$errors['email'] = 'Indiquez un courriel ou un téléphone selon votre méthode de contact.';
	}
	$length = arkemis_core_quote_length( $values['description'] );
	if ( $length < 10 || $length > 5000 ) {
		$errors['description'] = 'Décrivez votre projet en 10 à 5 000 caractères.';
	}
	if ( arkemis_core_quote_length( $values['city'] ) > 120 ) {
		$errors['city'] = 'Limitez la ville ou le secteur à 120 caractères.';
	}
	return array( 'values' => $values, 'errors' => $errors );
}

function arkemis_core_quote_token() {
	$payload = wp_generate_uuid4() . '.' . time();
	return $payload . '.' . hash_hmac( 'sha256', $payload, wp_salt( 'nonce' ) );
}

function arkemis_core_quote_token_id( $token ) {
	if ( ! is_string( $token ) || ! preg_match( '/^([a-f0-9-]{36})\.(\d{10})\.([a-f0-9]{64})$/', $token, $parts ) ) {
		return '';
	}
	$age = time() - (int) $parts[2];
	return $age >= 0 && $age <= 2 * HOUR_IN_SECONDS && hash_equals( hash_hmac( 'sha256', $parts[1] . '.' . $parts[2], wp_salt( 'nonce' ) ), $parts[3] ) ? $parts[1] : '';
}

function arkemis_core_quote_form_token( $state = array() ) {
	$existing = is_array( $state ) && isset( $state['quote_token'] ) && is_string( $state['quote_token'] ) ? $state['quote_token'] : '';
	return arkemis_core_quote_token_id( $existing ) ? $existing : arkemis_core_quote_token();
}

function arkemis_core_is_quote_page() {
	return function_exists( 'is_page' ) && is_page( 'demander-une-soumission' );
}

function arkemis_core_quote_disable_cache() {
	if ( ! arkemis_core_is_quote_page() ) {
		return;
	}
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	do_action( 'litespeed_control_set_nocache', 'Formulaire de soumission Arkemis dynamique' );
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
	header( 'CDN-Cache-Control: no-store', true );
	header( 'X-LiteSpeed-Cache-Control: no-cache', true );
}

add_action( 'wp', 'arkemis_core_quote_disable_cache', 0 );

add_action( 'init', function () {
	$cache_revision = '2026-09-24-quote-form-v1';
	if ( get_option( 'arkemis_quote_cache_revision' ) === $cache_revision ) {
		return;
	}
	do_action( 'litespeed_purge_url', home_url( '/demander-une-soumission/' ) );
	update_option( 'arkemis_quote_cache_revision', $cache_revision, false );
} );

function arkemis_core_platform_token() {
	if ( defined( 'ARKEMIS_PLATFORM_API_TOKEN' ) && is_string( ARKEMIS_PLATFORM_API_TOKEN ) ) {
		return trim( ARKEMIS_PLATFORM_API_TOKEN );
	}
	$token = getenv( 'ARKEMIS_PLATFORM_API_TOKEN' );
	return is_string( $token ) ? trim( $token ) : '';
}

function arkemis_core_quote_payload( $request ) {
	$choices = arkemis_core_quote_choices();
	$details = array(
		'Budget approximatif : ' . ( $choices['budget'][ $request['budget'] ] ?? $request['budget'] ),
		'Échéancier souhaité : ' . ( $choices['timeline'][ $request['timeline'] ] ?? $request['timeline'] ),
		'Méthode de contact préférée : ' . ( $choices['contact_method'][ $request['contact_method'] ] ?? $request['contact_method'] ),
	);
	return array(
		'first_name' => $request['first_name'],
		'last_name' => $request['last_name'],
		'phone' => $request['phone'],
		'email' => $request['email'],
		'city' => $request['city'],
		'project_type' => $request['project_type'],
		'message' => $request['description'] . "\n\n" . implode( "\n", $details ),
		'source' => 'WEBSITE',
	);
}

/** Journal technique sans coordonnées, corps de réponse ni secret. */
function arkemis_core_quote_log( $event, $request_id, $context = array() ) {
	$allowed = array_intersect_key( $context, array_flip( array( 'status', 'error_code' ) ) );
	error_log( '[arkemis-core] website-inquiry ' . wp_json_encode( array_merge( array( 'event' => $event, 'request_id' => $request_id ), $allowed ) ) );
}

function arkemis_core_send_quote( $request ) {
	$token = arkemis_core_platform_token();
	if ( '' === $token ) {
		arkemis_core_quote_log( 'configuration_error', $request['request_id'], array( 'error_code' => 'missing_token' ) );
		return new WP_Error( 'arkemis_platform_configuration', 'Le jeton de la plateforme n’est pas configuré.' );
	}

	$response = wp_remote_post(
		'https://plateforme.arkemis.ca/api/public/website-inquiries',
		array(
			'timeout'            => 10,
			'redirection'        => 0,
			'reject_unsafe_urls' => true,
			'sslverify'          => true,
			'headers'            => array(
				'Accept'                    => 'application/json',
				'Content-Type'              => 'application/json',
				'X-Arkemis-Website-Token'   => $token,
				'X-Arkemis-Idempotency-Key' => $request['request_id'],
			),
			'body'                => wp_json_encode( arkemis_core_quote_payload( $request ) ),
			'data_format'         => 'body',
		)
	);

	if ( is_wp_error( $response ) ) {
		arkemis_core_quote_log( 'transport_error', $request['request_id'], array( 'error_code' => $response->get_error_code() ) );
		return $response;
	}
	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( $status < 200 || $status >= 300 ) {
		arkemis_core_quote_log( 'api_error', $request['request_id'], array( 'status' => $status ) );
		return new WP_Error( 'arkemis_platform_http_error', 'La plateforme a refusé la demande.', array( 'status' => $status ) );
	}

	arkemis_core_quote_log( 'accepted', $request['request_id'], array( 'status' => $status ) );
	return true;
}

function arkemis_core_quote_error_message( $error ) {
	if ( is_wp_error( $error ) && 'arkemis_platform_http_error' === $error->get_error_code() ) {
		$data = $error->get_error_data();
		$status = is_array( $data ) ? (int) ( $data['status'] ?? 0 ) : 0;
		if ( $status >= 400 && $status < 500 ) {
			return 'Le service n’a pas pu accepter votre demande. Vérifiez les informations du formulaire, puis réessayez ou écrivez à info@arkemis.ca.';
		}
		if ( $status >= 500 ) {
			return 'Le service de demande est temporairement indisponible. Réessayez plus tard ou écrivez à info@arkemis.ca.';
		}
	}
	return 'Votre demande n’a pas pu être envoyée. Réessayez ou écrivez directement à info@arkemis.ca.';
}

function arkemis_core_quote_state() {
	return $GLOBALS['arkemis_quote_state'] ?? array( 'values' => array(), 'errors' => array(), 'success' => false, 'quote_token' => '' );
}

add_action( 'template_redirect', function () {
	if ( ! arkemis_core_is_quote_page() ) {
		return;
	}
	arkemis_core_quote_disable_cache();
	$receipt = isset( $_GET['demande'] ) && is_string( $_GET['demande'] ) ? sanitize_text_field( wp_unslash( $_GET['demande'] ) ) : '';
	if ( preg_match( '/^[a-f0-9-]{36}$/', $receipt ) && get_transient( 'arkemis_quote_sent_' . $receipt ) ) {
		$GLOBALS['arkemis_quote_state'] = array( 'values' => array(), 'errors' => array(), 'success' => true );
	}
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['arkemis_quote_submit'] ) ) {
		return;
	}
	$input = wp_unslash( $_POST );
	$state = arkemis_core_validate_quote( $input );
	$state['success'] = false;
	$nonce = $input['arkemis_quote_nonce'] ?? '';
	$id = arkemis_core_quote_token_id( $input['arkemis_quote_token'] ?? '' );
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'arkemis_quote_submit' ) || ! $id ) {
		$state['errors']['form'] = 'Le formulaire a expiré. Vérifiez vos informations et envoyez de nouveau votre demande.';
	} else {
		$state['quote_token'] = $input['arkemis_quote_token'];
	}
	if ( ! $state['errors'] && ( ! isset( $input['website'] ) || ! is_string( $input['website'] ) || '' !== $input['website'] ) ) {
		$state['errors']['form'] = 'La demande n’a pas pu être validée. Rechargez la page et réessayez.';
	} elseif ( ! $state['errors'] && get_transient( 'arkemis_quote_sent_' . $id ) ) {
		wp_safe_redirect( add_query_arg( 'demande', $id, get_permalink() ) . '#demande-confirmation', 303 );
		exit;
	} elseif ( ! $state['errors'] ) {
		$ip = is_string( $_SERVER['REMOTE_ADDR'] ?? null ) ? $_SERVER['REMOTE_ADDR'] : '';
		$key = 'arkemis_quote_rate_' . hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
		$count = (int) get_transient( $key );
		if ( $count >= 10 ) {
			$state['errors']['form'] = 'Plusieurs tentatives ont été reçues. Réessayez dans 15 minutes ou écrivez à info@arkemis.ca.';
		} else {
			set_transient( $key, $count + 1, 15 * MINUTE_IN_SECONDS );
		}
	}
	if ( ! $state['errors'] ) {
		$lock = 'arkemis_quote_lock_' . $id;
		$locked_at = (int) get_option( $lock );
		if ( $locked_at && $locked_at < time() - 5 * MINUTE_IN_SECONDS ) {
			delete_option( $lock );
		}
		if ( ! add_option( $lock, time(), '', false ) ) {
			$state['errors']['form'] = 'Cette demande est déjà en cours de traitement. Patientez avant de réessayer.';
		} else {
			$request = array_merge( array( 'schema_version' => 1, 'request_id' => $id ), $state['values'] );
			try {
				$sent = arkemis_core_send_quote( $request );
				if ( true === $sent ) {
					set_transient( 'arkemis_quote_sent_' . $id, 1, DAY_IN_SECONDS );
				}
			} catch ( Throwable $error ) {
				arkemis_core_quote_log( 'unexpected_error', $id, array( 'error_code' => 'unexpected_exception' ) );
				$sent = new WP_Error( 'arkemis_platform_unexpected_error', 'Une erreur inattendue est survenue.' );
			} finally {
				delete_option( $lock );
			}
			if ( true === $sent ) {
				do_action( 'arkemis_quote_received', $request );
				wp_safe_redirect( add_query_arg( 'demande', $id, get_permalink() ) . '#demande-confirmation', 303 );
				exit;
			}
			$state['errors']['form'] = arkemis_core_quote_error_message( $sent );
		}
	}
	$GLOBALS['arkemis_quote_state'] = $state;
} );
