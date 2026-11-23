<?php
/**
 * Strate Tableau comparatif — colonnes & lignes administrables via ACF.
 *
 * @package Impactexpo
 */

$eyebrow = get_sub_field( 'tableau_eyebrow' );
$title   = get_sub_field( 'tableau_title' );
$columns = get_sub_field( 'tableau_columns' );
$rows    = get_sub_field( 'tableau_rows' );
$notes   = get_sub_field( 'tableau_notes' );

$advanced         = function_exists( 'get_advanced_fields' ) ? get_advanced_fields() : array();
$class_names      = function_exists( 'get_class_strate' ) ? get_class_strate( $advanced ) : '';
$background_color = function_exists( 'get_background_strate' ) ? get_background_strate( $advanced ) : '';
$background_cut   = function_exists( 'get_background_cut' ) ? get_background_cut( $advanced ) : '';

$col_labels = array();
if ( is_array( $columns ) ) {
	foreach ( $columns as $col ) {
		$label = isset( $col['column_label'] ) ? trim( (string) $col['column_label'] ) : '';
		if ( '' !== $label ) {
			$col_labels[] = $label;
		}
	}
}

$col_count = count( $col_labels );
if ( $col_count < 1 ) {
	return;
}
?>
<div class="strate strate-tableau <?= esc_attr( $class_names ); ?>" <?= $background_color; ?>>
	<?= $background_cut; ?>
	<div class="container">
		<div class="strate-tableau__intro">
			<?php if ( $eyebrow ) : ?>
				<p class="strate-tableau__eyebrow"><?= esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="strate-tableau__title"><?= esc_html( $title ); ?></h2>
			<?php endif; ?>
		</div>

		<div class="strate-tableau__scroll">
			<table class="strate-tableau__table">
				<thead>
					<tr>
						<?php foreach ( $col_labels as $label ) : ?>
							<th scope="col"><?= esc_html( $label ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php if ( is_array( $rows ) ) : ?>
						<?php foreach ( $rows as $row ) : ?>
							<?php
							$cells  = isset( $row['tableau_cells'] ) && is_array( $row['tableau_cells'] ) ? $row['tableau_cells'] : array();
							$values = array();
							foreach ( $cells as $cell ) {
								$values[] = isset( $cell['cell_value'] ) ? $cell['cell_value'] : '';
							}
							while ( count( $values ) < $col_count ) {
								$values[] = '';
							}
							$values = array_slice( $values, 0, $col_count );
							?>
							<tr>
								<?php foreach ( $values as $i => $raw ) : ?>
									<?php
									$text           = trim( wp_strip_all_tags( (string) $raw ) );
									$is_placeholder = ( '' === $text || 0 === strcasecmp( $text, 'à compléter' ) || 0 === strcasecmp( $text, 'a completer' ) );
									$classes        = 'strate-tableau__cell';
									if ( 0 === $i ) {
										$classes .= ' strate-tableau__cell--first';
									}
									if ( $is_placeholder ) {
										$classes .= ' strate-tableau__cell--placeholder';
										$text = 'à compléter';
									}
									?>
									<td class="<?= esc_attr( $classes ); ?>">
										<?php if ( $is_placeholder ) : ?>
											<em><?= esc_html( $text ); ?></em>
										<?php else : ?>
											<?= wp_kses_post( nl2br( esc_html( $text ) ) ); ?>
										<?php endif; ?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php if ( is_array( $notes ) && count( $notes ) ) : ?>
			<ul class="strate-tableau__notes">
				<?php foreach ( $notes as $note ) : ?>
					<?php
					$note_text = isset( $note['note_text'] ) ? $note['note_text'] : '';
					if ( '' === trim( wp_strip_all_tags( (string) $note_text ) ) ) {
						continue;
					}
					?>
					<li class="strate-tableau__note"><?= wp_kses_post( $note_text ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
