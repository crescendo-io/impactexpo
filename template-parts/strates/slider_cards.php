<?php
    $cards_items = get_sub_field('cards');
    $cards_title = get_sub_field('title');

    $buttons = get_sub_field('buttons_enable');
    $advanced = get_advanced_fields();

    $classNames = get_class_strate($advanced);
    $backgroundColor = get_background_strate($advanced);
    $backgroundCut = get_background_cut($advanced);
?>


<div class="strate container-slider-cards <?= $classNames ?>" <?= $backgroundColor; ?>>
    <?= $backgroundCut; ?>

    <div class="container">
        <div class="row">
            <div class="col-sm-<?= ($buttons) ? '8' : '12'; ?>">
                <?= $cards_title; ?>
            </div>

        </div>
    </div>

    <?php if($cards_items): ?>
    <div class="swiper" data-itemsdesk="4.2" data-itemstablet="3" data-itemsmobile="1.3">

        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">

            <?php
                foreach ($cards_items as $cards_item ):
                    $text = $cards_item['card_text'];
                    $image = $cards_item['card_image'];
                    $link = $cards_item['card_url'];
                    $imageArray = get_custom_thumb($image, 'full');
            ?>

            <div class="swiper-slide">
                <div class="card-slide">
                    <?php if($link): ?>
                    <a href="<?= $link['url']; ?>" target="<?= $link['target']; ?>">
                    <?php endif; ?>
                        <img src="<?= $imageArray['url']; ?>" alt="<?= $imageArray['alt']; ?>" width="<?= $imageArray['width']; ?>" height="<?= $imageArray['height']; ?>" loading="lazy">
                        <div class="text">
                            <?= $text; ?>
                        </div>
                    <?php if($link): ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>

        <?php if($cards_items && count($cards_items) > 4): ?>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <?php endif; ?>

    </div>

        <?php if($buttons): ?>
            <div class="col-sm-12 text-center">
                <?= get_template_part('template-parts/general/bloc-button'); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

