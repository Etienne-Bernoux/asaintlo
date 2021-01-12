<?php
/**
 * The template for displaying all single playlist
 *
 *
 */
get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
?>

		<?php
		// Start the loop.
		while ( have_posts() ) :
			the_post();?>
			<div class="sr-container">
				<div class="sr-boxed">
					<?php $iron_sonaar_atts = array(
						'albums' => array($post->ID),
						'show_playlist' => true,
						'show_album_market' =>true,
						'show_track_market' =>true,
						'sticky_player'=>false,
					);

					the_widget('Sonaar_Music_Widget', $iron_sonaar_atts, array( 'before_widget'=>'<article class="widget iron_widget_radio">', 'after_widget'=>'</article>', 'widget_id'=>'single_album_thumb'));
					the_content();?>
				</div>
			</div>

			<?php
			/*
			 * Include the post format-specific template for the content. If you want to
			 * use this in a child theme, then include a file called content-___.php
			 * (where ___ is the post format) and that will be used instead.
			 */
			//get_template_part( 'content', get_post_format() );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

			// End the loop.
		endwhile;
		?>
<?php } ?>
<?php get_footer(); ?>