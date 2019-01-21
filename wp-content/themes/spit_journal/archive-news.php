<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">
<h1 class="mytitle"><?php $post_type = get_post_type( $post->ID );
echo $post_type; ?></h1>


			
<?php $news_query = new WP_Query( array( 'post_type' => 'news','posts_per_page' => '24', 'order' => 'DSC' ) ); while($news_query->have_posts()) : $news_query->the_post(); ?>
			
			<?php if ( has_post_thumbnail() ): ?>
	<div class="my-posts">
					<div class="my-featured-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('my-thumbnail', '242', '210', true); ?></a></div>
					<div class="green-bar"><p>Posted on&nbsp;<?php echo get_the_date(); ?></p></div>
					<div class="my-post-content"><h2 class="my-post-title"><a href="<?php the_permalink(); ?>"><?php echo substr(strip_tags($post->post_title), 0, 75);?>...</a></h2>
					 <p class="my-subheader"><a href="<?php bloginfo( 'url' ); ?>/<?php $post_type = get_post_type( $post->ID );
echo $post_type; ?>"><?php $post_type = get_post_type( $post->ID );
echo $post_type; ?></a>&nbsp;<span style="font-style:normal;">|</span>&nbsp;by&nbsp;<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author() ?></a>
					</p>
					<p class="my-excerpt"><?php echo substr(strip_tags($post->post_content), 0, 280);?>...</p>
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a><!-- <span style="padding:0;position:relative;top:10px; left:10px;"><?php echo synved_social_share_markup(); ?></span> --></p>
					</div><!-- post-content -->
					</div>

<?php else: ?>
	<div class="my-posts" style="width:100%;">
					
					<div class="my-post-content" style="width:100%;"><h2 class="my-post-title"><a href="<?php the_permalink(); ?>"><?php echo substr(strip_tags($post->post_title), 0, 120);?>...</a></h2>
					 <p class="my-subheader"><a href="<?php bloginfo( 'url' ); ?>/<?php $post_type = get_post_type( $post->ID );
echo $post_type; ?>"><?php $post_type = get_post_type( $post->ID );
echo $post_type; ?></a>&nbsp;<span style="font-style:normal;">|</span>&nbsp;posted on&nbsp;<?php echo get_the_date(); ?>&nbsp;by&nbsp;<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author() ?></a>
					</p>
					<p class="my-excerpt"><?php echo substr(strip_tags($post->post_content), 0, 640);?>...</p>
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a><!-- <span style="padding:0;position:relative;top:10px; left:10px;"><?php echo synved_social_share_markup(); ?></span> --></p>
					</div><!-- post-content -->
					</div>

<?php endif; ?>

			
			
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>	

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
