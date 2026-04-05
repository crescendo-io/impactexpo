<?php
/**
 * Soumission formulaire devis : Brevo + e-mail avec PJ.
 *
 * wp-config.php :
 * - IMPACTEXPO_BREVO_API_KEY (obligatoire pour Brevo ; sinon seul l’e-mail part)
 * - IMPACTEXPO_BREVO_LEAD_LIST_ID (defaut 32)
 * - IMPACTEXPO_LEAD_NOTIFICATION_EMAIL (defaut admin_email)
 * - IMPACTEXPO_LEAD_PUBLIC_CONTACT_EMAIL (optionnel, texte d’aide PJ)
 *
 * Attributs Brevo attendus (cles exactes) : PRENOM, NOM, SMS, COMPANY_ADDRESS_LINE_1,
 * ADRESSE_SOCIETE, CODE_POSTAL_SOCIETE, VILLE_SOCIETE, VILLE, ADRESSE_PROJET,
 * CODE_POSTAL_PROJET, VILLE_PROJET, PRESCRIPTEUR, DESCRIPTION_PROJET, SOURCE
 * (URL page formulaire ; + optionnels : JOB_TITLE, LINKEDIN, COMMERCIAL, etc.).
 *
 * @package Impactexpo
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liste Brevo des leads devis.
 */
function impactexpo_lead_list_id() {
	return defined( 'IMPACTEXPO_BREVO_LEAD_LIST_ID' ) ? (int) IMPACTEXPO_BREVO_LEAD_LIST_ID : 32;
}

/**
 * Destinataire du récap e-mail.
 */
function impactexpo_lead_notification_email() {
	if ( defined( 'IMPACTEXPO_LEAD_NOTIFICATION_EMAIL' ) && is_email( IMPACTEXPO_LEAD_NOTIFICATION_EMAIL ) ) {
		return IMPACTEXPO_LEAD_NOTIFICATION_EMAIL;
	}
	return get_option( 'admin_email' );
}

/**
 * Extensions et taille max des PJ.
 */
function impactexpo_lead_allowed_file_types() {
	return array( 'jpg', 'jpeg', 'png', 'pdf', 'zip' );
}

/**
 * Traite les fichiers uploadés ; retourne [ 'paths' => [], 'errors' => [] ].
 */
function impactexpo_lead_process_uploads() {
	$max_files  = 3;
	$max_bytes  = 3 * MB_IN_BYTES;
	$allowed    = impactexpo_lead_allowed_file_types();
	$paths      = array();
	$errors     = array();

	if ( empty( $_FILES['lead_files'] ) || ! is_array( $_FILES['lead_files']['name'] ) ) {
		return compact( 'paths', 'errors' );
	}

	$names = $_FILES['lead_files']['name'];
	$count = min( $max_files, count( array_filter( $names ) ) );

	for ( $i = 0; $i < $count; $i++ ) {
		if ( empty( $names[ $i ] ) ) {
			continue;
		}
		if ( ! empty( $_FILES['lead_files']['error'][ $i ] ) && UPLOAD_ERR_NO_FILE !== (int) $_FILES['lead_files']['error'][ $i ] ) {
			$errors[] = sprintf(
				/* translators: %s: file name */
				__( 'Erreur lors du transfert du fichier « %s ».', 'impactexpo' ),
				sanitize_file_name( $names[ $i ] )
			);
			continue;
		}
		if ( UPLOAD_ERR_NO_FILE === (int) $_FILES['lead_files']['error'][ $i ] ) {
			continue;
		}
		if ( (int) $_FILES['lead_files']['size'][ $i ] > $max_bytes ) {
			$errors[] = sprintf(
				/* translators: %s: file name */
				__( 'Le fichier « %s » dépasse 3 Mo.', 'impactexpo' ),
				sanitize_file_name( $names[ $i ] )
			);
			continue;
		}

		$filetype = wp_check_filetype( $names[ $i ], null );
		$ext      = strtolower( $filetype['ext'] );
		if ( ! $ext || ! in_array( $ext, $allowed, true ) ) {
			$errors[] = sprintf(
				/* translators: %s: file name */
				__( 'Type de fichier non autorisé : %s', 'impactexpo' ),
				sanitize_file_name( $names[ $i ] )
			);
			continue;
		}

		$tmp = $_FILES['lead_files']['tmp_name'][ $i ];
		if ( ! is_uploaded_file( $tmp ) ) {
			$errors[] = __( 'Fichier invalide.', 'impactexpo' );
			continue;
		}

		$upload_dir = wp_upload_dir();
		if ( $upload_dir['error'] ) {
			$errors[] = $upload_dir['error'];
			break;
		}

		$subdir = '/impactexpo-leads/' . gmdate( 'Y/m' );
		$dir    = $upload_dir['basedir'] . $subdir;
		if ( ! wp_mkdir_p( $dir ) ) {
			$errors[] = __( 'Impossible de créer le dossier de réception.', 'impactexpo' );
			break;
		}

		$basename = sanitize_file_name( pathinfo( $names[ $i ], PATHINFO_FILENAME ) );
		$basename = $basename ? $basename : 'fichier';
		$dest     = $dir . '/' . wp_unique_filename( $dir, $basename . '.' . $ext );

		if ( ! move_uploaded_file( $tmp, $dest ) ) {
			$errors[] = sprintf(
				/* translators: %s: file name */
				__( 'Enregistrement impossible pour « %s ».', 'impactexpo' ),
				sanitize_file_name( $names[ $i ] )
			);
			continue;
		}

		$paths[] = $dest;
	}

	return compact( 'paths', 'errors' );
}

