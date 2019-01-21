<?php
/*
Plugin Name: Map List Pro
Version: 3.3.5
Description: Create interactive maps and lists of locations quickly and easily.
Author: SmartRedFox & EireTek Ltd.
Author URI: http://www.smartredfox.com
ChangeLog:
-3.0.6
-- Sort error for distances less than 1
-- Distance display not clearing on all items on clear search click
-- Removed un-needed option on settings page. 
-3.0.7
-- Selected item zoom added back in
-- Initial map type shortcode option added in
-- Slide Up/Down moved to custom binding to make animations smooth
-- OSM map type added back in
-- Missing TimThumb file added back in.
-3.0.8
-- Added print directions option in.
-3.0.9
-- Additional styling added to detail page.
-3.0.10
-- Multi maps added back in
-3.0.11
-- All strings localised except shortcode wizard
-- Updated styles for featured images
-3.0.12
-- stayzoomedin option added
-- Fixed bug that was stopping selected zoom from working
-3.0.13
-- Added option to use default infowindows instead of infoboxes
-- Changed enqueues to hopefully stop some conflicts with other maps
-3.0.14
-- Started to abstract complex knockoutjs inline functions that cause wpauto and wptexturize filters to choke when double applied.
-3.0.15
- Added view detail button back in to infowindow.
- Fixed detail page incorrect enqueueing of scripts.
-3.0.16
- Fixed initial sort directions and type ignored.
- Fixed Print directions button keeps getting added when go is clicked.
- Fixed directions routes ignoring measurement units.
-3.1.0
- Fixed placeholder text stopping search in <IE10 with themes that run a shim
- Fixed categories on items blank
- Fixed trailing comma on categories list on item
- Fixed Template file ignoring child themes.
- Fixed View location link in infowindow not opening in new window if selected
- Fixed missing localization for "Categories:" on list items.
- Added custom map markers with custom shadows, and optional custom positioning.
-3.1.1
- Fixed / used instead of \ in icon gets
-3.1.2
- Fixed issue where pins are found in root of pin folder.
-3.2.0
- New filter added to allow extra fields to be added to the location editor.
- New filter added to allow editing of the description, with access to all available custom fields.
-3.2.1
- Misplaced div closure for map only view moved inside correct if statement.
-3.2.2
- Fixed bug that was stopping direction sort firing on geocode
- Added initial sort by direction for parameter, and location search.
-3.2.3
- Added home location option
-3.2.4
- Simplified shortcode output so it doesn't output defaults
-3.2.5
- Added css class to location list items for advanced styling.
- Made Map KO objects available in javascript via an array so that they can be refreshed when used in accordions etc.
- Selecting a location in the list hides all others.
- Categories start off unselected - but all locations show.
-3.2.6
-- Fixed custom css style save not working
-- Fixed insert button not showing properly in IE9
-- Missing localisation for View Location button in infowindow added.
-- Missing localisation for Print Directions button added.
-3.2.7
-- Fixed view location detail button still showing in info window when hidden.
-3.2.8
-- Fixed Google Chrome crash on directions print cancel.
-3.2.9
-- Fixed no search results on passed parameter issue with simple search
-- Added Google Map Language option to settings page
-- Fixed issue with map array causing maps to be generated twice
-- Fixed sort drop down hiding itself straight away
-3.3.0
-- Added day category mode - Add a category for each day to show only categories from that day
-3.3.1
-- Expand if only a single item returned
-- Fixed empty address causing errors
-- Fixed incomplete category save causing errors
-- Fixed category ordering getting ignored
-3.3.2
-- Added category search to text and combo search types
-- Removed some console.log calls from maplistfront.js
-- Fixed paging count not correct after search
-- Abstracted current page count from html
-- Added focus style to search boxes to make text clearer
-- Fixed missing "of" localisation in paging.
-3.3.3
-- Fixed send media to editor conflict - cmb issue.
-3.3.4
-- Stopped no location found message showing ahead of load.
-- Added post object to description filter so users can get custom fields etc.
-3.3.5
-- Added check in editor for matching place name and first address line to stop duplicates.
-- Made category label on filter button configurable.

 */

$lastMapId = 0;

class MapListProKO {
    /**
     * Class Constructor
     */
    public static $counter = 0;
    public static $maps = Array();
    public static $isOnPage = false;
    public $mlpoutputhtml;
    public $numberOfLocations;
    
    function __construct() {
        $this->plugin_defines();
        $this->setup_actions();
    }

