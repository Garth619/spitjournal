<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />

<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link href='http://fonts.googleapis.com/css?family=Roboto:400,700,500' rel='stylesheet' type='text/css'>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link href='http://fonts.googleapis.com/css?family=Lato:400,100,300,700,900,100italic,300italic,400italic,700italic,900italic|Josefin+Sans:400,100,300,600,700,300italic,400italic,600italic,700italic|Inconsolata|Patua+One' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>?v=13" />
<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'template_url' ); ?>/ie.css" />
<![endif]-->
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
<script src="<?php bloginfo( 'template_directory' ); ?>/cycle2.js"></script>

</head>

<body <?php body_class(); ?>>
<div id="wrapper" class="hfeed">
	<div id="header">
		
			<a href="<?php bloginfo( 'url' ); ?>"><img class="logo" src="<?php bloginfo( 'template_directory' ); ?>/images/logo3.png"/></a>
			<div class="social-media">
				<a href="https://www.facebook.com/spitjournal" target="_blank"><img src="<?php bloginfo( 'template_directory' ); ?>/images/facebook.png"/></a>
				<a href="https://twitter.com/spitjournal" target="_blank"><img src="<?php bloginfo( 'template_directory' ); ?>/images/twitter.png"/></a>
				<!--
<a href=""><img src="<?php bloginfo( 'template_directory' ); ?>/images/youtube.png"/></a>
				<a href=""><img src="<?php bloginfo( 'template_directory' ); ?>/images/google-plus.png"/></a>
				<a href=""><img src="<?php bloginfo( 'template_directory' ); ?>/images/rss.png"/></a>
-->
			</div>
		
						<?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
							</div><!-- #header -->

	<div id="main">
