<?php
    $galerie_photo = get_post_thumbnail_id();


    if($galerie_photo){
        $galerie_photo_array = get_custom_thumb($galerie_photo, 'full');
    }
?>


<article id="post-<?php the_ID(); ?>" class="post-galerie col-sm-3">
    <header class="entry-header">
        <?php if ($galerie_photo) : ?>
            <a href="<?= get_the_permalink() ?>" >
                <img src="<?= $galerie_photo_array['url']; ?>" width="<?= $galerie_photo_array['width']; ?>" height="<?= $galerie_photo_array['height']; ?>" class="img-galerie" alt="<?= $galerie_photo_array['alt']; ?>" loading="lazy">
                <div class="entry-title">
                    <?php the_title(); ?>

                    <div class="sub-info">
                        <span class="posted-on">
                            <?php echo __('Publié le :', 'textdomain') . ' ' . get_the_date(); ?>
                        </span>

                    </div>
                </div>
            </a>
        <?php endif; ?>
    </header><!-- .entry-header -->


</article>
