<?php /* Template Name: Map Locations*/?>

<?php get_header(); ?>
	
    <div id="content" role="main" class="FullMapPage">

        <?php if (have_posts()) : while (have_posts()) : the_post();
                //Get the custom fields
                $custom_fields = get_post_custom($post->ID);
        ?>
        
        <div id="SingleMapLocation"></div>
        
        <h2 id="Maplocation-<?php the_ID(); ?>"><?php the_title();?></h2>
        
        <div id="MapDescription" class="cf">
            
            <?php 
            //Post featured image
            if(has_post_thumbnail($post->ID)){
               the_post_thumbnail(array(150,150), array('class' => 'float_left maplist_featuredimage'));
            }
            
            //Get the main content, format it, and display it
            $content = get_the_content();
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
            $content = do_shortcode(  $content );
            
            if($content != ""){
                echo $content;
            }
            else{
                //If it's empty use the description field
               echo $custom_fields['maplist_description'][0];
            }            
            ?>
        </div>

        <?php 
        //Show address if it is set
        if(isset($custom_fields['maplist_address'])){ ?>
        <div id="MapAddressContainer">
            <span id="MapAddressLabel">
                <?php _e('Address:','maplistpro') ?>
            </span>            
            
            <div id="MapAddress">
                <?php echo $custom_fields['maplist_address'][0];?>
            </div>            
        </div>            
        <?php } ?>
            
        <?php endwhile; endif;// end of the loop. ?>
        <?php
            if(isset($_SERVER['HTTP_REFERER'])){
                $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
                echo "<a href='$url' id='MaplistBack' class='corePrettyStyle btn'>&laquo;" . __('Back','maplistpro') . "</a>"; 
            }        
         dynamic_sidebar( 'sidebar-6' ); ?>
    </div><!-- #content -->

<?php get_footer(); ?>