    /**
     * Defines To Be Used Anywhere In Wordpress After The Plugin Has Been Initiated.
     */
    function plugin_defines(){
        define( 'MAP_LIST_KO_PLUGIN_PATH', trailingslashit( WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) ) ) );
        define( 'MAP_LIST_KO_PLUGIN_URL' , trailingslashit( WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) ) ) );
    }

    //Map id's
    public $MapID = 0;

    /*Activation stuff - is run once on plugin activation*/
    function map_list_pro_activate(){
        //Set the default to be full view
        update_option('maplist_fullpageviewenabled','true');
    }

    /**
     * Setup actions
     */
    function setup_actions(){
        //New check to make sure new category pin system is in use - if not run translate
        $newPinStyle = get_option('mlp_pins_new_style');
        
        if($newPinStyle != 'true'){          
            $oldCategoryIcons = get_option('mlp_custom_category_icons_options');
            if($oldCategoryIcons != ''){
                $newCategoryIcons = array();
                
                foreach($oldCategoryIcons as $category => $oldIcon){                    
                    $position_iconid = explode (',',$oldIcon);
                    $position_iconid[1] = 'default/mapmarker' .  $position_iconid[1] . '.png';
                    $position_iconid[2] = 'default/shadow.png';
                    $newCategoryIcons[$category] = $position_iconid;
                }
                
                //Update the options
                update_option('mlp_custom_category_icons_options',$newCategoryIcons);
                //Don't check again
                update_option('mlp_pins_new_style','true');
            }
        }
        
        
        //Register the maplist_location_category taxonomy and maplist post type
        add_action('init', array($this,'maplist_cat_posttype_register'));

        //Add meta boxes to locations page
        add_action( 'init', array($this,'maplist_metaboxes'), 9999);

        //Location page metaboxes
        add_filter( 'cmb_meta_boxes', array($this,'location_metaboxes'));

        //Add map option to metaboxes
        add_action( 'cmb_render_map', array($this,'cmb_render_map'), 10, 2 );  

        //Set domain for translations
        load_plugin_textdomain( 'maplistpro', false, dirname( plugin_basename( __FILE__ ) ) );

        //Template fallback
        add_action("template_redirect", array($this,'maplist_theme_redirect'));

        if(is_admin()){
            //Add shortcode button to editor
            add_action('init', array($this,'add_maplist_shortcode_button'));
            //Attach the additonal menu items
            add_action('admin_menu', array($this,'create_admin_menus'));
            //Ajax calls from shortcode wizard
            add_action('wp_ajax_get_all_maplocations', array($this,'get_all_maplocations_ajax'));
            add_action('wp_ajax_get_all_mapcategories', array($this,'get_all_mapcategories_ajax'));
            //setup column headings on map locations list page
            add_filter('manage_edit-maplist_columns', array($this,'add_new_maplist_columns'));
            //setup column data for the map locations list page
            add_action('manage_maplist_posts_custom_column',  array($this,'manage_maplist_columns'), 10, 2);
            //Hook up admin init - use if later binding needed
            add_action( 'admin_enqueue_scripts', array( &$this, 'admin_init' ) );  
        }
        else{
            add_action( 'template_redirect' , array( $this , 'frontend_scripts_styles' ) );
            add_shortcode('maplist', array($this, 'register_maplist_shortcode'));

            $this->searchTextDefault =  __('Search...','maplistpro');
            $this->searchLocationTextDefault =  __('Location...','maplistpro');
        }
    }

    function maplist_cat_posttype_register(){

        //See if full page view is enable
        $fullPageViewEnabled = get_option('maplist_fullpageviewenabled',false);

        if($fullPageViewEnabled == 'true'){
            $boxesToShow = array('title','editor','thumbnail');//Enable full editor
        }
        else{
            $boxesToShow = array('title','thumbnail');//Disable full editor
        }

        //register custom post type
        $args = array(
            'labels' => array(
                'name' => _x('Map locations', 'Map locations general name','maplistpro'),
                'singular_name' => _x('Map location', 'Map locations singular name','maplistpro'),
                'add_new' => _x('Add new', 'maplist item','maplistpro'),
                'add_new_item' => __('Add new map location','maplistpro'),
                'edit_item' => __('Edit map location','maplistpro'),
                'new_item' => __('New map location','maplistpro'),
                'view_item' => __('View map location','maplistpro'),
                'search_items' => __('Search maps','maplistpro')
            ),
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => array('slug' => 'location'),
            'menu_icon' => MAP_LIST_KO_PLUGIN_URL . 'images/mappin.png',
            'supports' => $boxesToShow //Boxes to show in the panel
        );

        register_post_type('maplist' , $args );

        //register custom taxonomy
        $categorylabels = array(
            'name' => _x( 'Location Categories', 'map_location categories','maplistpro' ),
            'singular_name' => _x( 'Location Category', 'map_location categories','maplistpro' ),
            'search_items' => _x( 'Search Location Categories', 'map_location categories','maplistpro' ),
            'popular_items' => _x( 'Popular Location Categories', 'map_location categories','maplistpro' ),
            'all_items' => _x( 'All Location Categories', 'map_location categories','maplistpro' ),
            'parent_item' => _x( 'Parent Map Location Category', 'map_location categories','maplistpro' ),
            'parent_item_colon' => _x( 'Parent Map Location Category:', 'map_location categories','maplistpro' ),
            'edit_item' => _x( 'Edit Map Location Category', 'map_location categories','maplistpro' ),
            'update_item' => _x( 'Update Map Location Category', 'map_location categories','maplistpro' ),
            'add_new_item' => _x( 'Add New Map Location Category', 'map_location categories','maplistpro' ),
            'new_item_name' => _x( 'New Map Location Category', 'map_location categories','maplistpro' ),
            'separate_items_with_commas' => _x( 'Separate map location categories with commas', 'map_location categories','maplistpro' ),
            'add_or_remove_items' => _x( 'Add or remove map location categories', 'map_location categories','maplistpro' ),
            'choose_from_most_used' => _x( 'Choose from the most used map location categories', 'map_location categories','maplistpro' ),
            'menu_name' => _x( 'Location Categories', 'map_location categories','maplistpro' ),
        );

        $categoryargs = array(
            'labels' => $categorylabels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'map_location_categories', array('maplist'), $categoryargs );

        //See if we've flushed permalinks, if not flush them
        if(!get_option('maplist_permalinksflushed')){
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
            update_option('maplist_permalinksflushed',"true");
        }
    }

    function maplist_theme_redirect() {
        global $wp;
        $plugindir = dirname( __FILE__ );

        //A Specific Custom Post Type
        if (isset($wp->query_vars["post_type"]) && $wp->query_vars["post_type"] == 'maplist') {

            $templatefilename = 'single-maplist.php';
            
            if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
                $return_template = TEMPLATEPATH . '/' . $templatefilename;
            } else {
                $return_template = $plugindir . '/themefiles/' . $templatefilename;
            }

            $this->frontend_scripts_styles();

            //Create and pass the location
            $kolocation = new location();

            global $post;

            //Get all terms used by this location
            $lat = get_post_meta($post->ID, 'maplist_latitude', true);
            $lng = get_post_meta($post->ID, 'maplist_longitude', true);
            $temp = get_post_meta($post->ID, 'maplist_alternateurl', true);
            $alternateUrl = $temp == '' ? get_permalink($post->ID) : get_post_meta($post->ID, 'maplist_alternateurl', true);
            $imageUrlTemp = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), array(50,50));

            $imageUrl = $imageUrlTemp[0];
            $address = get_post_meta($post->ID, 'maplist_address', true);

            //Create object
            $kolocation->title = $post->post_title;
            $kolocation->cssClass = 'loc-' . $post->ID;  //strtolower(preg_replace("/[^A-Za-z0-9]/", "", $post->post_title));
            $kolocation->address = $address;
            $kolocation->latitude = $lat;
            $kolocation->longitude = $lng;
            $kolocation->pinColor = '';
            //$kolocation->pinImageUrl = $this->get_marker_image('');
            $kolocation->imageUrl = $imageUrl;           
            $kolocation->address = $address;
            $kolocation->_mapMarker = '';
            $kolocation->locationUrl = $alternateUrl;
            
            //Get description, fire it through wpautop to convert crs to p tags, then do shortcode stuff         
            $tempDesc = do_shortcode(wpautop(get_post_meta($post->ID, 'maplist_description', true),false));
            
            $kolocation->description = $tempDesc;

            //Encode all data to json
            $kojsobject = json_encode($kolocation);            

            $disableInfoBoxes = get_option('maplist_disableinfoboxes');
            
            $params = array("location" => $kojsobject,"pluginurl" => MAP_LIST_KO_PLUGIN_URL,'disableInfoBoxes' => $disableInfoBoxes,);
            
            $deps = array('knockout','jquery','map_list-google-places');       
            
            if($disableInfoBoxes != 'true'){
                $deps[] = 'infowindow_custom';
            }           
            
            //Script to load and show
            wp_register_script( 'FullPageMapScript', MAP_LIST_KO_PLUGIN_URL . 'js/fullPageMap.js', $deps,false,true);
            wp_localize_script('FullPageMapScript', 'maplistFrontScriptParams', $params );
            wp_enqueue_script( 'FullPageMapScript' );

            $this->do_theme_redirect($return_template);
        }
    }

    function do_theme_redirect($url) {
        global $post, $wp_query;
        if (have_posts()) {
            include($url);
            die();
        } else {
            $wp_query->is_404 = true;
        }
    }

    function frontend_scripts_styles()
    {

        //Get user selected stylesheet if any
        $options['maplist_stylesheet_to_use'] = get_option('maplist_stylesheet_to_use');

        $stylesheet_url = MAP_LIST_KO_PLUGIN_URL . 'styles/Grey_light_default.css';

        if($options['maplist_stylesheet_to_use'] != ""){
            //Add our prettylist stylesheet
            $stylesheet_url = MAP_LIST_KO_PLUGIN_URL . 'styles/' . $options['maplist_stylesheet_to_use'];
        }

        //Core styles
        wp_register_style('maplistCoreStyleSheets', MAP_LIST_KO_PLUGIN_URL . 'css/MapListProCore.css');
        wp_enqueue_style( 'maplistCoreStyleSheets');

        //Colour Styles
        wp_register_style('maplistStyleSheets', $stylesheet_url);
        wp_enqueue_style( 'maplistStyleSheets');

        //Add jQuery
        wp_enqueue_script( 'jquery' );

        //Add Google maps
        //Get language needed
        $google_map_language = get_option('maplist_google_map_language');
        $languageString = '';
        if($google_map_language != '' && $google_map_language != 'en'){
            $languageString = '&language=' . $google_map_language;
        }
        wp_register_script( 'map_list-google-places', 'http://maps.googleapis.com/maps/api/js?sensor=true&libraries=places' . $languageString);

        //Google map clusterer
        wp_register_script( 'map_list-google-marker-clusterer', MAP_LIST_KO_PLUGIN_URL . 'js/markerclusterer_compiled.js', array('map_list-google-places'));

        
        $disableInfoBoxes = get_option('maplist_disableinfoboxes');
        
        //Google map infoboxes
        if($disableInfoBoxes != 'true'){
            wp_register_script( 'infowindow_custom', MAP_LIST_KO_PLUGIN_URL . 'js/infobox_packed.js', array('map_list-google-places'));
        }

        //Knockout
        wp_register_script( 'knockout', MAP_LIST_KO_PLUGIN_URL . 'js/knockout-2.2.1.js');
    }

    /***********************************
     * ADMIN ONLY STUFF
     *
     */

    //Initialize the metabox class
    function maplist_metaboxes(){
        if ( ! class_exists( 'cmb_Meta_Box' ) ){
            require_once(MAP_LIST_KO_PLUGIN_PATH . 'metaboxes/init.php');
        }
    }

    function location_metaboxes( $meta_boxes ) {
        $prefix = 'maplist_'; // Prefix for all fields

        //FIELDS TO SHOW IN EDITOR
        //================================

        $meta_boxes[] = array(
                'id' => 'location_metabox',
                'title' => 'Location Details',
                'pages' => array('maplist'), // post type
                'context' => 'normal',
                'priority' => 'high',
                'show_names' => true, // Show field names on the left
                'fields' => array(
                        array(
                        'name' => __('Find location','maplistpro'),
                        'desc' => __('Location picker','maplistpro'),
                        'id' => $prefix . 'map',
                        'type' => 'map'),
                        array(
                        'name' => __('Latitude','maplistpro'),
                        'id' => $prefix . 'latitude',
                        'type' => 'text'),
                        array(
                        'name' => __('Longitude','maplistpro'),
                        'id' => $prefix . 'longitude',
                        'type' => 'text'),
                        array(
                        'name' => __('Short description','maplistpro'),
                        'desc' => __('This appears on the expanded items in the list','maplistpro'),
                        'id' => $prefix . 'description',
                        'type' => 'wysiwyg'),
                        array(
                        'name' => __('Address','maplistpro'),
                        'desc' => __('Enter address','maplistpro'),
                        'id' => $prefix . 'address',
                        'type' => 'textarea_small'),
                        array(
                        'name' => __('Alternate web address','maplistpro'),
                        'desc' => __('(optional) A full website url (including http://).','maplistpro'),
                        'id' => $prefix . 'alternateurl',
                        'type' => 'text')
                )
        );

        //Filter here is to allow extra fields to be added
        $meta_boxes[0]['fields'] = apply_filters( 'mlp_location_metaboxes', $meta_boxes[0]['fields']);

        return $meta_boxes;
    }

    //Render map field in admin
    function cmb_render_map( $field, $meta ) {
        
        //Load google maps
        wp_enqueue_script( 'map_list-google-places', 'http://maps.googleapis.com/maps/api/js?sensor=true&libraries=places');        

        //Get start position
        $defaultEditMapLocationLat = get_option('maplist_default_edit_map_location_lat');
        $defaultEditMapLocationLong = get_option('maplist_default_edit_map_location_long');
        $defaultEditMapZoom = get_option('maplist_default_edit_map_zoom');        

        if($defaultEditMapLocationLat == '' || $defaultEditMapLocationLong == ''){
            $defaultEditMapLocationLat = '40.3';
            $defaultEditMapLocationLong ='-98.2' ;
        }
        
        if($defaultEditMapZoom == '' || $defaultEditMapZoom == 'None' ){
            $defaultEditMapZoom = 4;
        }
        
        wp_localize_script('map_list-google-places','maplocationdata',array('defaultEditMapLocationLat' => $defaultEditMapLocationLat,'defaultEditMapLocationLong' => $defaultEditMapLocationLong,'defaultEditMapZoom'=>$defaultEditMapZoom));
        
        //Display the map        
        echo '<input type="text" value="" aria-required="true" id="MapSearchInput" placeholder="' . __('Enter a location','maplistpro') . '" autocomplete="off">';
        echo '<div id="GoogleMap"></div>';
        echo '<a style="margin-right: 17px;float: right;margin-top: 10px;" class="button" id="UpdateMap" href="#">Update</a>';
                
    }   

    function admin_init(){
        //Only load if needed
        global $post_type;
        if( 'maplist' == $post_type ){
            //Set up styles
            wp_enqueue_style( 'plugin_style', MAP_LIST_KO_PLUGIN_URL . 'css/admin/Metaboxes.css' );
            
            //Set up map scripts for editor
            wp_register_script('cmb_metabox_map', MAP_LIST_KO_PLUGIN_URL . 'js/admin/Metaboxes.js',array('jquery'));
            wp_enqueue_script( 'cmb_metabox_map');
        }
    }

    /**********************
    ADMIN MENUS
     **********************/
    public function create_admin_menus()
    {
        // this is where we add our plugin to the admin menu
        $page = add_options_page('Map Location Settings', 'Map List Pro', 'manage_options', dirname(__FILE__), array($this,'maplistpro_admin_options'));
        //Category icons page
        $iconpage = add_submenu_page( '/edit.php?post_type=maplist', __('Category icons','maplistpro'), __('Category icons','maplistpro'), 'manage_options', 'maplistproicons', array($this,'maplistpro_admin_icons') );
        add_action('admin_init', array($this,'map_list_pro_custom_category_icons_init'));

        //Shortcode wizard - does not appear in menus
        add_submenu_page(null,'Create Map Shortcode','Create Map Shortcode','edit_pages','createmapshortcode',array($this,'maplistpro_shortcode_creator'));        
        
        //Add admin preview script only to settings page
        add_action( 'admin_print_styles-' . $page, array($this,'maplistpro_admin_scripts'));
        //Add admin preview script only to icon pages
        add_action( 'admin_print_styles-' . $iconpage, array($this,'category_order_editor_scripts'));
    }

    /**********************
    ADMIN SETTINGS PAGE
     **********************/    
    //Get the options page from an include file
    function maplistpro_admin_options()
    {
        include(MAP_LIST_KO_PLUGIN_PATH . 'includes/admin/SettingsPage.php');
    }

    function maplistpro_admin_scripts()
    {
        /*Settings page js*/
        //Uses get_stylesheet_directory() to make child theme aware
        $params = array('pluginUrl' => MAP_LIST_KO_PLUGIN_URL,'altPluginUrl' => get_stylesheet_directory() . '/prettymapstyles/');
        wp_register_script('maplistpreviewer', MAP_LIST_KO_PLUGIN_URL . 'js/admin/SettingsPage.js');
        wp_localize_script('maplistpreviewer', 'maplistScriptParams', $params );
        wp_enqueue_script('maplistpreviewer' );
    }

    /**********************
    CATEGORY ORDERING PAGE
     **********************/

    //Get the category icons page from an include file
    function maplistpro_admin_icons()
    {
        include(MAP_LIST_KO_PLUGIN_PATH . 'includes/admin/CustomCategoryOrder.php');
    }

    //TODO:Localize all strings below
    /* register settings for icon page */
    function map_list_pro_custom_category_icons_init(){

        add_settings_section(
            'mlp_custom_category_icons_description',
            __('Simple instructions:','maplistpro'),
            array($this,'mlp_custom_category_icons_desc'),
            'maplist_page_maplistproicons'
         );

        add_settings_field(
            'mlp_custom_category_icons_list',
            '',
            array($this,'mlp_custom_category_icons_list'),
            'maplist_page_maplistproicons',
            'mlp_custom_category_icons_description'
         );

        register_setting(
            'mlp_custom_category_icons_options',
            'mlp_custom_category_icons_options',
            array($this,'mlp_custom_category_icons_validate')
         );
    }

    /* validate input */
    function mlp_custom_category_icons_validate($input){
        //If item is empty set it to default
        foreach($input as &$inp){
            if($inp == ""){
                $inp = array(30,"default/mapmarker1.png","default/shadow.png");
            }
            else{
                //Split into more usable array
                $inp = explode(',',$inp);
            }
        }

        return $input;
    }

    /* description text */
    function mlp_custom_category_icons_desc(){
        _e('<p>Click to expand and choose a custom icon colour. When there are multiple categories per location, categories at the top of the list show first. Drag and drop to set category order.</p>','maplistpro');
    }

    /* filed output */
    function mlp_custom_category_icons_list() {
        //Get saved custom icons
        $options = get_option('mlp_custom_category_icons_options');
        
        //Get all categories for locations
        //================================

        $args = array(
          'orderby' => 'name',
          'pad_counts' => 0,
          'hierarchical' => 0,
          'taxonomy' => 'map_location_categories',
          'hide_empty' => 0
        );

        $categories = get_categories($args);        
        
        //Get all folders for pins
        $markerOptions = scandir(MAP_LIST_KO_PLUGIN_PATH . 'images/pins');
        $markerArray = array();            

        foreach($markerOptions as $markerOptionFolder){
            //Not '.' or '..' as not real directories   
            if(is_dir(MAP_LIST_KO_PLUGIN_PATH . "images/pins/" . $markerOptionFolder)){
                if($markerOptionFolder != "." && $markerOptionFolder != ".." && $markerOptionFolder !='Attribution.txt'){                
                        $markerArray[] = array($markerOptionFolder,scandir(MAP_LIST_KO_PLUGIN_PATH . "images/pins/" . $markerOptionFolder));//TODO:Add in-use flag
                }
            }
        }            
            
        //Output all categories with hidden form fields
        ?>
        
        <ul id='IconPicker'>
            <?php    
        foreach($categories as $category){                            
            
            //No options set
            if(empty($options) || !isset($options[$category->slug]) || !isset($options[$category->slug][1])){
                echo "<li data-position='100'><span class='currentIcon' style='background-image:url(" . MAP_LIST_KO_PLUGIN_URL . "images/pins/default/mapmarker1.png);'>&nbsp;</span><label>$category->name</label>";            
            }
            else{
                //Output the label
                echo "<li data-position='{$options[$category->slug][0]}' data-marker='{$options[$category->slug][1]}'><span class='currentIcon' style='background-image:url(" . MAP_LIST_KO_PLUGIN_URL . "images/pins/" . $options[$category->slug][1] . ");'>&nbsp;</span><label>$category->name</label>";            
            }
            
            //See if there is a setting for it already
            if(isset($options[$category->slug])){
                
                if(isset($options[$category->slug][3])){
                    $existingCustomShadowOverrides =  ',' . $options[$category->slug][3] . ',' . $options[$category->slug][4] . ',' . $options[$category->slug][5] . ',' . $options[$category->slug][6];
                }
                else{
                    $existingCustomShadowOverrides = '';
                }
                ?>
                    
                <input type="hidden" class="known" name="mlp_custom_category_icons_options[<?php echo $category->slug; ?>]" value='<?php echo $options[$category->slug][0] . ',' . $options[$category->slug][1] . ',' . $options[$category->slug][2] . $existingCustomShadowOverrides; ?>' />
                <?php
            }
            else{
                ?>
                <input type="hidden" class="unknown" name="mlp_custom_category_icons_options[<?php echo $category->slug; ?>]" value='' />
                <?php
            }
            
            echo "<div class='iconChooser'><span>Choose an icon:</span><ul class='mapCategoryIcons'>";

            $i = 0;
            echo '<div id="AllIconChoices"><ul>';
            foreach($markerArray as $markerSet){
                
                echo '<li>';
                    echo '<h3>' . str_replace('_', ' ', $markerSet[0])  . '</h3><ul>';
                    
                    //See if there is a custom shadow
                    $customShadow = in_array('shadow.png',$markerSet[1]);
                    $customShadowOverrides = '';
                    //See if there are shadow overrides for size and position
                    if(in_array('shadowoverrides.txt',$markerSet[1])){     
                        $fileCont = file_get_contents(MAP_LIST_KO_PLUGIN_PATH . 'images/pins/' . $markerSet[0] . '/shadowoverrides.txt');
                        if($fileCont != ''){
                            $customShadowOverrides = 'data-shadowoverrides="' . $fileCont . '"';
                        }
                    }

                    foreach($markerSet[1] as $marker){
                        if($marker != "." && $marker != ".." && $marker !='shadow.png' && $marker != 'shadowoverrides.txt'){
                            echo "<li><a href='#' class='mapIcon' $customShadowOverrides data-iconshadow='$customShadow' data-iconimage='" . rawurlencode($marker) . "' data-iconfolder='" . rawurlencode($markerSet[0]) . "'><img src='" . MAP_LIST_KO_PLUGIN_URL . "images/pins/" . $markerSet[0] . "/" .  $marker . "' /></a></li>";   
                            $i++;
                        }
                    }
                echo '</ul></li>';
            }
            echo '</ul></div>';
            echo "</ul></div>";
            echo "</li>";            
        }
        
        ?> 
        </ul>
        <?php                    
    }

    /**********************
    LOAD ADMIN ICON CUSTOMISER SCRIPTS
     **********************/
    function category_order_editor_scripts()
    {
        //Add the javascript for the custom catgeory icons
        $params = array('pluginUrl' => MAP_LIST_KO_PLUGIN_URL);
        wp_register_script('maplisticons', MAP_LIST_KO_PLUGIN_URL . 'js/admin/CategoryOrderEditor.js');
        wp_localize_script('maplisticons', 'maplistScriptParams', $params );
        wp_enqueue_script('maplisticons' );
        wp_enqueue_script('jquery-ui-sortable' );

        //Ad styles for the same page
        wp_register_style('maplistIconCustomiserStyleSheets', MAP_LIST_KO_PLUGIN_URL . 'css/admin/CategoryOrderEditor.css');
        wp_enqueue_style( 'maplistIconCustomiserStyleSheets');
    }

    /*********************
    ADD SHORTCODE BUTTON
     **********************/

    function add_maplist_shortcode_button() {
        if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )
        {
            add_filter('mce_external_plugins', array($this,'add_shortcode_wizard'));
            add_filter('mce_buttons', array($this,'register_maplist_shortcode_button'));
        }
    }

    function register_maplist_shortcode_button($buttons) {
        array_push($buttons, "maplist");
        return $buttons;
    }

    function add_shortcode_wizard($plugin_array) {
        $plugin_array['maplist'] = MAP_LIST_KO_PLUGIN_URL.'js/admin/CreateShortcodeWizardModal.js';
        return $plugin_array;
    }
    

    function maplistpro_shortcode_creator($plugin_array){
        //edit.php?post_type=maplist&page=createmapshortcode
        include MAP_LIST_KO_PLUGIN_PATH . 'includes/admin/shortcode_wizard.php';
    }    


    /*********************
    AJAX CALLS FOR SHORTCODE WIZARD
     **********************/
    //Ajax call for add shortcode modal
    function get_all_maplocations_ajax() {
        global $wpdb; // this is how you get access to the database

        $args = array( 'post_type' => 'maplist','orderby' => 'title','order' => 'ASC', 'numberposts' => -1, 'post_status' => null);
        $attachments = get_children($args);
        $html = '<ul>';//Start the list

        foreach ( $attachments as $attachment ) {
            $html .= '<li><label><input type="checkbox" class="file" id="file_' . $attachment->ID . '" name="file_' . $attachment->ID .'" value="' . $attachment->ID . '">' . $attachment->post_title . '</label></li>';
        }
        $html .= '</ul>';//End the list
        echo $html;

        die(); // this is required to return a proper result
    }

    //Ajax call for add shortcode modal
    function get_all_mapcategories_ajax() {

        $args = array('taxonomy' => 'map_location_categories','orderby' => 'name','order' => 'ASC');

        $categories = get_categories( $args );

        $html = '<ul>';//Start the list
        foreach ( $categories as $category ) {
            $html .= '<li><label><input type="checkbox" class="file" id="file_' . $category->term_id . '" name="file_' . $category->term_id .'" value="' . $category->term_id . '">' . $category->cat_name . '</label></li>';
        }
        $html .= '</ul>';//End the list
        echo $html;

        die(); // this is required to return a proper result
    }

    /*********************
    Admin Columns setup
     **********************/
    //Define column heading for Map location list page
    function add_new_maplist_columns() {
        global $post;

        $new_columns['cb'] = '<input type="checkbox" />';
        $new_columns['id'] = __('Id', 'maplistpro');
        
        //add custom fields
        
        if($field['name'] != 'Latitude' && $field['name'] != 'Longitude'){
            $new_columns[$field['id']] = _x($field['name'], 'maplistpro');
        }       
        
        $new_columns['mapimage'] = _x('', 'column name');        
        
        $new_columns['title'] = _x('Title', 'maplistpro');
        $new_columns['address'] = _x('Address', 'maplistpro');
        $new_columns['maplocationcategories'] = __('Categories', 'maplistpro');

        return $new_columns;
    }

    //Get column data for Map location list page
    function manage_maplist_columns($column_name, $id) {
        global $post;
        if($column_name == "id"){
            echo $post->ID;
        }
        else if($column_name == "maplocationcategories")
        {
            //get map location categories for this post (custom taxonmy: map-location-categories)
            $categories = wp_get_object_terms($post->ID, 'map_location_categories', array('orderby' => 'name', 'order' => 'ASC'));
                ?>
                <ul>
                <?php
            if ( !empty( $categories ) ) {
                foreach ( $categories as $cat ) {?>
                        <li><?php
                    if($cat->parent != 0) {
                        echo '>> ';
                    }
                            ?>
                            <a href='edit.php?map-location-categories=<?php echo $cat->slug;?>&post_type=maplist'><?php echo $cat->name;?></a>
                        </li>
                    <?php
                }
                    ?>
                    </ul>
                <?php
            }
            else{
                _e('Uncategorized','maplistpro');
            }
        }
        else if($column_name == "mapimage"){
            $lat = get_post_meta($post->ID, 'maplist_latitude', true);
            $lng = get_post_meta($post->ID, 'maplist_longitude', true);
                ?>
                <img border="0" alt="<?php the_title($post->ID); ?>" src="//maps.googleapis.com/maps/api/staticmap?center=<?php echo $lat; ?>,<?php echo $lng; ?>&zoom=14&size=100x100&sensor=false&markers=color:blue|<?php echo $lat; ?>,<?php echo $lng; ?>" title="Latitude: <?php echo $lat; ?> Longitude: <?php echo $lng; ?>" alt="Latitude: <?php echo $lat; ?> Longitude: <?php echo $lng; ?>">
                <?php
        }
        else if($column_name == "address"){
            echo get_post_meta($post->ID, 'maplist_address', true);                
        }
        else{
            echo get_post_meta($id, $column_name, true);
        }
    }            
    
    /*********************
    GET MARKER IMAGES
     **********************/
    //Get a single url from an array (or single string) of map markers
    function get_marker_images($postTerms){

        //Get all category icons
        $categoryIcons = get_option('mlp_custom_category_icons_options');
        //First number = position, second = icon, third = shadow
        //Default icon
        $catIcon = array(101,'default/mapmarker1.png','default/shadow.png');

        //Only do this if there are custom icons
        if($categoryIcons != ''){

            //See if this is a string passed in
            if(!is_array($postTerms)){
                $postTerms = array($postTerms);
            }

            foreach($postTerms as $postTerm){

                //Get the matching icon from $categoryIcons
                //if its index is higher than the previous
                if($postTerm != 'uncategorised'){
                    if(isset($postTerm->slug)){
                        if(array_key_exists($postTerm->slug,$categoryIcons)){
                            //If category is currently set check it
                            if(isset($tempCat)){
                                if($categoryIcons[$postTerm->slug][0] < $tempCat[0]){
                                    $tempCat = $categoryIcons[$postTerm->slug]; 
                                }
                            }                                
                            else{
                                //otherwise just set it
                                $tempCat = $categoryIcons[$postTerm->slug];    
                            }
                        }
                    }
                    else{
                        if(array_key_exists(strtolower($postTerm),$categoryIcons)){
                            $tempCat = $categoryIcons[strtolower($postTerm)];
                        }
                    }
                }              
            }

            //If found in the array        
            if(isset($tempCat)){
                $shadow = $tempCat[2] != 'none' ? MAP_LIST_KO_PLUGIN_URL . "images/pins/" . $tempCat[2] : '';
                $shadowOverrides = null;                    
                
                //Custom overrides
                if(isset($tempCat[3]) && $tempCat[3] != 'undefined'){
                    $shadowOverrides = array($tempCat[3],$tempCat[4],$tempCat[5],$tempCat[6]);
                }
                
                return array(
                        "marker" => MAP_LIST_KO_PLUGIN_URL . "images/pins/" . $tempCat[1],
                        "shadow" => $shadow,
                        "overrides" => $shadowOverrides
                    );
            }              
        }


        //Return the default marker
        return array(
                "marker" => MAP_LIST_KO_PLUGIN_URL . "images/pins/default/mapmarker1.png",
                "shadow" => MAP_LIST_KO_PLUGIN_URL . "images/pins/default/shadow.png"
            );
        
    }

    function register_maplist_shortcode($atts, $content = null){
        
        //Get attributes from shortcode
        global $options;
        $options = '';
        $options = shortcode_atts(
            array(
                "mapid" => "0",
                "categories" => "",
                "orderby" => "title",
                "orderdir" => "ASC",
                "hidefilter" => "false",
                "hidesort" => "false",
                "hidegeo" => "false",
                "hidesearch" => "false",
                "homelocationid" => "",
                "initialsorttype" => "title",
                "openinnew" => false,
                "locationsperpage" => "3",
                "locationstoshow" => "",
                "daycategorymode" => false,
                "showdirections" => "true",
                "geoenabled" => "false",
                "clustermarkers" => 'false',
                "clustermaxzoomlevel" => '15',
                "clustergridsize" => '50',
                "defaultzoom" => "",
                "fullpageviewenabled" => "",
                "initialmaptype" => "ROADMAP",
                "selectedzoomlevel" => "",
                "categoriesticked" => "false",
                "simplesearch" => "false",
                "hidecategoriesonitems" => "false",
                "hideviewdetailbuttons" => "false",
                "viewstyle" => "both",//listonly,maponly,both
                "mapposition" => "above",//above,leftmap,rightmap
                "startlatlong" => "",
                "country" => "",
                "showthumbnailicon" => false,
                "limitresults" => -1,
                "keepzoomlevel" => false,
                "hideaddress" => false
            ), $atts);

        extract($options);

        //Create map object
        $newMap = new Map;        
        
        //Set id of this map
        $newMap->id = self::$counter;
        
        $categoriesTerms = '';
        $locationstoshowarray = array();

        //Special category as day mode
        if($daycategorymode == true){
            //Get todays day as int
            $dw = date( "w");
            switch ($dw)
            {
                case 0:
                    $categoriesTerms = __('sunday','maplistpro');
                    break;
                case 1:
                    $categoriesTerms = __('monday','maplistpro');
                    break;
                case 2:
                    $categoriesTerms = __('tuesday','maplistpro');
                    break;
                case 3:
                    $categoriesTerms = __('wednesday','maplistpro');
                    break;
                case 4:
                    $categoriesTerms = __('thursday','maplistpro');
                    break;
                case 5:
                    $categoriesTerms = __('friday','maplistpro');
                    break;
                case 6:
                    $categoriesTerms = __('saturday','maplistpro');
                    break;
            }             
        }
        else{        
            //If using the location filter
            if($locationstoshow != ""){
                //Get an array of files to display
                $locationstoshowarray = explode(',',$locationstoshow);
            }
            else{
                //else see if we have a cat filter
                if($categories != ""){

                    $categoriesToShow = explode(',',$categories);
                    //Get our terms string
                    foreach($categoriesToShow as $key=>$value){
                        $term = get_term($value,'map_location_categories');
                        $categoriesTerms .= $term->slug;//Must be slug for get_posts
                        $categoriesTerms .= ',';
                    }
                }
            }
        }        

        $locationArgs = array(
            'post_type' => 'maplist',
            'orderby' => $orderby,
            'order' => $orderdir,
            'post_status' => 'publish',
            'map_location_categories' => $categoriesTerms,
            'suppress_filters' => true,
            'posts_per_page'  => 2000,
            );

        $mapLocations = get_posts($locationArgs);

        $this->numberOfLocations= count($mapLocations);

        //Full page view option
        if($fullpageviewenabled == ''){
            $fullpageviewenabled = get_option('maplist_fullpageviewenabled');
        }
        
        //Array of categories actually in use on this map
        $allCategoriesUsedByID = array();
        $allCategoriesUsedObjects = array();

        //Is the uncategorised option needed
        $uncatNeeded = false;

        foreach ($mapLocations as $mapLocation)
        {
            //See if in locations list
            if($locationstoshow != "" && !in_array($mapLocation->ID, $locationstoshowarray)){
                continue;
            }

            //Create location
            $kolocation = new location();

            //Get all terms used by this location
            //Get all meta fields for this post
            $locationMetaFields = get_post_custom($mapLocation->ID);

            $lat = $locationMetaFields['maplist_latitude'][0]; //get_post_meta($mapLocation->ID, 'maplist_latitude', true);
            $lng = $locationMetaFields['maplist_longitude'][0];//get_post_meta($mapLocation->ID, 'maplist_longitude', true);
            $temp = array_key_exists('maplist_alternateurl', $locationMetaFields) ? $locationMetaFields['maplist_alternateurl'][0] : '';//get_post_meta($mapLocation->ID, 'maplist_alternateurl', true);


            //No external link
            if($temp == ''){
                //If full page view and there is content show the view detail button
                if($fullpageviewenabled == true && $mapLocation->post_content != ''){
                    $alternateUrl = get_permalink($mapLocation->ID);
                }
                else{
                    $alternateUrl = '';
                }
            }
            else{
                $alternateUrl = $temp;    
            }
            
            
            $imageUrlTemp = wp_get_attachment_image_src( get_post_thumbnail_id($mapLocation->ID), array(50,50));
            $imageUrl = $imageUrlTemp[0];            
            $address = isset($locationMetaFields['maplist_address']) ? $locationMetaFields['maplist_address'][0] : '';

            //Get all terms used by this location
            $postCategories = wp_get_object_terms($mapLocation->ID, 'map_location_categories');

            //Holder for our category objects to be passed to front end
            $assPostCategories = array();

            //If no cat add uncat
            if(count($postCategories) == 0){
                $uncatNeeded = true;
            }

            //Add new categories to array for menu
            foreach($postCategories as $category){
                //Make sure it's unique
                //in_array ( mixed $needle , array $haystack [, bool $strict = FALSE ] )
                if(!in_array($category->term_id,$allCategoriesUsedByID)){
                    $allCategoriesUsed[] = new Category($category->name,$category->slug,'');
                    //Put it in found array
                    $allCategoriesUsedByID[] = $category->term_id;
                }

                $assPostCategories[] = new Category($category->name,$category->slug,'');
            }

            //Show thumbnail icon
            if($showthumbnailicon){
                $tinyImageUrlTemp = wp_get_attachment_image_src( get_post_thumbnail_id($mapLocation->ID));
                if($tinyImageUrlTemp != ''){
                    $kolocation->smallImageUrl = MAP_LIST_KO_PLUGIN_URL . '/includes/timthumb.php?src=' . $tinyImageUrlTemp[0] . '&w=25&h=25';
                }                
            }
            
            $markerImages = $this->get_marker_images($postCategories);
            
            //Create object
            $kolocation->title = $mapLocation->post_title;            
            $kolocation->cssClass = 'loc-' . $mapLocation->ID;  //strtolower(preg_replace("/[^A-Za-z0-9]/", "", $mapLocation->post_title));
            $kolocation->address = $address;
            $kolocation->latitude = $lat;
            $kolocation->longitude = $lng;
            $kolocation->pinColor = '';
            $kolocation->pinImageUrl = $markerImages['marker'];//$this->get_marker_images($postCategories);
            $kolocation->pinShadowImageUrl = $markerImages['shadow'];//$this->get_shadow_image();

            if(isset($markerImages['overrides'])){
                $kolocation->pinShadowOverrides = $markerImages['overrides'];//$this->get_shadow_image();
            }
            $kolocation->imageUrl = $imageUrl;
            $kolocation->address = $address;
            $kolocation->categories = $assPostCategories;
            $kolocation->_mapMarker = '';
            $kolocation->locationUrl = $alternateUrl;
            
            //Additional detail
            //==================================

            //Address            
            $topArea = "<div class='address'>$address</div>";
            
            //Get description, fire it through wpautop to convert crs to p tags, then do shortcode stuff
            $tempDesc = isset($locationMetaFields['maplist_description']) ? do_shortcode(wpautop($locationMetaFields['maplist_description'][0])) : '';
                        
            $kolocation->description = $topArea . $tempDesc;

            //Fire filter to allow whatever is needed to be done - pass fields in case they're needed
            $kolocation->description = apply_filters( 'mlp_location_description', $kolocation->description,$locationMetaFields,$mapLocation);


            //Add locations to map
            $newMap->locations[] = $kolocation;
        }

        //No categories
        if(!isset($allCategoriesUsed)){
            $allCategoriesUsed[] = new Category('Uncategorized','uncategorized','');
        }

        $newMap->categories = $allCategoriesUsed;

        //Get the output file
        $filePath = MAP_LIST_KO_PLUGIN_PATH . 'includes/shortcode_output.php';

        
        //$mlpoutputhtml = '';
        $html = include($filePath);


        $startlat = '';
        $startlong = '';

        if($startlatlong != ''){
            $splitString = explode (',', $startlatlong);
            $startlat = $splitString[0];
            $startlong = $splitString[1];
        }

        //Set options object
        $newMap->options = array(
            'orderby' => $orderby,
            'orderdir' => $orderdir,            
            'locationsperpage' => $locationsperpage,
            'simplesearch' => $simplesearch,
            'country' => $country,
            'geoenabled' => $geoenabled,
            "openinnew" => $openinnew,
            "limitresults" => $limitresults,            
            "locationsperpage" => $locationsperpage,
            "locationstoshow" => $locationstoshow,
            "showdirections" => $showdirections,
            "fullpageviewenabled" => $fullpageviewenabled,
            "selectedzoomlevel" => $selectedzoomlevel,
            "initialmaptype" => $initialmaptype,
            "categoriesticked" => $categoriesticked,
            "hidecategoriesonitems" => $hidecategoriesonitems,
            "hideviewdetailbuttons" => $hideviewdetailbuttons,
            "clustermarkers" => $clustermarkers,
            "startlat" => $startlat,
            "startlong" => $startlong,
            "defaultzoom" => $defaultzoom,
            "hideaddress" => $hideaddress,
            "viewstyle" => $viewstyle,
            "keepzoomlevel" => $keepzoomlevel
        );
        

        //Get home location if needed
        if($homelocationid != ''){
            $home = get_post($homelocationid);

            //Make sure a home location is found
            if($home){

                //Create location
                $homelocation = new location();

                //Get all meta fields for this post
                $locationMetaFields = get_post_custom($mapLocation->ID);

                $lat = $locationMetaFields['maplist_latitude'][0];
                $lng = $locationMetaFields['maplist_longitude'][0];               
                
                $address = isset($locationMetaFields['maplist_address']) ? $locationMetaFields['maplist_address'][0] : '';

                //Categories are needed to work out icon to use
                //================================================

                //Get all terms used by this location
                $postCategories = wp_get_object_terms($mapLocation->ID, 'map_location_categories');

                //Holder for our category objects to be passed to front end
                $assPostCategories = array();

                //If no cat add uncat
                if(count($postCategories) == 0){
                    $uncatNeeded = true;
                }

                //Add new categories to array for menu
                foreach($postCategories as $category){
                    //Make sure it's unique
                    if(!in_array($category->term_id,$allCategoriesUsedByID)){
                        $allCategoriesUsed[] = new Category($category->name,$category->slug,'');
                        //Put it in found array
                        $allCategoriesUsedByID[] = $category->term_id;
                    }

                    $assPostCategories[] = new Category($category->name,$category->slug,'');
                }
                
                $markerImages = $this->get_marker_images($postCategories);
                
                //Create object
                $homelocation->title = $mapLocation->post_title;            
                $homelocation->address = $address;
                $homelocation->latitude = $lat;
                $homelocation->longitude = $lng;
                $homelocation->pinColor = '';
                $homelocation->pinImageUrl = $markerImages['marker'];//$this->get_marker_images($postCategories);
                $homelocation->pinShadowImageUrl = $markerImages['shadow'];//$this->get_shadow_image();

                if(isset($markerImages['overrides'])){
                    $homelocation->pinShadowOverrides = $markerImages['overrides'];//$this->get_shadow_image();
                }
                $homelocation->imageUrl = $imageUrl;
                $homelocation->address = $address;
                $homelocation->categories = $assPostCategories;
                $homelocation->_mapMarker = '';
                $homelocation->locationUrl = $alternateUrl;            

                //Add home location to map object
                $newMap->homelocation =  $homelocation;
            }
        }

        //Check to see if this is single object
        self::$maps[] = $newMap;
        
        //Encode all data to json
        $kojsobject = json_encode(self::$maps);

        //See if infowindows are preferred
        $disableInfoBoxes = get_option('maplist_disableinfoboxes');
        
        /*Map page js*/
        $params = array(
            'KOObject' => self::$maps,
            'pluginurl' => MAP_LIST_KO_PLUGIN_URL,          
            'defaultSearchMessage' => $this->searchTextDefault,
            'defaultSearchLocationMessage' => __('location...','maplistpro'),
            'noSelectedTypeMessage' => __('No locations of selected type(s) found.','maplistpro'),
            'noTypeMessage' => __('No categories selected.','maplistpro'),
            'printDirectionsMessage' => __('Print directions','maplistpro'),
            'noFilesFoundMessage' => __('No locations found.','maplistpro'),
            'noGeoSupported' => __('Geolocation is not supported by this browser.','maplistpro'),
            'measurementUnits' => get_option('maplist_measurementunits'),
            'disableInfoBoxes' => $disableInfoBoxes,
            'measurementUnitsMetricText' => __('Kms','maplistpro'),
            'measurementUnitsImperialText' => __('Miles','maplistpro'),
            'viewLocationDetail' => __('View location','maplistpro'),
            'distanceWithinText' => __('within','maplistpro'),
            'distanceOfText' => __('of','maplistpro'),
            'hideviewdetailbuttons' => $hideviewdetailbuttons
        );

        $deps = array('knockout','jquery','map_list-google-places','map_list-google-marker-clusterer');       
        
        if($disableInfoBoxes != 'true'){
            $deps[] = 'infowindow_custom';
        }
        
        wp_register_script('maplistko', MAP_LIST_KO_PLUGIN_URL . 'js/maplistfront.js',$deps,false,true);
        wp_enqueue_script('maplistko');
        
        wp_localize_script('maplistko', 'maplistScriptParamsKo', $params );       
        
        //Shortcode is on page
        $isOnPage = true;
        return $this->mlpoutputhtml;
    }
}

//Activation hook
register_activation_hook(__FILE__, array('MapListProKO','map_list_pro_activate'));

$MapListProKO = new MapListProKO();

class Map
{
    public $id;
    public $locations = array();
    public $homelocation;
    public $categories;
    public $options;
}

class Location
{
    public $title;
    public $cssClass;
    public $description;
    public $dateCreated;
    public $categories;
    public $latitude;
    public $longitude;
    public $address;
    public $pinImageUrl;
    public $pinShadowImageUrl;
    public $pinShadowOverrides;
    public $pinShape;
    public $imageUrl;
    public $smallImageUrl;
    public $locationUrl;
    public $_mapMarker;
    public $expanded;
}

class Category
{
    public function Category($title,$slug,$markerImage){
        $this->title = $title;
        $this->slug = $slug;
        if($markerImage){
            $this->markerImage = $markerImage;
        }
        else{
            //Default marker
            $markerImage = MAP_LIST_KO_PLUGIN_URL . 'images/pins/default/BluePin.png';
        }
    }

    public $title;
    public $slug;
    public $selected;
    public $markerImage;
}