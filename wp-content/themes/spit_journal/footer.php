<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
<?php
if ( is_user_logged_in() ) {
    
} else {
    echo 'Welcome, visitor! To participate in <a href="http://www.spitjournal.com/forums/">forum topics</a>, please register <a href="http://www.spitjournal.com/wp-login.php?action=register">here</a>, and we will update your status.';
}
?> 
	</div><!-- #main -->
 
	<div id="footer" role="contentinfo">
		<?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>

	</div><!-- #footer -->
	<div id="yourz">
		<a href="mailto:garrettcullen@yahoo.com" target="_blank">website by Garrett Cullen</a>
	</div>

</div><!-- #wrapper -->

<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>

</body>
</html>