/**
 * Supprime les fichiers temporaires après envoi.
 *
 * @param string[] $paths Chemins absolus.
 */
function impactexpo_lead_cleanup_files( array $paths ) {
	foreach ( $paths as $path ) {
		if ( is_string( $path ) && is_file( $path ) ) {
			wp_delete_file( $path );
		}
	}
}

/**
 * Formate le téléphone pour l’attribut SMS Brevo (E.164, ex. +33607080910).
 *
 * @param string $phone Numéro saisi par l’utilisateur.
 */
function impactexpo_normalize_phone_for_brevo( $phone ) {
	$p = preg_replace( '/[\s().\-]/', '', (string) $phone );
	if ( '' === $p ) {
		return '';
	}
	// Déjà E.164 valide minimal (+ puis chiffres).
	if ( preg_match( '/^\+[1-9]\d{6,14}$/', $p ) ) {
		return $p;
	}
	// France métropolitaine : 0X XX XX XX XX (10 chiffres).
	if ( preg_match( '/^0([1-9])(\d{8})$/', $p, $m ) ) {
		return '+33' . $m[1] . $m[2];
	}
	// 33XXXXXXXXX sans préfixe +.
	if ( preg_match( '/^33([1-9]\d{8})$/', $p, $m ) ) {
		return '+33' . $m[1];
	}

	return $p;
}

/**
 * Vérifie que le mobile, une fois normalisé, est un E.164 valide pour Brevo.
 *
 * @param string $mobile Valeur saisie.
 */
function impactexpo_lead_mobile_is_valid( $mobile ) {
	$normalized = impactexpo_normalize_phone_for_brevo( $mobile );
	return (bool) preg_match( '/^\+[1-9]\d{6,14}$/', $normalized );
}

/**
 * Code postal français métropolitain / Corse (5 chiffres).
 *
 * @param string $zip Valeur saisie.
 */
function impactexpo_lead_fr_zip_valid( $zip ) {
	return (bool) preg_match( '/^\d{5}$/', $zip );
}

/**
 * Texte avec au moins une lettre (prénom, nom, ville…).
 *
 * @param string $value Valeur.
 */
function impactexpo_lead_has_letter( $value ) {
	return (bool) preg_match( '/\p{L}/u', $value );
}

/**
 * Valeurs manifestement factices (saisie « test », etc.).
 *
 * @param string $value Valeur.
 */
