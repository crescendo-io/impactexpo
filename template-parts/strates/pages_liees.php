<?php
/**
 * Strate Pages liées (maillage interne).
 *
 * @package Impactexpo
 */

$eyebrow = get_sub_field( 'pages_liees_eyebrow' );
$title   = get_sub_field( 'pages_liees_title' );
$columns = get_sub_field( 'pages_liees_columns' );
$items   = get_sub_field( 'pages_liees_items' );

$advanced         = function_exists( 'get_advanced_fields' ) ? get_advanced_fields() : array();
$class_names      = function_exists( 'get_class_strate' ) ? get_class_strate( $advanced ) : '';
$background_color = function_exists( 'get_background_strate' ) ? get_background_strate( $advanced ) : '';
$background_cut   = function_exists( 'get_background_cut' ) ? get_background_cut( $advanced ) : '';

$columns = (string) $columns;
if ( ! in_array( $columns, array( '2', '3', '4' ), true ) ) {
	$columns = '4';
}

if ( ! is_array( $items ) || ! count( $items ) ) {
	return;
}
?>
<div class="strate strate-pages-liees <?= esc_attr( $class_names ); ?>" <?= $background_color; ?>>
	<?= $background_cut; ?>
	<div class="container">
		<div class="strate-pages-liees__intro">
			<?php if ( $eyebrow ) : ?>
				<p class="strate-pages-liees__eyebrow"><?= esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="strate-pages-liees__title"><?= esc_html( $title ); ?></h2>
			<?php endif; ?>
		</div>

		<div class="strate-pages-liees__grid strate-pages-liees__grid--cols-<?= esc_attr( $columns ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$item_title = isset( $item['item_title'] ) ? trim( (string) $item['item_title'] ) : '';
				$link       = isset( $item['item_link'] ) ? $item['item_link'] : null;

				$url    = '';
				$label  = '';
				$target = '_self';
				$rel    = '';

				if ( is_array( $link ) ) {
					$url    = ! empty( $link['url'] ) ? $link['url'] : '';
					$label  = ! empty( $link['title'] ) ? $link['title'] : '';
					$target = ! empty( $link['target'] ) ? $link['target'] : '_self';
					if ( '_blank' === $target ) {
						$rel = 'noopener noreferrer';
					}
				}

				if ( '' === $item_title && '' === $url ) {
					continue;
				}

				$tag   = $url ? 'a' : 'div';
				$class = 'strate-pages-liees__card';
				?>
				<<?= $tag; ?>
					class="<?= esc_attr( $class ); ?>"
					<?php if ( $url ) : ?>
						href="<?= esc_url( $url ); ?>"
						<?php if ( $target ) : ?>target="<?= esc_attr( $target ); ?>"<?php endif; ?>
						<?php if ( $rel ) : ?>rel="<?= esc_attr( $rel ); ?>"<?php endif; ?>
					<?php endif; ?>
				>
					<?php if ( $item_title ) : ?>
						<span class="strate-pages-liees__card-title"><?= esc_html( $item_title ); ?></span>
					<?php endif; ?>
					<?php if ( $label || $url ) : ?>
						<span class="strate-pages-liees__card-link">
							<?= esc_html( $label ? $label : __( 'En savoir plus', 'impactexpo' ) ); ?>
							<span class="strate-pages-liees__card-arrow" aria-hidden="true">→</span>
						</span>
					<?php endif; ?>
				</<?= $tag; ?>>
			<?php endforeach; ?>
		</div>
	</div>
</div>
