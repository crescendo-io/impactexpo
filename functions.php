<?php


function add_hreflang_tags() {
    // Définir l'URL de la version par défaut (x-default) du site
    $default_url = get_home_url(); // ou mettre une URL spécifique

    // Obtenir l'URL actuelle
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    // Si la langue est française
    if ( get_locale() == 'fr_FR' ) {
        echo '<link rel="alternate" hreflang="fr" href="' . esc_url( $current_url ) . '" />' . "\n";
    }

    // Pour la version x-default
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $default_url ) . '" />' . "\n";
}
add_action( 'wp_head', 'add_hreflang_tags' );


function add_self_canonical_tag() {
    // Obtenir l'URL de la page actuelle
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    // Ajouter la balise canonical
    echo '<link rel="canonical" href="' . esc_url( $current_url ) . '" />' . "\n";
}
add_action( 'wp_head', 'add_self_canonical_tag' );

add_action( 'wp_enqueue_scripts', 'wpm_enqueue_styles' );
function wpm_enqueue_styles(){
    //wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/styles/theme.css' );
    wp_enqueue_style('lightbox', get_stylesheet_directory_uri() . '/styles/lightbox.css', array(), filemtime(get_template_directory() . '/styles/theme.css'));
    wp_enqueue_style('theme', get_stylesheet_directory_uri() . '/styles/theme.css', array(), filemtime(get_template_directory() . '/styles/theme.css'));
    wp_enqueue_script(
        'beforeafter', // Identifiant unique du script
        get_stylesheet_directory_uri() . '/js/beforeafter.js', // URL du fichier JS
        array( 'jquery' ), // Dépendances (si besoin, ici 'jquery')
        null, // Version du script (null pour désactiver la gestion des versions)
        true // Charger dans le footer (true) ou dans le header (false)
    );

    wp_enqueue_script(
        'script', // Identifiant unique du script
        get_stylesheet_directory_uri() . '/js/script.js', // URL du fichier JS
        array( 'jquery' ), // Dépendances (si besoin, ici 'jquery')
        null, // Version du script (null pour désactiver la gestion des versions)
        true // Charger dans le footer (true) ou dans le header (false)
    );
}


function hide_post_type_from_frontend($args, $post_type) {
    if ($post_type === 'post') {  // Remplacez 'post' par le post type que vous voulez masquer
        $args['public'] = false;  // Rend le post type privé
        $args['publicly_queryable'] = false;  // Empêche les requêtes sur le front-end
        $args['show_ui'] = false;  // Masque du menu d'administration
        $args['exclude_from_search'] = true;  // Exclut des résultats de recherche
    }
    return $args;
}
add_filter('register_post_type_args', 'hide_post_type_from_frontend', 10, 2);


// Fil d'ariane

function custom_breadcrumb() {
    // Start the breadcrumb with a link to the home page
    if (!is_front_page()) {
        echo '<nav class="breadcrumb">';
        echo '<a href="' . home_url() . '">Accueil</a> ';

        // If we're on a single post, custom post type or page
        if (is_singular()) {
            global $post;
            $post_type = get_post_type_object(get_post_type());

            // If the post type is not 'post', show the post type archive link
            if ($post_type && $post_type->has_archive) {
                echo '<a href="' . get_post_type_archive_link($post_type->name) . '">' . $post_type->labels->name . '</a> ';
            }

            // Get ancestors of the current post to show hierarchy
            $ancestors = array_reverse(get_post_ancestors($post));

            foreach ($ancestors as $ancestor) {
                echo '<a href="' . get_permalink($ancestor) . '">' . get_the_title($ancestor) . '</a> ';
            }

            // Finally, the current post title
            echo '<span>' . get_the_title() . '</span>';
        }
        // If we're on a post type archive page
        elseif (is_post_type_archive()) {
            $post_type = get_post_type_object(get_post_type());
            if ($post_type) {
                echo '<span>' . $post_type->labels->name . '</span>';
            }
        }
        // If we're on a category or custom taxonomy archive page
        elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            echo '<span>' . $term->name . '</span>';
        }
        // If we're on an archive page like date, author, etc.
        elseif (is_archive()) {
            if (is_date()) {
                if (is_day()) {
                    echo '<span>' . get_the_date() . '</span>';
                } elseif (is_month()) {
                    echo '<span>' . get_the_date('F Y') . '</span>';
                } elseif (is_year()) {
                    echo '<span>' . get_the_date('Y') . '</span>';
                }
            } elseif (is_author()) {
                echo '<span>' . get_the_author() . '</span>';
            }
        }
        // For 404 pages
        elseif (is_404()) {
            echo '<span>Erreur 404</span>';
        }
    }

    // Close nav tag
    echo '</nav>';
}