function impactexpo_lead_is_placeholder_text( $value ) {
	$v = mb_strtolower( trim( $value ), 'UTF-8' );
	$bad = array( 'test', 'tests', 'xxx', 'xxxx', 'aaa', 'aaaa', 'abc', 'lorem', 'ipsum', 'n/a', 'na', 'none', 'rien', 'xxx xxx' );
	return in_array( $v, $bad, true );
}

/**
 * Contrôle les formats avant traitement (complète required / is_email).
 *
 * @param array $data Données déjà sanitizées.
 * @return true|\WP_Error
 */
function impactexpo_lead_validate_field_formats( array $data ) {
	$company_name = $data['company_name'];
	if ( mb_strlen( $company_name ) < 2 || mb_strlen( $company_name ) > 120 ) {
		return new WP_Error( 'bad_company', __( 'Le nom de la société doit contenir entre 2 et 120 caractères.', 'impactexpo' ) );
	}
	if ( ! impactexpo_lead_has_letter( $company_name ) ) {
		return new WP_Error( 'bad_company', __( 'Le nom de la société doit contenir au moins une lettre.', 'impactexpo' ) );
	}
	if ( impactexpo_lead_is_placeholder_text( $company_name ) ) {
		return new WP_Error( 'bad_company', __( 'Merci d’indiquer le véritable nom de votre société.', 'impactexpo' ) );
	}

	$addr = $data['company_address'];
	if ( mb_strlen( $addr ) < 5 || mb_strlen( $addr ) > 250 ) {
		return new WP_Error( 'bad_address', __( 'L’adresse de la société doit contenir entre 5 et 250 caractères.', 'impactexpo' ) );
	}
	if ( ! impactexpo_lead_has_letter( $addr ) ) {
		return new WP_Error( 'bad_address', __( 'L’adresse de la société doit contenir au moins une lettre.', 'impactexpo' ) );
	}

	if ( ! impactexpo_lead_fr_zip_valid( $data['company_zip'] ) ) {
		return new WP_Error( 'bad_zip', __( 'Le code postal de la société doit comporter exactement 5 chiffres.', 'impactexpo' ) );
	}

	$city = $data['company_city'];
	if ( mb_strlen( $city ) < 2 || mb_strlen( $city ) > 100 ) {
		return new WP_Error( 'bad_city', __( 'La ville doit contenir entre 2 et 100 caractères.', 'impactexpo' ) );
	}
	if ( ! impactexpo_lead_has_letter( $city ) ) {
		return new WP_Error( 'bad_city', __( 'La ville doit contenir au moins une lettre.', 'impactexpo' ) );
	}
	if ( impactexpo_lead_is_placeholder_text( $city ) ) {
		return new WP_Error( 'bad_city', __( 'Merci d’indiquer le nom réel de la ville.', 'impactexpo' ) );
	}

	foreach ( array( 'first_name' => __( 'prénom', 'impactexpo' ), 'last_name' => __( 'nom', 'impactexpo' ) ) as $field => $label ) {
		$v = $data[ $field ];
		if ( mb_strlen( $v ) < 2 || mb_strlen( $v ) > 80 ) {
			return new WP_Error(
				'bad_name',
				sprintf(
					/* translators: %s: prénom or nom */
					__( 'Votre %s doit contenir entre 2 et 80 caractères.', 'impactexpo' ),
					$label
				)
			);
		}
		if ( ! impactexpo_lead_has_letter( $v ) ) {
			return new WP_Error(
				'bad_name',
				sprintf(
					/* translators: %s: prénom or nom */
					__( 'Votre %s doit contenir au moins une lettre.', 'impactexpo' ),
					$label
				)
			);
		}
		if ( impactexpo_lead_is_placeholder_text( $v ) ) {
			return new WP_Error(
				'bad_name',
				sprintf(
					/* translators: %s: prénom or nom */
					__( 'Merci d’indiquer votre véritable %s.', 'impactexpo' ),
					$label
				)
			);
		}
	}

	if ( ! is_email( $data['email'] ) || strlen( $data['email'] ) > 100 ) {
		return new WP_Error( 'bad_email', __( 'Veuillez indiquer une adresse e-mail valide.', 'impactexpo' ) );
	}

	if ( ! impactexpo_lead_mobile_is_valid( $data['mobile'] ) ) {
		return new WP_Error(
			'bad_phone',
			__( 'Le numéro de portable n’est pas valide. Utilisez un numéro français (ex. 06 12 34 56 78) ou le format international (+33…).', 'impactexpo' )
		);
	}

	$proj_zip = $data['project_zip'];
	if ( '' !== $proj_zip && ! impactexpo_lead_fr_zip_valid( $proj_zip ) ) {
		return new WP_Error( 'bad_project_zip', __( 'Le code postal du projet doit comporter exactement 5 chiffres.', 'impactexpo' ) );
	}

	$proj_addr = $data['project_address'];
	$proj_city = $data['project_city'];
	if ( '' !== $proj_addr || '' !== $proj_city ) {
		if ( ! impactexpo_lead_fr_zip_valid( $proj_zip ) ) {
			return new WP_Error(
				'bad_project_zip',
				__( 'Renseignez un code postal du projet valide (5 chiffres) lorsque l’adresse ou la ville du projet est indiquée.', 'impactexpo' )
			);
		}
		if ( '' !== $proj_addr && ( mb_strlen( $proj_addr ) < 3 || mb_strlen( $proj_addr ) > 250 || ! impactexpo_lead_has_letter( $proj_addr ) ) ) {
			return new WP_Error( 'bad_project_addr', __( 'L’adresse du projet doit être complète (au moins 3 caractères et une lettre).', 'impactexpo' ) );
		}
		if ( '' !== $proj_city && ( mb_strlen( $proj_city ) < 2 || ! impactexpo_lead_has_letter( $proj_city ) ) ) {
			return new WP_Error( 'bad_project_city', __( 'La ville du projet doit contenir au moins 2 lettres.', 'impactexpo' ) );
		}
	}

	$desc = $data['description'];
	if ( '' !== $desc ) {
		if ( mb_strlen( $desc ) < 3 || mb_strlen( $desc ) > 10000 ) {
			return new WP_Error( 'bad_desc', __( 'La description doit faire au moins 3 caractères si vous la renseignez.', 'impactexpo' ) );
		}
		if ( impactexpo_lead_is_placeholder_text( $desc ) ) {
			return new WP_Error( 'bad_desc', __( 'Merci de décrire votre projet de façon plus précise.', 'impactexpo' ) );
		}
	}

	return true;
}

