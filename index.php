<?php get_header(); ?>
<main class="wrapper wl-main">
     <section class="wrapper wl-section">
          <div class="container">
          <ul>
	<?php
	$args = array('post_type' => 'Car', 'posts_per_page' => 10 );
	$myposts = get_posts( $args );
	foreach( $myposts as $post ){ setup_postdata($post);
		?>
        <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
		<?php
	}
	wp_reset_postdata();

	?>

</ul>
          </div>
     </section>
</main>
<?php get_footer(); ?>