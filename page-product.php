<?php
/*
Template Name: Produit
*/

get_header();

if( have_rows('page') ):
    $i = 0;
    while ( have_rows('page') ) : the_row();

        if($i == 1){ ?>
            <div class="formulaire-product">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="container-img-devis">
                                <img src="https://images.pexels.com/photos/29422191/pexels-photo-29422191.jpeg" alt="">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="container-form">
                                <div class="container-text-form">
                                    <h3>
                                        Décrivez votre projet
                                    </h3>
                                    <p>
                                        Réponse sous 24h / Sans engagement
                                    </p>
                                </div>
                                <form action="">
                                    <input type="text" placeholder="Nom / Entreprise * ">
                                    <input type="email" placeholder="Email">
                                    <input type="text" placeholder="Nom / Entreprise * ">
                                    <input type="email" placeholder="Email">
                                    <textarea name="" id="" cols="30" rows="10"></textarea>
                                    <button type="submit" class="button primary">Envoyer ma demande</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        get_template_part('template-parts/strates/' . get_row_layout());
    $i++; endwhile;
endif; ?>

<?php get_footer();