/**
 * Nettoie une URL : même domaine que le site, longueur raisonnable.
 *
 * @param string $raw URL brute (ex. envoyée par le navigateur).
 */
function impactexpo_lead_sanitize_source_url( $raw ) {
	$url = esc_url_raw( trim( (string) $raw ) );
	if ( '' === $url || strlen( $url ) > 2000 ) {
		return '';
	}
	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$src_host  = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $src_host || ! $site_host ) {
		return '';
	}
	if ( strtolower( $src_host ) !== strtolower( (string) $site_host ) ) {
		return '';
	}

	return $url;
}

/**
 * URL de la page contenant le formulaire (POST puis referer HTTP).
 *
 * @param string $post_raw Valeur du champ source_url.
 */
function impactexpo_lead_resolve_source_url( $post_raw ) {
	$u = impactexpo_lead_sanitize_source_url( $post_raw );
	if ( '' !== $u ) {
		return $u;
	}
	$ref = wp_get_referer();
	if ( $ref ) {
		$u = impactexpo_lead_sanitize_source_url( $ref );
		if ( '' !== $u ) {
			return $u;
		}
	}

	return home_url( '/' );
}

/**
 * Envoie le contact vers Brevo.
 *
 * @param array $data Données sanitizées.
 * @return true|\WP_Error
 */
