<?php
/*
Template Name: Plan du site
*/
get_header(); ?>


<div class="strate-hero large dark" style="color: #ffffff; background: #0e445f">
    <div class="strate-hero_inner">
        <h1>Plan du site</h1>
    </div>

</div>

<div class="container">

    <div class="strate row">
        <div class="col-sm-4 mx-auto">
            <h2>Pages</h2>
            <ul>
                <?php
                $pages = get_pages();
                foreach ( $pages as $page ) {
                    $link = get_page_link( $page->ID );
                    echo '<li><a href="' . $link . '">' . $page->post_title . '</a></li>';
                }
                ?>
            </ul>
        </div>

        <div class="col-sm-4 mx-auto">
            <h2>Articles</h2>
            <ul>
                <?php
                $articles = new WP_Query( array( 'post_type' => 'articles', 'posts_per_page' => -1 ) );
                while ( $articles->have_posts() ) : $articles->the_post(); ?>
                    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
    </div>

</div>

<?php get_footer(); ?>
