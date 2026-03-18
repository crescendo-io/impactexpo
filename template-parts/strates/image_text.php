<?php
    $image          = get_sub_field('image');
    $imageArray     = get_custom_thumb($image);
    $title          = get_sub_field('title');
    $title_size    = get_sub_field('title_size');
    $title_color    = get_sub_field('title_color');
    $text           = get_sub_field('text');
    $order          = get_sub_field('order');

    // Advanded
    $advanced       = get_advanced_fields();

    $classNames = get_class_strate($advanced);
    $backgroundColor = get_background_strate($advanced);
    $backgroundCut = get_background_cut($advanced);

?>


<div class="strate container-image-text <?= $classNames ?>" <?= $backgroundColor; ?>>
    <?= $backgroundCut; ?>

    <div class="container">
        <div class="row">

            <?php if($order == 'left'): ?>
            <div class="col-sm-5">
                <?php if($imageArray['url']): ?>
                    <img src="<?= $imageArray['url']; ?>" class="image-strate" alt="<?= $imageArray['alt']; ?>" width="<?= $imageArray['width']; ?>" height="<?= $imageArray['height']; ?>" loading="lazy">
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($order == 'right'): ?>
            <div class="col-sm-5 visible-xs">
                <?php if($imageArray['url']): ?>
                    <img src="<?= $imageArray['url']; ?>" class="image-strate" alt="<?= $imageArray['alt']; ?>" loading="lazy">
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="col-sm-6 mx-auto">
                <div class="text-content">
                    <?= $text; ?>
                    <?= get_template_part('template-parts/general/bloc-button'); ?>
                </div>
            </div>

            <?php if($order == 'right'): ?>
                <div class="col-sm-5 hidden-xs">
                    <?php if($imageArray['url']): ?>
                        <img src="<?= $imageArray['url']; ?>" class="image-strate" alt="<?= $imageArray['alt']; ?>" loading="lazy">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


