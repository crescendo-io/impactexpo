<?php
    $option_logo_primary = get_field('option_logo_primary', 'option');
    $option_logo_primary_array = get_custom_thumb($option_logo_primary, 'full');

    $option_logo_scroll = get_field('option_logo_scroll', 'option');
    $option_logo_scroll_array = get_custom_thumb($option_logo_scroll, 'full');
?>

<div class="sentence-header">
    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium blanditiis dolorem fuga fugiat incidunt ipsum iusto laboriosam magni molestias, natus nobis nostrum quod quos repudiandae sequi sunt ut?
</div>
<header class="burger linear">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-5 col-2 visible-xs">
                <div class="button-menu">
                    <div class="barre"></div>
                    <div class="text">
                        menu
                    </div>
                </div>
            </div>

            <div class="col-sm-2 col-3">
                <?php if(isset($option_logo_primary_array['url']) && $option_logo_primary_array['url']): ?>
                <a href="<?= get_site_url(); ?>">
                    <img src="<?= $option_logo_primary_array['url']; ?>" class="logo" alt="<?= $option_logo_primary_array['alt']; ?>">
                    <img src="<?= $option_logo_scroll_array['url']; ?>" class="logo-scroll" alt="<?= $option_logo_scroll_array['alt']; ?>">
                </a>
                <?php endif; ?>
            </div>

            <div class="col-sm-10 col-2 text-right hidden-xs">
                <?= wp_nav_menu(array(
                    'menu'				=> "menu", // (int|string|WP_Term) Desired menu. Accepts a menu ID, slug, name, or object.
                    'menu_class'		=> "",
                    'container_class'	=> "menu",// (string) CSS class to use for the ul element which forms the menu. Default 'menu'.
                )); ?>
            </div>
        </div>
    </div>
    <?php custom_breadcrumb(); ?>
</header>
