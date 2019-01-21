<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php if(get_field('book_review_title')) : ?>
						<h1 class="entry-title"><?php the_field('book_review_title'); ?></h1>
						<?php else : ?>
						<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php endif; ?>
					<div class="entry-meta">
						<?php twentyten_posted_on(); ?>
					</div><!-- .entry-meta -->

					<div class="entry-content">
						<?php the_content(); ?>
						
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
						Share:<br/>
						<?php echo synved_social_share_markup(); ?>
					</div><!-- .entry-content -->

<?php if ( get_the_author_meta( 'description' ) ) : // If a user has filled out their description, show a bio on their entries  ?>
					<div id="entry-author-info">
						<div id="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
						</div><!-- #author-avatar -->
						<div id="author-description">
							<h2><?php printf( esc_attr__( 'About %s', 'twentyten' ), get_the_author() ); ?></h2>
							<?php the_author_meta( 'description' ); ?>
							<div id="author-link">
								<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" rel="author">
									<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentyten' ), get_the_author() ); ?>
								</a>
							</div><!-- #author-link	-->
						</div><!-- #author-description -->
					</div><!-- #entry-author-info -->
<?php endif; ?>

					<div class="entry-utility">
						<?php twentyten_posted_in(); ?>
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->
				<div id="related_articles">
					
				</div>
				
				<?php // comments_template( '', true ); ?>

<?php endwhile; // end of the loop. ?>
<h2 style="margin-bottom:5px;font-weight:bold;">Related Articles</h2>
<?php $relatednews = new WP_Query( array( 'post_type' => 'interviews','posts_per_page' => '10', 'order' => 'DSC' ) ); while($relatednews->have_posts()) : $relatednews->the_post(); ?>
			
			<?php if ( has_post_thumbnail() ): ?>
			<div class="related_articles" style="width:181px;float:left;margin-right:6px;margin-bottom:7px;height:215px;">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('related', '181', '151', true); ?></a>
				<a style="color:#000;font-weight:bold;" href="<?php the_permalink(); ?>"><?php echo substr(strip_tags($post->post_title), 0, 40);?>...</a>
			</div>
					
<?php endif; ?>

			
			
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>	
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>





