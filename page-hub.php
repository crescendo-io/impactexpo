<?php
/*
Template Name: Hub
*/

get_header();

if( have_rows('page') ):
    while ( have_rows('page') ) : the_row();
        get_template_part('template-parts/strates/' . get_row_layout());
    endwhile;
endif; ?>

<div class="formulaire-footer white">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <div class="container-text-devis">
                    <h4>
                        Contactez-nous
                    </h4>
                    <h3>
                        Demandez votre devis de signalétique personnalisée
                    </h3>
                    <p>
                        Chez Impact Expo, chaque projet est unique – et ça commence dès le premier contact. Que vous ayez une idée précise ou besoin de conseils pour la faire émerger, notre équipe vous accompagne de la conception à l'installation.
                    </p>

                    <ul>
                        <li><div class="picto">📍</div>Villeneuve-la-Garenne (92) – Zone d'intervention Île-de-France</li>
                        <li><div class="picto">🚚</div>Livraison France entière</li>
                        <li><div class="picto">✓</div>Devis gratuit sous 24h</li>
                        <li><div class="picto">🎯</div>Étude personnalisée de votre projet</li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="container-form">
                    <h3>
                        Décrivez votre projet
                    </h3>
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
<?php get_footer();
