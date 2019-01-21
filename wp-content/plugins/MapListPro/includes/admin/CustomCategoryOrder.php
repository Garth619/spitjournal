<div class="wrap">
    <?php screen_icon();?>
    <h2><?php _e('Custom category icons','maplistpro') ?></h2>
    <form id="mlp_custom_category_icons_options" action="options.php" method="post">
        <!-- Name = cat slug, First number = position, second = icon id -->
        <?php         
            settings_fields('mlp_custom_category_icons_options');
            do_settings_sections('maplist_page_maplistproicons'); 
            submit_button(__('Save options','maplistpro'), 'primary', 'maplist_page_maplistproicons_submit');
        ?>
    </form>
</div>