function impactexpo_lead_send_brevo( array $data ) {
	if ( ! defined( 'IMPACTEXPO_BREVO_API_KEY' ) || '' === IMPACTEXPO_BREVO_API_KEY ) {
		return new WP_Error( 'no_key', __( 'Clé API Brevo absente.', 'impactexpo' ) );
	}

	$list_id = impactexpo_lead_list_id();
	if ( $list_id < 1 ) {
		return new WP_Error( 'bad_list', __( 'ID de liste Brevo invalide.', 'impactexpo' ) );
	}

	// Cles = noms exacts des attributs dans Brevo (PRENOM / NOM, pas FIRSTNAME / LASTNAME).
	// SMS : Brevo exige un format E.164 (sinon HTTP 400 "Invalid phone number").
	$sms = impactexpo_normalize_phone_for_brevo( $data['mobile'] );

	$attributes = array(
		'PRENOM'                 => $data['first_name'],
		'NOM'                    => $data['last_name'],
		'SMS'                    => $sms,
		'COMPANY_ADDRESS_LINE_1' => $data['company_name'],
		'ADRESSE_SOCIETE'        => $data['company_address'],
		'CODE_POSTAL_SOCIETE'    => $data['company_zip'],
		'VILLE_SOCIETE'          => $data['company_city'],
		'VILLE'                  => $data['company_city'],
		'ADRESSE_PROJET'         => $data['project_address'],
		'CODE_POSTAL_PROJET'     => $data['project_zip'],
		'VILLE_PROJET'           => $data['project_city'],
		'PRESCRIPTEUR'           => $data['prescriber'] ? __( 'Oui', 'impactexpo' ) : __( 'Non', 'impactexpo' ),
		'DESCRIPTION_PROJET'     => $data['description'],
		'SOURCE'                 => isset( $data['source_url'] ) ? $data['source_url'] : '',
	);

	/**
	 * Filtre les attributs envoyés à Brevo (noms = champs Brevo).
	 *
	 * @param array $attributes Attributs.
	 * @param array $data       Données source.
	 */
	$attributes = apply_filters( 'impactexpo_brevo_lead_attributes', $attributes, $data );

	// Brevo refuse souvent les attributs vides ou types incohérents : on n’envoie que les valeurs non vides.
	foreach ( $attributes as $attr_key => $attr_val ) {
		if ( $attr_val === '' || $attr_val === null ) {
			unset( $attributes[ $attr_key ] );
		}
	}

	$body = array(
		'email'         => $data['email'],
		'attributes'    => $attributes,
		'listIds'       => array( $list_id ),
		'updateEnabled' => true,
	);

	$api_key = trim( (string) IMPACTEXPO_BREVO_API_KEY );

	$response = wp_remote_post(
		'https://api.brevo.com/v3/contacts',
		array(
			'timeout' => 25,
			'headers' => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json; charset=UTF-8',
				'api-key'      => $api_key,
			),
			'body'    => wp_json_encode( $body, JSON_UNESCAPED_UNICODE ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$msg  = wp_remote_retrieve_body( $response );

	// 201 créé, 204 mis à jour, 200 selon version — 0 = souvent échec réseau / SSL non remonté comme WP_Error.
	if ( $code < 200 || $code >= 300 ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'Impact Expo Brevo: HTTP ' . $code . ' ' . $msg );
		}
		return new WP_Error(
			'brevo_http',
			__( 'Le CRM n’a pas pu enregistrer le contact. Votre demande a tout de même été transmise par e-mail.', 'impactexpo' ),
			array(
				'status' => $code,
				'body'   => $msg,
			)
		);
	}

	return true;
}

/**
 * Bloc texte ajouté au mail interne pour diagnostiquer Brevo.
 *
 * @param true|\WP_Error $brevo_ok Résultat de impactexpo_lead_send_brevo().
 */
