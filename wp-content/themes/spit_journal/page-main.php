<?php
/**
 * Template Name: Main
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
	<?php if(get_field('slideshow')): ?>
	<div class="cycle-slideshow" data-cycle-fx="fade" data-cycle-pause-on-hover="true" data-cycle-speed="700" data-cycle-slides=".slide">
	<div class="cycle-pager"></div>
	<?php while(has_sub_field('slideshow')): ?>
		<?php if(get_sub_field('slide_link')) : ?>
		<div class="slide" style="background:<?php the_sub_field('slide_color'); ?>;">
				<a href="<?php the_sub_field('slide_link'); ?>"><img width="717" height="344" class="slide-pic" src="<?php the_sub_field('slide_image'); ?>"/></a>
				<!-- <img class="tri" src="<?php bloginfo( 'template_directory' ); ?>/images/tri.png"/> -->
		<div class="side-content">
			<h2><a href="<?php the_sub_field('slide_link'); ?>"><?php the_sub_field('slide_post_title'); ?></a></h2>
			<p><a href="<?php the_sub_field('slide_link'); ?>"><?php the_sub_field('slide_post_content'); ?>...</a></p>
				<p class="read-more"><a href="<?php the_sub_field('slide_link'); ?>">Read More</a></p>
			</div><!-- slide content -->
			</div><!-- slide -->
			<?php else : ?>
			<div class="slide">
				<img width="717" height="344" class="slide-pic" src="<?php the_sub_field('slide_image'); ?>"/>
				<img class="tri" src="<?php bloginfo( 'template_directory' ); ?>/images/tri.png"/>
		<div class="side-content">
			<h2 class="no-link-h2"><?php the_sub_field('slide_post_title'); ?></h2>
			<p class="no-link-p"><?php the_sub_field('slide_post_content'); ?>...</p>
				
			</div><!-- slide content -->
			</div><!-- slide -->

			
			<?php endif; ?>
		<?php endwhile; ?>
	</div><!-- cycle slideshow -->
	<?php endif; ?>

					
		<div id="container">
			<div id="content" role="main">
			<h1 class="mytitle">Recent</h1>
			<?php $main_query = new WP_Query( array( 'post_type' => array ( 'news', 'interviews', 'books','sociopoetix_blog' ),'posts_per_page' => '6', 'order' => 'DSC' ) ); while($main_query->have_posts()) : $main_query->the_post(); ?>
			
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