add_image_size( 'relsize', 1920, 1080, true );
add_image_size( 'crosslink', 900, 900, true );

/**
 * Ajoute un selecteur de styles dans tous les TinyMCE/WYSIWYG.
 */
function impactexpo_add_tinymce_style_select( $buttons ) {
    if ( ! in_array( 'styleselect', $buttons, true ) ) {
        array_unshift( $buttons, 'styleselect' );
    }

    return $buttons;
}
add_filter( 'mce_buttons_2', 'impactexpo_add_tinymce_style_select' );

/**
 * Definit les styles editor qui inserent des div avec classes "style-H*".
 */
function impactexpo_register_tinymce_style_formats( $init_array ) {
    $style_formats = array(
        array(
            'title'   => 'Style H1',
            'block'   => 'div',
            'classes' => 'style-H1',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H2',
            'block'   => 'div',
            'classes' => 'style-H2',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H3',
            'block'   => 'div',
            'classes' => 'style-H3',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H4',
            'block'   => 'div',
            'classes' => 'style-H4',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H5',
            'block'   => 'div',
            'classes' => 'style-H5',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Style H6',
            'block'   => 'div',
            'classes' => 'style-H6',
            'wrapper' => true,
        ),
    );

    $init_array['style_formats'] = wp_json_encode( $style_formats );
    $init_array['style_formats_merge'] = true;

    return $init_array;
}
add_filter( 'tiny_mce_before_init', 'impactexpo_register_tinymce_style_formats' );

/**
 * Charge les styles front dans les editeurs du back-office.
 */
function impactexpo_setup_editor_styles() {
    add_theme_support( 'editor-styles' );
    add_editor_style( 'styles/theme.css' );
}
add_action( 'after_setup_theme', 'impactexpo_setup_editor_styles' );

/**
 * Force TinyMCE (notamment ACF WYSIWYG) a charger le CSS du theme.
 */
function impactexpo_add_mce_css( $mce_css ) {
    $theme_css_uri = get_stylesheet_directory_uri() . '/styles/theme.css';
    $theme_css_path = get_stylesheet_directory() . '/styles/theme.css';

    if ( file_exists( $theme_css_path ) ) {
        $theme_css_uri = add_query_arg( 'ver', filemtime( $theme_css_path ), $theme_css_uri );
    }

    if ( ! empty( $mce_css ) ) {
        $mce_css .= ',';
    }

    $mce_css .= $theme_css_uri;

    return $mce_css;
}
add_filter( 'mce_css', 'impactexpo_add_mce_css' );

/**
 * SMTP Brevo via PHPMailer (cles dans wp-config.php) :
 * IMPACTEXPO_BREVO_SMTP_USER, IMPACTEXPO_BREVO_SMTP_PASS,
 * IMPACTEXPO_MAIL_FROM, IMPACTEXPO_MAIL_FROM_NAME.
 */
function impactexpo_configure_brevo_smtp( $phpmailer ) {
    if ( ! defined( 'IMPACTEXPO_BREVO_SMTP_PASS' ) || '' === IMPACTEXPO_BREVO_SMTP_PASS ) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp-relay.brevo.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 587;
    $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $phpmailer->Username   = defined( 'IMPACTEXPO_BREVO_SMTP_USER' ) ? IMPACTEXPO_BREVO_SMTP_USER : '';
    $phpmailer->Password   = IMPACTEXPO_BREVO_SMTP_PASS;

    if ( defined( 'IMPACTEXPO_MAIL_FROM' ) && IMPACTEXPO_MAIL_FROM ) {
        $phpmailer->From = IMPACTEXPO_MAIL_FROM;
    }
    if ( defined( 'IMPACTEXPO_MAIL_FROM_NAME' ) && IMPACTEXPO_MAIL_FROM_NAME ) {
        $phpmailer->FromName = IMPACTEXPO_MAIL_FROM_NAME;
    }
}
add_action( 'phpmailer_init', 'impactexpo_configure_brevo_smtp' );