function impactexpo_lead_brevo_status_footer( $brevo_ok ) {
	$list_id = impactexpo_lead_list_id();
	// Éviter une ligne « --- » seule : Gmail masque parfois la suite comme signature.
	$lines   = array( '', '==========', '[Brevo / synchronisation contact]' );

	if ( true === $brevo_ok ) {
		$lines[] = sprintf(
			/* translators: %d: list ID */
			__( 'OK — contact enregistré ou mis à jour (liste n°%d).', 'impactexpo' ),
			$list_id
		);
		return "\n" . implode( "\n", $lines );
	}

	if ( is_wp_error( $brevo_ok ) ) {
		$lines[] = $brevo_ok->get_error_message();
		$errdata = $brevo_ok->get_error_data();
		if ( is_array( $errdata ) ) {
			if ( ! empty( $errdata['status'] ) ) {
				$lines[] = 'HTTP ' . (int) $errdata['status'];
			}
			if ( ! empty( $errdata['body'] ) ) {
				$snippet = wp_strip_all_tags( (string) $errdata['body'] );
				if ( strlen( $snippet ) > 1200 ) {
					$snippet = substr( $snippet, 0, 1200 ) . '…';
				}
				$lines[] = $snippet;
			}
		}
		return "\n" . implode( "\n", $lines );
	}

	return '';
}

/**
 * Corps texte du mail récapitulatif.
 */
function impactexpo_lead_build_email_body( array $data ) {
	$lines = array(
		__( 'Nouvelle demande de devis', 'impactexpo' ),
		'',
		__( 'Page du formulaire', 'impactexpo' ) . ': ' . ( isset( $data['source_url'] ) ? $data['source_url'] : '' ),
		'',
		__( 'Société', 'impactexpo' ) . ': ' . $data['company_name'],
		__( 'Adresse société', 'impactexpo' ) . ': ' . $data['company_address'],
		__( 'Code postal', 'impactexpo' ) . ': ' . $data['company_zip'],
		__( 'Ville', 'impactexpo' ) . ': ' . $data['company_city'],
		'',
		__( 'Adresse projet différente', 'impactexpo' ) . ': ' . ( $data['project_address_diff'] ? __( 'Oui', 'impactexpo' ) : __( 'Non', 'impactexpo' ) ),
		__( 'Adresse projet', 'impactexpo' ) . ': ' . $data['project_address'],
		__( 'Code postal projet', 'impactexpo' ) . ': ' . $data['project_zip'],
		__( 'Ville projet', 'impactexpo' ) . ': ' . $data['project_city'],
		'',
		__( 'Prénom', 'impactexpo' ) . ': ' . $data['first_name'],
		__( 'Nom', 'impactexpo' ) . ': ' . $data['last_name'],
		__( 'E-mail', 'impactexpo' ) . ': ' . $data['email'],
		__( 'Portable', 'impactexpo' ) . ': ' . $data['mobile'],
		__( 'Prescripteur', 'impactexpo' ) . ': ' . ( $data['prescriber'] ? __( 'Oui', 'impactexpo' ) : __( 'Non', 'impactexpo' ) ),
		'',
		__( 'Description du projet', 'impactexpo' ) . ':',
		$data['description'],
		'',
		__( 'Consentement RGPD', 'impactexpo' ) . ': ' . ( ! empty( $data['rgpd_consent'] ) ? __( 'Accepté', 'impactexpo' ) : '' ),
	);

	return implode( "\n", $lines );
}

/**
 * Handler AJAX.
 */
