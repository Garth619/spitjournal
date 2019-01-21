<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
	
		<div id="primary">
		<div class="side-content">
				<h2><a href="<?php the_field('forum_link', 'option'); ?>"><?php the_field('forum_title', 'option'); ?></a></h2>
				<p><a href="<?php the_field('forum_link', 'option'); ?>"><?php the_field('forum_description', 'option'); ?></a></p>
				<p class="read-more"><a href="<?php the_field('forum_link', 'option'); ?>"><?php the_field('forum_button', 'option'); ?></a></p>
				
				</div><!-- side content -->
			<div class="side-content">
				<h2><a href="<?php the_field('about_link', 'option'); ?>"><?php the_field('about_title', 'option'); ?></a></h2>
				<p><a href="<?php the_field('about_link', 'option'); ?>"><?php the_field('about_description', 'option'); ?></a></p>
				<p class="read-more"><a href="<?php the_field('about_link', 'option'); ?>"><?php the_field('about_button', 'option'); ?></a></p>
				<?php if(get_field('ads', 'option')): ?>
				<?php while(has_sub_field('ads', 'option')): ?>
				<a href="<?php the_sub_field('ad_link'); ?>" target="_blank"><img class="my-ads" src="<?php the_sub_field('ad_image'); ?>"/></a>
				<?php endwhile; ?>
 				<?php endif; ?>
				</div><!-- side content -->

		</div><!-- #primary -->


 
	
 

 

 
	