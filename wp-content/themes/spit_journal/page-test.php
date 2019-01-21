<?php
/**
 * Template Name: Test
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

			<?php $test_query = new WP_Query( array( 'post_type' => 'news','posts_per_page' => '10', 'order' => 'DSC' ) ); while($test_query->have_posts()) : $test_query->the_post(); ?>
			
			<?php if ( has_post_thumbnail() ): ?>
	<div class="my-posts">
					<div class="my-featured-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumnbnail'); ?></a></div>
					<div class="green-bar"><p>Posted on&nbsp;<?php echo get_the_date(); ?></p></div>
					<div class="my-post-content"><h2 class="my-post-title"><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></h2>
					 <p class="my-subheader"><a href="<?php bloginfo( 'url' ); ?>/<?php $post_type = get_post_type( $post->ID );
echo $post_type; ?>"><?php $post_type = get_post_type( $post->ID );
echo $post_type; ?></a>&nbsp;<span style="font-style:normal;">|</span>&nbsp;by&nbsp;<a href="<?php bloginfo( 'url' ); ?>/author/<?php the_author() ?>"><?php the_author() ?></a>
					</p>
					<p class="my-excerpt"><?php echo substr(strip_tags($post->post_content), 0, 340);?>...</p>
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a></p>
					</div><!-- post-content -->
					</div>

<?php else: ?>
	<div class="my-posts" style="width:100%;">
					
					<div class="my-post-content" style="width:100%;"><h2 class="my-post-title"><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></h2>
					 <p class="my-subheader"><a href="<?php bloginfo( 'url' ); ?>/<?php $post_type = get_post_type( $post->ID );
echo $post_type; ?>"><?php $post_type = get_post_type( $post->ID );
echo $post_type; ?></a>&nbsp;<span style="font-style:normal;">|</span>&nbsp;posted on&nbsp;<?php echo get_the_date(); ?>&nbsp;by&nbsp;<a href="<?php bloginfo( 'url' ); ?>/author/<?php the_author() ?>"><?php the_author() ?></a>
					</p>
					<p class="my-excerpt"><?php echo substr(strip_tags($post->post_content), 0, 640);?>...</p>
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a></p>
					</div><!-- post-content -->
					</div>

<?php endif; ?>

			
			
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>	
			
			
			
			
						
						
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
