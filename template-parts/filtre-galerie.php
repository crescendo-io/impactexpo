<div class="visible-xs">
    <button class="button filter-buttons-toggle">Filtres</button>
</div>
<form method="GET" action="<?= get_site_url(); ?>/galerie/" class="filters-form">
    <!-- Checkbox pour typo_client -->
    <div class="filter-group">
        <h4><?php _e('Choix par marques', 'textdomain'); ?></h4>
        <div class="list-group">
        <?php
        // Fonction récursive pour afficher les termes et leurs enfants
        function display_terms_recursive($terms, $selected, $taxonomy, $level = 0) {
            foreach ($terms as $term) {
                $checked = in_array($term->slug, $selected) ? 'checked' : '';
                echo '<label style="margin-left: ' . ($level * 20) . 'px;">';
                echo '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" ' . $checked . '>';
                echo esc_html($term->name);
                echo '</label>';

                // Rechercher les termes enfants
                $child_terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'parent' => $term->term_id,
                    'hide_empty' => false,
                ));

                if (!empty($child_terms)) {
                    display_terms_recursive($child_terms, $selected, $taxonomy, $level + 1);
                }
            }
        }

        // Obtenir tous les termes parents de la taxonomy 'typo_client'
        $typo_clients = get_terms(array(
            'taxonomy' => 'typo_client',
            'parent' => 0,
            'hide_empty' => false,
        ));

        // Sélectionner les termes cochés
        $selected_clients = isset($_GET['typo_client']) ? (array) $_GET['typo_client'] : array();

        // Afficher les termes et leurs enfants récursivement
        if (!empty($typo_clients)) {
            display_terms_recursive($typo_clients, $selected_clients, 'typo_client');
        }
        ?>
        </div>
    </div>

    <!-- Checkbox pour typo_product -->
    <div class="filter-group">
        <h4><?php _e('Choix par prestation', 'textdomain'); ?></h4>
        <div class="list-group">
        <?php
        // Obtenir tous les termes parents de la taxonomy 'typo_product'
        $typo_products = get_terms(array(
            'taxonomy' => 'typo_product',
            'parent' => 0,
            'hide_empty' => false,
        ));

        // Sélectionner les termes cochés
        $selected_products = isset($_GET['typo_product']) ? (array) $_GET['typo_product'] : array();

        // Afficher les termes et leurs enfants récursivement
        if (!empty($typo_products)) {
            display_terms_recursive($typo_products, $selected_products, 'typo_product');
        }
        ?>
        </div>
    </div>



</form>