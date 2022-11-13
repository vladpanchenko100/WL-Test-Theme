<?php
/*
Template Name: Car page
Template Post Type: Car
*/
?>

<?php get_header(); ?>

<main class="wrapper wl-main">
     <section class="wrapper wl-section">
          <div class="container">
               <div>
                    <p>
                         <?php the_title(); ?>
                    </p>
                    <p>
                         <?php the_content(); ?>
                    </p>
                    <div class="wl-main-thumbnail">
                         <?php the_post_thumbnail(); ?>
                    </div>
               </div>
               <div>
                    <p>
                         <?php $custom = get_post_custom();

                         if (isset($custom['car_options_price'])) {
                              echo 'Price: ' . $custom['car_options_price'][0] . "$";
                         }
                         ?>
                    </p>
                    <p>
                         <?php $custom = get_post_custom();
                         if (isset($custom['car_options_color'])) {
                              echo 'Color: ' . $custom['car_options_color'][0];
                         }
                         ?>
                    </p>
                    <p>
                         <?php $custom = get_post_custom();
                         if (isset($custom['car_options_kw-power'])) {
                              echo 'Kw Power: ' . $custom['car_options_kw-power'][0];
                         }
                         ?>
                    </p>
                    <p>
                         <?php $custom = get_post_custom();
                         if (isset($custom['car_options_fuel'])) {
                              echo 'Fuel: ' . $custom['car_options_fuel'][0];
                         }
                         ?>
                    </p>
                    <p>
                         <?php
                         $terms = wp_get_post_terms($post->ID, "brand");
                         if ($terms) {
                              $out = array();
                              foreach ($terms as $term) {
                                   $out[] = "Brand: " . $term->name;
                              }
                              echo join(', ', $out);
                         } ?>
                    </p>
                    <p>
                         <?php
                         $terms = wp_get_post_terms($post->ID, "producing country");
                         if ($terms) {
                              $out = array();
                              foreach ($terms as $term) {
                                   $out[] = "Producing country: " . $term->name;
                              }
                              echo join(', ', $out);
                         } ?>
                    </p>
               </div>
          </div>
     </section>
</main>

<?php get_footer(); ?>