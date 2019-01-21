<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop.php or
 * loop-template.php, where 'template' is the loop context
 * requested by a template. For example, loop-index.php would
 * be used if it exists and we ask for the loop with:
 * <code>get_template_part( 'loop', 'index' );</code>
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<div id="nav-above" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
	</div><!-- #nav-above -->
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Found', 'twentyten' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyten' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php
	/* Start the Loop.
	 *
	 * In Twenty Ten we use the same loop in multiple contexts.
	 * It is broken into three main parts: when we're displaying
	 * posts that are in the gallery category, when we're displaying
	 * posts in the asides category, and finally all other posts.
	 *
	 * Additionally, we sometimes check for whether we are on an
	 * archive page, a search page, etc., allowing for small differences
	 * in the loop on each template without actually duplicating
	 * the rest of the loop that is shared.
	 *
	 * Without further ado, the loop:
	 */ ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php /* How to display posts of the Gallery format. The gallery category is the old way. */ ?>

	
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
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a><span style="padding:0;position:relative;top:10px; left:10px;"><?php echo synved_social_share_markup(); ?></span></p>
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
					<p class="post-read-more"><a href="<?php the_permalink(); ?>">Read More</a><span style="padding:0;position:relative;top:10px; left:10px;"><?php echo synved_social_share_markup(); ?></span></p>
					</div><!-- post-content -->
					</div>

<?php endif; ?>


		<?php comments_template( '', true ); ?>

	

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; ?>
