<?php
/**
 * Strate Grille de modèles — catégories + cartes + étiquettes (ACF).
 *
 * @package Impactexpo
 */

$eyebrow   = get_sub_field( 'grille_modeles_eyebrow' );
$title     = get_sub_field( 'grille_modeles_title' );
$subtitle  = get_sub_field( 'grille_modeles_subtitle' );
$categories = get_sub_field( 'grille_modeles_categories' );

$advanced         = function_exists( 'get_advanced_fields' ) ? get_advanced_fields() : array();
$class_names      = function_exists( 'get_class_strate' ) ? get_class_strate( $advanced ) : '';
$background_color = function_exists( 'get_background_strate' ) ? get_background_strate( $advanced ) : '';
$background_cut   = function_exists( 'get_background_cut' ) ? get_background_cut( $advanced ) : '';

if ( ! is_array( $categories ) || ! count( $categories ) ) {
	return;
}
?>
<div class="strate strate-grille-modeles <?= esc_attr( $class_names ); ?>" <?= $background_color; ?>>
	<?= $background_cut; ?>
	<div class="container">
		<div class="strate-grille-modeles__intro">
			<?php if ( $eyebrow ) : ?>
				<p class="strate-grille-modeles__eyebrow"><?= esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="strate-grille-modeles__title"><?= esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $subtitle ) : ?>
				<p class="strate-grille-modeles__subtitle"><?= esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>

		<?php foreach ( $categories as $category ) : ?>
			<?php
			$cat_title = isset( $category['category_title'] ) ? $category['category_title'] : '';
			$cat_count = isset( $category['category_count'] ) ? trim( (string) $category['category_count'] ) : '';
			$cat_cols  = isset( $category['category_columns'] ) ? (string) $category['category_columns'] : '3';
			$cards     = isset( $category['category_cards'] ) && is_array( $category['category_cards'] ) ? $category['category_cards'] : array();

			if ( ! in_array( $cat_cols, array( '2', '3', '4' ), true ) ) {
				$cat_cols = '3';
			}

			$card_count = count( $cards );
			if ( '' === $cat_count && $card_count > 0 ) {
				$cat_count = sprintf(
					/* translators: %d: number of models */
					_n( '%d modèle', '%d modèles', $card_count, 'impactexpo' ),
					$card_count
				);
			}

			if ( ! $cat_title && ! $card_count ) {
				continue;
			}
			?>
			<section class="strate-grille-modeles__category">
				<div class="strate-grille-modeles__category-head">
					<?php if ( $cat_title ) : ?>
						<h3 class="strate-grille-modeles__category-title"><?= esc_html( $cat_title ); ?></h3>
					<?php endif; ?>
					<?php if ( $cat_count ) : ?>
						<span class="strate-grille-modeles__category-count"><?= esc_html( $cat_count ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $card_count ) : ?>
					<div class="strate-grille-modeles__grid strate-grille-modeles__grid--cols-<?= esc_attr( $cat_cols ); ?>">
						<?php foreach ( $cards as $card ) : ?>
							<?php
							$image = isset( $card['card_image'] ) ? $card['card_image'] : null;
							$c_title = isset( $card['card_title'] ) ? $card['card_title'] : '';
							$c_text  = isset( $card['card_text'] ) ? $card['card_text'] : '';
							$tags    = isset( $card['card_tags'] ) && is_array( $card['card_tags'] ) ? $card['card_tags'] : array();

							$img_url = '';
							$img_alt = $c_title ? $c_title : '';
							if ( is_array( $image ) ) {
								$img_url = ! empty( $image['url'] ) ? $image['url'] : '';
								if ( ! empty( $image['alt'] ) ) {
									$img_alt = $image['alt'];
								}
							} elseif ( is_numeric( $image ) ) {
								$img_url = wp_get_attachment_image_url( (int) $image, 'medium_large' );
								$img_alt = get_post_meta( (int) $image, '_wp_attachment_image_alt', true ) ?: $img_alt;
							}
							?>
							<article class="strate-grille-modeles__card">
								<div class="strate-grille-modeles__card-media">
									<?php if ( $img_url ) : ?>
										<img src="<?= esc_url( $img_url ); ?>" alt="<?= esc_attr( $img_alt ); ?>" loading="lazy">
									<?php else : ?>
										<span class="strate-grille-modeles__card-placeholder" aria-hidden="true"></span>
									<?php endif; ?>
								</div>
								<div class="strate-grille-modeles__card-body">
									<?php if ( $c_title ) : ?>
										<h4 class="strate-grille-modeles__card-title"><?= esc_html( $c_title ); ?></h4>
									<?php endif; ?>
									<?php if ( $c_text ) : ?>
										<p class="strate-grille-modeles__card-text"><?= wp_kses_post( $c_text ); ?></p>
									<?php endif; ?>
									<?php if ( count( $tags ) ) : ?>
										<ul class="strate-grille-modeles__tags">
											<?php foreach ( $tags as $tag ) : ?>
												<?php
												$label = isset( $tag['tag_label'] ) ? trim( (string) $tag['tag_label'] ) : '';
												if ( '' === $label ) {
													continue;
												}
												?>
												<li class="strate-grille-modeles__tag"><?= esc_html( $label ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
</div>