function impactexpo_ajax_submit_lead() {
	check_ajax_referer( 'impactexpo_submit_lead', 'impactexpo_lead_nonce' );

	if ( ! empty( $_POST['impactexpo_hp'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Merci, votre demande a bien été envoyée.', 'impactexpo' ) ) );
	}

	if ( empty( $_POST['rgpd_consent'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Veuillez accepter la politique de confidentialité pour envoyer le formulaire.', 'impactexpo' ),
			),
			400
		);
	}

	$required = array(
		'company_name'    => 'sanitize_text_field',
		'company_address' => 'sanitize_text_field',
		'company_zip'     => 'sanitize_text_field',
		'company_city'    => 'sanitize_text_field',
		'first_name'      => 'sanitize_text_field',
		'last_name'       => 'sanitize_text_field',
		'email'           => 'sanitize_email',
		'mobile'          => 'sanitize_text_field',
	);

	$data = array();
	foreach ( $required as $field => $cb ) {
		$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
		$data[ $field ] = call_user_func( $cb, $raw );
		if ( 'email' === $field ) {
			if ( ! is_email( $data[ $field ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Adresse e-mail invalide.', 'impactexpo' ) ), 400 );
			}
		} elseif ( '' === $data[ $field ] ) {
			wp_send_json_error( array( 'message' => __( 'Veuillez remplir tous les champs obligatoires.', 'impactexpo' ) ), 400 );
		}
	}

	$data['project_address_diff'] = ! empty( $_POST['project_address_diff'] );
	$data['project_address']      = isset( $_POST['project_address'] ) ? sanitize_text_field( wp_unslash( $_POST['project_address'] ) ) : '';
	$data['project_zip']          = isset( $_POST['project_zip'] ) ? sanitize_text_field( wp_unslash( $_POST['project_zip'] ) ) : '';
	$data['project_city']         = isset( $_POST['project_city'] ) ? sanitize_text_field( wp_unslash( $_POST['project_city'] ) ) : '';
	$data['prescriber']           = ! empty( $_POST['prescriber'] );
	$data['description']          = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
	$data['rgpd_consent']         = true;
	$data['source_url']           = impactexpo_lead_resolve_source_url(
		isset( $_POST['source_url'] ) ? wp_unslash( $_POST['source_url'] ) : ''
	);

	$formats_ok = impactexpo_lead_validate_field_formats( $data );
	if ( is_wp_error( $formats_ok ) ) {
		wp_send_json_error( array( 'message' => $formats_ok->get_error_message() ), 400 );
	}

	$uploads = impactexpo_lead_process_uploads();
	if ( ! empty( $uploads['errors'] ) ) {
		foreach ( $uploads['paths'] as $p ) {
			wp_delete_file( $p );
		}
		wp_send_json_error(
			array(
				'message' => implode( ' ', $uploads['errors'] ),
			),
			400
		);
	}

	$attachment_paths = $uploads['paths'];

	$brevo_ok = impactexpo_lead_send_brevo( $data );
	if ( is_wp_error( $brevo_ok ) && defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( 'Impact Expo Brevo: ' . $brevo_ok->get_error_message() );
	}

	$brevo_footer = impactexpo_lead_brevo_status_footer( $brevo_ok );

	$files_block = '';
	if ( ! empty( $attachment_paths ) ) {
		$files_block = "\n\n" . __( 'Fichiers joints', 'impactexpo' ) . ":\n- " . implode( "\n- ", array_map( 'basename', $attachment_paths ) );
	}

	$to       = impactexpo_lead_notification_email();
	$subject  = sprintf(
		/* translators: %s: company name */
		__( '[Impact Expo] Nouveau devis — %s', 'impactexpo' ),
		$data['company_name']
	);
	$body     = impactexpo_lead_build_email_body( $data ) . $files_block . $brevo_footer;
	$headers  = array( 'Content-Type: text/plain; charset=UTF-8' );
	$mail_ok  = wp_mail( $to, $subject, $body, $headers, $attachment_paths );

	impactexpo_lead_cleanup_files( $attachment_paths );

	if ( ! $mail_ok ) {
		wp_send_json_error(
			array(
				'message' => __( 'L’envoi du message a échoué. Veuillez réessayer ou nous contacter par téléphone.', 'impactexpo' ),
			),
			500
		);
	}

	$message = __( 'Merci, votre demande a bien été envoyée. Nous vous répondrons sous 24h.', 'impactexpo' );
	if ( is_wp_error( $brevo_ok ) ) {
		$message = __( 'Merci, votre demande a bien été transmise par e-mail.', 'impactexpo' );
	}

	wp_send_json_success( array( 'message' => $message ) );
}
add_action( 'wp_ajax_impactexpo_submit_lead', 'impactexpo_ajax_submit_lead' );
add_action( 'wp_ajax_nopriv_impactexpo_submit_lead', 'impactexpo_ajax_submit_lead' );
