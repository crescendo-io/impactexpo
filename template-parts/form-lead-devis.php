<?php
/**
 * Formulaire devis (Hub + Produit) — envoi AJAX vers Brevo + e-mail.
 *
 * @package Impactexpo
 */

$extra_contact = defined( 'IMPACTEXPO_LEAD_PUBLIC_CONTACT_EMAIL' ) && IMPACTEXPO_LEAD_PUBLIC_CONTACT_EMAIL
	? IMPACTEXPO_LEAD_PUBLIC_CONTACT_EMAIL
	: get_option( 'admin_email' );

$privacy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
?>
<form id="impactexpo-lead-form" class="impactexpo-lead-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<?php wp_nonce_field( 'impactexpo_submit_lead', 'impactexpo_lead_nonce' ); ?>
	<input type="text" name="impactexpo_hp" value="" class="impactexpo-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
	<input type="hidden" name="source_url" id="impactexpo-source-url" value="">

	<p id="impactexpo-lead-feedback" class="impactexpo-lead-feedback" role="alert" hidden></p>

	<div id="impactexpo-lead-body" class="impactexpo-lead-body">
	<label class="impactexpo-field">
		<span class="impactexpo-label">Nom de la société <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="company_name" required autocomplete="organization" maxlength="120" minlength="2">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Adresse complète de votre société <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="company_address" required autocomplete="street-address" maxlength="250" minlength="5">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Code postal de votre société <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="company_zip" required autocomplete="postal-code" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" title="<?php echo esc_attr__( '5 chiffres, sans espace', 'impactexpo' ); ?>">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Ville de votre société <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="company_city" required autocomplete="address-level2" maxlength="100" minlength="2">
	</label>

	<label class="impactexpo-checkbox">
		<input type="checkbox" name="project_address_diff" id="impactexpo-project-diff" value="1">
		<span>Adresse du projet différente ?</span>
	</label>

	<div id="impactexpo-project-fields" class="impactexpo-project-fields is-hidden" hidden>
		<label class="impactexpo-field">
			<span class="impactexpo-label">Adresse de votre projet (si différente)</span>
			<input type="text" name="project_address" autocomplete="street-address" maxlength="250">
		</label>
		<label class="impactexpo-field">
			<span class="impactexpo-label">Code postal de votre projet (si différente)</span>
			<input type="text" name="project_zip" autocomplete="postal-code" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" title="<?php echo esc_attr__( '5 chiffres, sans espace', 'impactexpo' ); ?>">
		</label>
		<label class="impactexpo-field">
			<span class="impactexpo-label">Ville de votre projet (si différente)</span>
			<input type="text" name="project_city" autocomplete="address-level2" maxlength="100">
		</label>
	</div>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Votre prénom <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="first_name" required autocomplete="given-name" maxlength="80" minlength="2">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Votre nom <abbr title="obligatoire">*</abbr></span>
		<input type="text" name="last_name" required autocomplete="family-name" maxlength="80" minlength="2">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Votre e-mail <abbr title="obligatoire">*</abbr></span>
		<input type="email" name="email" required autocomplete="email" maxlength="100">
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Numéro de portable <abbr title="obligatoire">*</abbr></span>
		<input type="tel" name="mobile" required autocomplete="tel" maxlength="20" placeholder="<?php echo esc_attr__( 'Ex. 06 12 34 56 78', 'impactexpo' ); ?>">
	</label>

	<label class="impactexpo-checkbox">
		<input type="checkbox" name="prescriber" id="impactexpo-prescriber" value="1">
		<span>Je suis prescripteur (architecte, décorateur, agenceur…)</span>
	</label>

	<label class="impactexpo-field">
		<span class="impactexpo-label">Description du projet</span>
		<textarea name="description" rows="8" cols="30" maxlength="10000"></textarea>
	</label>

	<div class="impactexpo-field impactexpo-field-files">
		<span class="impactexpo-label">Ajouter jusqu’à 3 fichiers</span>
		<p class="impactexpo-files-hint">(seuls les fichiers .jpg, .png, .pdf et .zip sont autorisés), 3&nbsp;Mo maximum par fichier.</p>
		<input type="file" name="lead_files[]" id="impactexpo-lead-files" accept=".jpg,.jpeg,.png,.pdf,.zip,image/jpeg,image/png,application/pdf,application/zip" multiple>
		<ul id="impactexpo-file-list" class="impactexpo-file-list" hidden aria-live="polite"></ul>
	</div>

	<p class="impactexpo-files-contact-note">
		Si vous souhaitez insérer plus de pièces jointes, vous pouvez nous les envoyer sur
		<a href="mailto:<?php echo esc_attr( $extra_contact ); ?>"><?php echo esc_html( $extra_contact ); ?></a>
	</p>

	<label class="impactexpo-checkbox impactexpo-rgpd">
		<input type="checkbox" name="rgpd_consent" id="impactexpo-rgpd" value="1" required>
		<span class="impactexpo-rgpd-text">
			<?php if ( $privacy_url ) : ?>
				J’accepte que mes données personnelles soient utilisées pour répondre à ma demande de contact / devis, conformément à la
				<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.
			<?php else : ?>
				J’accepte que mes données personnelles soient utilisées pour répondre à ma demande de contact / devis.
			<?php endif; ?>
			<abbr title="obligatoire">*</abbr>
		</span>
	</label>

	<button type="submit" class="button primary impactexpo-lead-submit">ENVOYER</button>
	</div>
</form>
