<?php
        //Add css file for admin
        $admin_stylesheet_url = MAP_LIST_KO_PLUGIN_URL . 'css/admin/SettingsPage.css';

        //Core styles
        wp_register_style('maplistCoreStyleSheets', MAP_LIST_KO_PLUGIN_URL . 'css/MapListProCore.css');
        wp_enqueue_style( 'maplistCoreStyleSheets');

        //Colour styles
        wp_register_style('maplistadminStyleSheets', $admin_stylesheet_url);
        wp_enqueue_style( 'maplistadminStyleSheets');

        //Blank message created
        $message = "";

        //The @ suppresses an error if post[action] is not set
        if (@$_POST['action'] == 'update')
        {
            //todo: make this a method and save an array
            //Set the option to the form variable

            //Need to check for old settings first
            updateOption('stylesheet_to_use');
            //updateOption('usecustomtemplate');
            updateOption('fullpageviewenabled');
            updateOption('measurementunits');
            updateOption('default_edit_map_location_lat');
            updateOption('default_edit_map_location_long');
            updateOption('default_edit_map_zoom');
            updateOption('disableinfoboxes');
            updateOption('google_map_language');
            updateOption('category_name');
            
            //Send a message to the user to let them know it was updated
            $message = '<div id="message" class="updated fade"><p><strong>' . __('Options saved','maplistpro') . '</strong></p></div>';
        }

          //path to directory to scan
	  $directory = MAP_LIST_KO_PLUGIN_PATH . 'styles/';
	  $altDirectory = get_template_directory() . '/prettymapstyles/';

	  //get all files with a .css extension.
	  $styles = glob($directory . "*.css");
	  $altStyles = glob($altDirectory . "*.css");

	    //Get options
	    $options['maplist_stylesheet_to_use'] = get_option('maplist_stylesheet_to_use');
        //$options['maplist_usecustomtemplate'] = get_option('maplist_usecustomtemplate');
        $options['maplist_fullpageviewenabled'] = get_option('maplist_fullpageviewenabled');
        $options['maplist_measurementunits'] = get_option('maplist_measurementunits');
        $options['maplist_default_edit_map_location_lat'] = get_option('maplist_default_edit_map_location_lat');
        $options['maplist_default_edit_map_location_long'] = get_option('maplist_default_edit_map_location_long');
        $options['maplist_default_edit_map_zoom'] = get_option('maplist_default_edit_map_zoom');
        $options['maplist_disableinfoboxes'] = get_option('maplist_disableinfoboxes');
        $options['maplist_google_map_language'] = get_option('maplist_google_map_language');
        $options['maplist_category_name'] = get_option('maplist_category_name');
        
        //Display options form
        echo '<div class="wrap">' . $message;
	    echo '<div class="icon32"><br /></div>';
	    echo '<h2>' . __('Settings - Map List Pro','maplistpro') . '</h2>';
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="action" value="update" />';

        //Styling
        echo '<h3>' . __('Styling','maplistpro'). '</h3>';
        echo '<table class="form-table">';
            echo '<tbody>';
            echo '<tr valign="top">';
                echo '<th scope="row">' . __('Pick a style','maplistpro'). '</th>';
                echo '<td>';
                    echo '<style id="Previewer"></style>';

                    echo '<select name="stylesheet_to_use" id="show_pages">';

                    //Print each available css file
                    //Core styles
                    
                    foreach($styles as $style)
                    {
                        echo '<option value="' . basename($style) .'"' . selected($options['maplist_stylesheet_to_use'],basename($style),false)  . '>' . friendlyCssFileName(basename($style)) . '</option>';
                    }
                    //Custom styles
                    foreach($altStyles as $style)
                    {
                        echo '<option value="' . basename($style) .'#"' . selected($options['maplist_stylesheet_to_use'],(basename($style) . '#'),false) . '>' . friendlyCssFileName(basename($style)) . ' (Custom)</option>';
                    }

                    echo '</select>';
                        
                echo '</td>';
            echo '</tr>';
            echo '</tbody>';
        echo '</table>';
        
        //Other options
        echo '<h3>' . __('Other options','maplistpro'). '</h3>
        <p>' . __('These options are site wide and WILL override settings on individual lists.','maplistpro'). '</p>
        <table class="form-table">
        <tbody>

        <tr valign="top">
        <th scope="row">' . __('Distance units','maplistpro'). '</th>
        <td><fieldset><legend class="screen-reader-text"><span>' . __('Distance units','maplistpro'). '</span></legend>
        <label for="measurementunits">
        <select name="measurementunits" id="measurementunits">
            <option value="IMPERIAL"' . ($options['maplist_measurementunits'] == 'IMPERIAL' ? 'selected="selected"' : '')  . '>' . __('Imperial','maplistpro'). '</option>
            <option value="METRIC"' . ($options['maplist_measurementunits'] == 'METRIC' ? 'selected="selected"' : '') . '>' . __('Metric','maplistpro'). '</option>
        </select>
        </fieldset></td>
        </tr>
        <tr valign="top">
        <th scope="row">&nbsp;</th>
        <td><fieldset><legend class="screen-reader-text"><span>' . __('Enable full page view for locations','maplistpro'). '</span></legend>
        <label for="fullpageviewenabled"><input type="checkbox"' . ($options['maplist_fullpageviewenabled'] == 'true' ? 'checked="checked"' : '')  . ' value="true" name="fullpageviewenabled" id="fullpageviewenabled"/> ' . __('Enable full page view for locations.','maplistpro'). ' </label>
        </fieldset></td>
        </tr>
        <tr valign="top">
        <th scope="row">&nbsp;</th>
        <td><fieldset><legend class="screen-reader-text"><span>' . __('Disable styled infoboxes for maps.','maplistpro'). '</span></legend>
        <label for="disableinfoboxes"><input type="checkbox"' . ($options['maplist_disableinfoboxes'] == 'true' ? 'checked="checked"' : '')  . ' value="true" name="disableinfoboxes" id="disableinfoboxes"/> ' . __('Disable styled infoboxes for maps.','maplistpro'). ' </label>
        </fieldset></td>
        </tr>';
        echo '<tr valign="top">
        <th scope="row">' . __('Override category label','maplistpro'). '</th>
        <td><fieldset><legend class="screen-reader-text"><span>' . __('Override category label','maplistpro'). '</span></legend>
        <label for="fullpageviewenabled"><input type="text" value="' . $options['maplist_category_name'] . '" name="category_name" id="category_name"/></label>
        </fieldset></td>
        </tr>';

        //Google maps language
        echo '<tr valign="top">';
            echo '<th scope="row">' . __('Google Map Language','maplistpro'). '</th>';
            echo '<td><fieldset><legend class="screen-reader-text"><span>' . __('Google Map Language','maplistpro'). '</span></legend>';
                echo '<label for="google_map_language">';
            
                echo '<select name="google_map_language" id="google_map_language">';
                    echo '<option value="en" ' . selected($options['maplist_google_map_language'],'en',false) .'>' . __('ENGLISH','maplistpro')  . '</option>
                    <option value="ar" ' . selected($options['maplist_google_map_language'],'ar',false) . '>' . __('ARABIC','maplistpro'). '</option>
                    <option value="eu" ' . selected($options['maplist_google_map_language'],'eu',false) . '>' . __('BASQUE','maplistpro'). '</option>
                    <option value="bg" ' . selected($options['maplist_google_map_language'],'bg',false) . '>' . __('BULGARIAN','maplistpro'). '</option>
                    <option value="bn" ' . selected($options['maplist_google_map_language'],'bn',false) . '>' . __('BENGALI','maplistpro'). '</option>
                    <option value="ca" ' . selected($options['maplist_google_map_language'],'ca',false) . '>' . __('CATALAN','maplistpro'). '</option>
                    <option value="cs" ' . selected($options['maplist_google_map_language'],'cs',false) . '>' . __('CZECH','maplistpro') . '</option>
                    <option value="da" ' . selected($options['maplist_google_map_language'],'da',false) . '>' . __('DANISH','maplistpro') . '</option>
                    <option value="de" ' . selected($options['maplist_google_map_language'],'de',false) . '>' . __('GERMAN','maplistpro') . '</option>
                    <option value="el" ' . selected($options['maplist_google_map_language'],'el',false) . '>' . __('GREEK','maplistpro') . '</option>                    
                    <option value="en-AU" ' . selected($options['maplist_google_map_language'],'en-AU',false) . '>' . __('ENGLISH (AUSTRALIAN)','maplistpro') . '</option>
                    <option value="en-GB" ' . selected($options['maplist_google_map_language'],'en-GB',false) .'>' . __('ENGLISH (GREAT BRITAIN)','maplistpro') .  '</option>
                    <option value="es" ' . selected($options['maplist_google_map_language'],'es',false) . '>' . __('SPANISH','maplistpro') . '</option>
                    <option value="fa" ' . selected($options['maplist_google_map_language'],'fa',false) . '>' . __('FARSI','maplistpro') . '</option>
                    <option value="fi" ' . selected($options['maplist_google_map_language'],'fi',false) . '>' . __('FINNISH','maplistpro') . '</option>
                    <option value="fil" ' . selected($options['maplist_google_map_language'],'fil',false) . '>' . __('FILIPINO','maplistpro') . '</option>
                    <option value="fr" ' . selected($options['maplist_google_map_language'],'fr',false) . '>' . __('FRENCH','maplistpro') . '</option>
                    <option value="gl" ' . selected($options['maplist_google_map_language'],'gl',false) . '>' . __('GALICIAN','maplistpro') . '</option>
                    <option value="gu" ' . selected($options['maplist_google_map_language'],'gu',false) . '>' . __('GUJARATI','maplistpro') . '</option>
                    <option value="hi" ' . selected($options['maplist_google_map_language'],'hi',false) . '>' . __('HINDI','maplistpro') . '</option>
                    <option value="hr" ' . selected($options['maplist_google_map_language'],'hr',false) . '>' . __('CROATIAN','maplistpro') . '</option>
                    <option value="hu" ' . selected($options['maplist_google_map_language'],'hu',false) . '>' . __('HUNGARIAN','maplistpro') . '</option>
                    <option value="id" ' . selected($options['maplist_google_map_language'],'id',false) . '>' . __('INDONESIAN','maplistpro') . '</option>
                    <option value="it" ' . selected($options['maplist_google_map_language'],'it',false) . '>' . __('ITALIAN','maplistpro') . '</option>
                    <option value="iw" ' . selected($options['maplist_google_map_language'],'iw',false) . '>' . __('HEBREW','maplistpro') . '</option>
                    <option value="ja" ' . selected($options['maplist_google_map_language'],'ja',false) . '>' . __('JAPANESE','maplistpro') . '</option>
                    <option value="kn" ' . selected($options['maplist_google_map_language'],'kn',false) . '>' . __('KANNADA','maplistpro') . '</option>
                    <option value="ko" ' . selected($options['maplist_google_map_language'],'ko',false) . '>' . __('KOREAN','maplistpro') . '</option>
                    <option value="lt" ' . selected($options['maplist_google_map_language'],'lt',false) . '>' . __('LITHUANIAN','maplistpro') . '</option>
                    <option value="lv" ' . selected($options['maplist_google_map_language'],'lv',false) . '>' . __('LATVIAN','maplistpro') . '</option>
                    <option value="ml" ' . selected($options['maplist_google_map_language'],'ml',false) . '>' . __('MALAYALAM','maplistpro') . '</option>
                    <option value="mr" ' . selected($options['maplist_google_map_language'],'mr',false) . '>' . __('MARATHI','maplistpro') . '</option>
                    <option value="nl" ' . selected($options['maplist_google_map_language'],'nl',false) . '>' . __('DUTCH','maplistpro') . '</option>
                    <option value="no" ' . selected($options['maplist_google_map_language'],'no',false) . '>' . __('NORWEGIAN','maplistpro') . '</option>
                    <option value="pl" ' . selected($options['maplist_google_map_language'],'pl',false) . '>' . __('POLISH','maplistpro') . '</option>
                    <option value="pt" ' . selected($options['maplist_google_map_language'],'pt',false) . '>' . __('PORTUGUESE','maplistpro') . '</option>
                    <option value="pt-BR" ' . selected($options['maplist_google_map_language'],'pt-BR',false) .'>' . __('PORTUGUESE (BRAZIL)','maplistpro')  . '</option>
                    <option value="pt-PT" ' . selected($options['maplist_google_map_language'],'pt-PT',false) . '>' . __('PORTUGUESE (PORTUGAL)','maplistpro') . '</option>
                    <option value="ro" ' . selected($options['maplist_google_map_language'],'ro',false) . '>' . __('ROMANIAN','maplistpro') . '</option>
                    <option value="ru" ' . selected($options['maplist_google_map_language'],'ru',false) . '>' . __('RUSSIAN','maplistpro') . '</option>
                    <option value="sk" ' . selected($options['maplist_google_map_language'],'sk',false) . '>' . __('SLOVAK','maplistpro') . '</option>
                    <option value="sl" ' . selected($options['maplist_google_map_language'],'sl',false) . '>' . __('SLOVENIAN','maplistpro') . '</option>
                    <option value="sr" ' . selected($options['maplist_google_map_language'],'sr',false) . '>' . __('SERBIAN','maplistpro') . '</option>
                    <option value="sv" ' . selected($options['maplist_google_map_language'],'sv',false) . '>' . __('SWEDISH','maplistpro') . '</option>
                    <option value="tl" ' . selected($options['maplist_google_map_language'],'tl',false) . '>' . __('TAGALOG','maplistpro') . '</option>
                    <option value="ta" ' . selected($options['maplist_google_map_language'],'ta',false) . '>' . __('TAMIL','maplistpro') . '</option>
                    <option value="te" ' . selected($options['maplist_google_map_language'],'te',false) . '>' . __('TELUGU','maplistpro') . '</option>
                    <option value="th" ' . selected($options['maplist_google_map_language'],'th',false) . '>' . __('THAI','maplistpro') . '</option>
                    <option value="tr" ' . selected($options['maplist_google_map_language'],'tr',false) . '>' . __('TURKISH','maplistpro') . '</option>
                    <option value="uk" ' . selected($options['maplist_google_map_language'],'uk',false) .'>' . __('UKRAINIAN','maplistpro')  . '</option>
                    <option value="vi" ' . selected($options['maplist_google_map_language'],'vi',false) .'>' . __('VIETNAMESE','maplistpro')  . '</option>
                    <option value="zh-CN" ' . selected($options['maplist_google_map_language'],'zh-CN',false) . '>' . __('CHINESE (SIMPLIFIED)','maplistpro') . '</option>
                    <option value="zh-TW" ' . selected($options['maplist_google_map_language'],'zh-TW',false) . '>' . __('CHINESE (TRADITIONAL)','maplistpro') . '</option>';

                echo '</select>';
            
            echo '</fieldset></td>';
        echo '</tr>';

        echo '<tr valign="top">
        <th scope="row">' . __('Default location for edit screen','maplistpro'). '</th>
        <td><fieldset>
        <label for="defaultEditMapLocationLat">' . __('Latitude:','maplistpro'). ' <input type="text" value="' . $options['maplist_default_edit_map_location_lat'] . '" name="default_edit_map_location_lat" id="default_edit_map_location_lat"/></label>
        </fieldset>
        <fieldset>
            <label for="defaultEditMapLocationLong">' . __('Longitude:','maplistpro'). ' <input type="text" value="' . $options['maplist_default_edit_map_location_long'] . '" name="default_edit_map_location_long" id="default_edit_map_location_long"/></label>
        </fieldset>
<fieldset>
        <label for="defaultEditMapZoom">
        ' . __('Zoom','maplistpro'). ' <select name="default_edit_map_zoom" id="default_edit_map_zoom">
            <option value="None"' . ($options['maplist_default_edit_map_zoom'] == '' ? 'selected="selected"' : '')  . '>' . __('Please select','maplistpro'). '</option>
            <option value="1"' . ($options['maplist_default_edit_map_zoom'] == '1' ? 'selected="selected"' : '')  . '>1</option>
            <option value="2"' . ($options['maplist_default_edit_map_zoom'] == '2' ? 'selected="selected"' : '') . '>2</option>
            <option value="3"' . ($options['maplist_default_edit_map_zoom'] == '3' ? 'selected="selected"' : '') . '>3</option>
            <option value="4"' . ($options['maplist_default_edit_map_zoom'] == '4' ? 'selected="selected"' : '') . '>4</option>
            <option value="5"' . ($options['maplist_default_edit_map_zoom'] == '5' ? 'selected="selected"' : '') . '>5</option>
            <option value="6"' . ($options['maplist_default_edit_map_zoom'] == '6' ? 'selected="selected"' : '') . '>6</option>
            <option value="7"' . ($options['maplist_default_edit_map_zoom'] == '7' ? 'selected="selected"' : '') . '>7</option>
            <option value="8"' . ($options['maplist_default_edit_map_zoom'] == '8' ? 'selected="selected"' : '') . '>8</option>
            <option value="9"' . ($options['maplist_default_edit_map_zoom'] == '9' ? 'selected="selected"' : '') . '>9</option>
            <option value="10"' . ($options['maplist_default_edit_map_zoom'] == '10' ? 'selected="selected"' : '') . '>10</option>
            <option value="11"' . ($options['maplist_default_edit_map_zoom'] == '11' ? 'selected="selected"' : '') . '>11</option>
            <option value="12"' . ($options['maplist_default_edit_map_zoom'] == '12' ? 'selected="selected"' : '') . '>12</option>
            <option value="13"' . ($options['maplist_default_edit_map_zoom'] == '13' ? 'selected="selected"' : '') . '>13</option>
            <option value="14"' . ($options['maplist_default_edit_map_zoom'] == '14' ? 'selected="selected"' : '') . '>14</option>
            <option value="15"' . ($options['maplist_default_edit_map_zoom'] == '15' ? 'selected="selected"' : '') . '>15</option>
            <option value="16"' . ($options['maplist_default_edit_map_zoom'] == '16' ? 'selected="selected"' : '') . '>16</option>
            <option value="17"' . ($options['maplist_default_edit_map_zoom'] == '17' ? 'selected="selected"' : '') . '>17</option>
            <option value="18"' . ($options['maplist_default_edit_map_zoom'] == '18' ? 'selected="selected"' : '') . '>18</option>
            <option value="19"' . ($options['maplist_default_edit_map_zoom'] == '19' ? 'selected="selected"' : '') . '>19</option>
        </select>
        </fieldset>      
        </td>
        </tr>        
</tbody></table>';

        //Save button
        echo '<p><input type="submit" class="button-primary" value="' . __('Save Changes','maplistpro'). '" /></p>';
        
        function updateOption($optionName){
            //new window
            if(@$_POST[$optionName]){
                update_option('maplist_' . $optionName, $_POST[$optionName]);
            }
            else{
                update_option('maplist_' . $optionName, '');
            }
        }

        function friendlyCssFileName($unfriendlyName){
            $friendlyName = str_replace('_',' ',$unfriendlyName);
            $friendlyName = str_replace('.css','',$friendlyName);
            return $friendlyName;
        }
        ?>

<div class="prettyMapList" style="max-width:750px">

    <!--The Map -->
    <div class="mapHolder" id="map0" style="position: relative; background-color: rgb(229, 227, 223); overflow: hidden;"><div style="position: absolute; left: 0px; top: 0px; overflow: hidden; width: 100%; height: 100%; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; overflow: hidden; width: 100%; height: 100%; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1; cursor: url(&quot;http://maps.gstatic.com/mapfiles/openhand_8_8.cur&quot;), default;"><div style="z-index: 100; position: absolute; left: 0px; top: 0px;"><div style="position: absolute; left: 0px; top: 0px; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1;"><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: 87px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: 87px;"></div><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: -169px;"></div><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: 343px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: -169px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: 343px;"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: 87px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: 87px;"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: -169px;"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: 343px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: -169px;"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: 343px;"></div></div></div></div><div style="z-index: 101; position: absolute; left: 0px; top: 0px;"></div><div style="z-index: 102; position: absolute; left: 0px; top: 0px;"><div style="position: absolute; left: 0px; top: 0px; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1;"><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: 87px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: 343px;"></div></div></div></div><div style="z-index: 103; position: absolute; left: 0px; top: 0px;"><div style="position: absolute; left: 0px; top: 0px; z-index: -1;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1;"><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -16px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 240px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: 87px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: 87px;"><canvas style="position: absolute; left: 0px; top: 0px;" height="256" width="256"></canvas></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: -272px; top: 343px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: -169px;"></div><div style="width: 256px; height: 256px; overflow: hidden; position: absolute; left: 496px; top: 343px;"></div></div></div></div><div style="z-index: 104; position: absolute; left: 0px; top: 0px;"></div><div style="z-index: 105; position: absolute; left: 0px; top: 0px;"></div><div style="z-index: 106; position: absolute; left: 0px; top: 0px;"><div style="display: none;"><div style="width: 107px; height: 137px; overflow: hidden; position: absolute; left: 21px; top: 26px;"><img style="position: absolute; left: -784px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"><div style="position: absolute; left: 7px; top: 5px; width: 94px; height: 75px; background-color: rgb(211, 211, 211); z-index: 1;"></div><div style="width: 95px; bottom: 38px; z-index: 1; left: 7px; font-family: Arial,sans-serif; font-size: 9px; color: gray; background-color: white; position: absolute;">Loading...</div><img style="position: absolute; left: 7px; top: 5px; width: 94px; height: 75px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; opacity: 0.25;" src="http://maps.gstatic.com/mapfiles/transparent.png"></div><div style="width: 21px; height: 26px; overflow: hidden; position: absolute; left: 63px; top: 127px;"><img style="position: absolute; left: -441px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"></div></div></div><div style="position: absolute; z-index: 0; left: 0px; top: 0px;"><div style="overflow: hidden; width: 642px; height: 352px;"><img style="width: 642px; height: 352px;" src="http://maps.googleapis.com/maps/api/js/StaticMapService.GetMapImage?1m2&amp;1i16&amp;2i169&amp;2e1&amp;3u2&amp;4m2&amp;1u642&amp;2u352&amp;5m3&amp;1e0&amp;2b1&amp;5sen-GB&amp;token=46441"></div></div><div style="position: absolute; left: 0px; top: 0px; z-index: 0;"><div style="position: absolute; left: 0px; top: 0px; z-index: 1;"><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: 87px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=0&amp;y=1&amp;z=2&amp;s=G&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: 87px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=1&amp;y=1&amp;z=2&amp;s=Gali&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: -169px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=0&amp;y=0&amp;z=2&amp;s=&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: -16px; top: 343px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=0&amp;y=2&amp;z=2&amp;s=Ga&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: -169px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=1&amp;y=0&amp;z=2&amp;s=Gal&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 240px; top: 343px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=1&amp;y=2&amp;z=2&amp;s=Galil&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: 87px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=3&amp;y=1&amp;z=2&amp;s=Ga&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: -169px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=3&amp;y=0&amp;z=2&amp;s=G&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: -272px; top: 343px;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt1.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=3&amp;y=2&amp;z=2&amp;s=Gal&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: 87px; opacity: 1; -moz-transition: opacity 200ms ease-out 0s;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=2&amp;y=1&amp;z=2&amp;s=Galileo&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: -169px; opacity: 1; -moz-transition: opacity 200ms ease-out 0s;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=2&amp;y=0&amp;z=2&amp;s=Galile&amp;style=api%7Csmartmaps"></div><div style="width: 256px; height: 256px; position: absolute; left: 496px; top: 343px; opacity: 1; -moz-transition: opacity 200ms ease-out 0s;"><img style="width: 256px; height: 256px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://mt0.googleapis.com/vt?lyrs=m@179000000&amp;src=apiv3&amp;hl=en-GB&amp;x=2&amp;y=2&amp;z=2&amp;s=&amp;style=api%7Csmartmaps"></div></div></div></div></div><div style="margin: 2px 5px 2px 2px; z-index: 1000000; position: absolute; left: 0px; bottom: 0px;"><a style="position: static; overflow: visible; float: none; display: inline;" title="Click to see this area on Google Maps" target="_blank" href="http://maps.google.com/maps?ll=50.436395,-61.516347&amp;z=2&amp;t=m&amp;hl=en-GB"><div style="width: 62px; height: 24px; cursor: pointer;"><img style="position: absolute; left: 0px; top: 0px; width: 62px; height: 24px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://maps.gstatic.com/mapfiles/google_white.png"></div></a></div><div class="gmnoprint" style="z-index: 1000001; position: absolute; right: 0px; bottom: 0px;"><div style="height: 19px; -moz-user-select: none; line-height: 19px; padding-right: 2px; padding-left: 50px; background: -moz-linear-gradient(left center , rgba(255, 255, 255, 0) 0pt, rgba(255, 255, 255, 0.5) 50px) repeat scroll 0% 0% transparent; font-family: Arial,sans-serif; font-size: 10px; color: rgb(68, 68, 68); white-space: nowrap; direction: ltr; text-align: right;"><a style="color: rgb(68, 68, 68); text-decoration: underline; cursor: pointer; display: none;">Map Data</a><span>Map data &copy;2012 MapLink, Tele Atlas</span><span> - </span><a style="color: rgb(68, 68, 68); text-decoration: underline; cursor: pointer;" href="http://www.google.com/intl/en-GB_GB/help/terms_maps.html" target="_blank">Terms of Use</a></div></div><div style="background-color: white; padding: 15px 21px; border: 1px solid rgb(171, 171, 171); font-family: Arial,sans-serif; color: rgb(34, 34, 34); box-shadow: 0pt 4px 16px rgba(0, 0, 0, 0.2); z-index: 10000002; display: none; width: 256px; height: 148px; position: absolute; left: 171px; top: 86px;"><div style="padding: 0pt 0pt 10px; font-size: 16px;">Map Data</div><div style="font-size: 13px;">Map data &copy;2012 MapLink, Tele Atlas</div><div style="width: 10px; height: 10px; overflow: hidden; position: absolute; opacity: 0.7; right: 12px; top: 12px; z-index: 10000; cursor: pointer;"><img style="position: absolute; left: -18px; top: -44px; width: 68px; height: 67px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://maps.gstatic.com/mapfiles/mv/imgs8.png"></div></div><div class="gmnoscreen" style="position: absolute; right: 0px; bottom: 0px;"><div style="font-family: Arial,sans-serif; font-size: 10px; color: rgb(68, 68, 68); direction: ltr; text-align: right; background-color: rgb(245, 245, 245);">Map data &copy;2012 MapLink, Tele Atlas</div></div><div style="display: none; font-size: 10px; height: 17px; background-color: rgb(245, 245, 245); border: 1px solid rgb(220, 220, 220); line-height: 19px; position: absolute; right: 0px; bottom: 0px;" class="gmnoprint"><a target="_new" title="Report errors in the road map or imagery to Google" style="font-family: Arial,sans-serif; font-size: 85%; font-weight: bold; padding: 1px 3px; color: rgb(68, 68, 68); text-decoration: none; position: relative; bottom: 1px;" href="http://maps.google.com/maps?ll=50.436395,-61.516347&amp;z=2&amp;t=m&amp;hl=en-GB&amp;skstate=action:rmi_dialog$apiref:1$location:50.436395,-61.516347">Report a map error</a></div><div class="gmnoprint" style="margin: 5px; -moz-user-select: none; position: absolute; left: 0px; top: 0px;" controlwidth="32" controlheight="84"><div style="cursor: url(&quot;http://maps.gstatic.com/mapfiles/openhand_8_8.cur&quot;), default; position: absolute; left: 0px; top: 0px;" controlwidth="32" controlheight="40"><div style="width: 32px; height: 40px; overflow: hidden; position: absolute; left: 0px; top: 0px;"><img style="position: absolute; left: -9px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"></div><div style="width: 32px; height: 40px; overflow: hidden; position: absolute; left: 0px; top: 0px; visibility: hidden;"><img style="position: absolute; left: -107px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"></div><div style="width: 32px; height: 40px; overflow: hidden; position: absolute; left: 0px; top: 0px; visibility: hidden;"><img style="position: absolute; left: -58px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"></div><div style="width: 32px; height: 40px; overflow: hidden; position: absolute; left: 0px; top: 0px; visibility: hidden;"><img style="position: absolute; left: -205px; top: -102px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 1029px; height: 255px;" src="http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_api_003.png"></div></div><div class="gmnoprint" style="opacity: 0.6; display: none; position: absolute;" controlwidth="0" controlheight="0"><img src="http://maps.gstatic.com/mapfiles/rotate2.png" style="-moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; cursor: pointer; width: 22px; height: 22px;" title="Rotate map 90 degrees"></div><div class="gmnoprint" controlwidth="22" controlheight="39" style="position: absolute; left: 5px; top: 45px;"><img style="width: 22px; height: 39px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http://maps.gstatic.com/mapfiles/szc4.png"><div style="position: absolute; left: 0px; top: 0px; width: 22px; height: 17px; cursor: pointer;" title="Zoom in"></div><div style="position: absolute; left: 0px; top: 18px; width: 22px; height: 17px; cursor: pointer;" title="Zoom out"></div></div></div><div class="gmnoprint" style="margin: 5px; z-index: 0; position: absolute; cursor: pointer; text-align: left; width: 85px; right: 0px; top: 0px;"><div style="direction: ltr; overflow: hidden; text-align: left; position: relative; color: rgb(0, 0, 0); font-family: Arial,sans-serif; -moz-user-select: none; font-size: 13px; background: none repeat scroll 0% 0% rgb(255, 255, 255); padding: 1px 6px; border: 1px solid rgb(113, 123, 135); box-shadow: 0pt 2px 4px rgba(0, 0, 0, 0.4); font-weight: bold;" title="Change map style">Map<img src="http://maps.gstatic.com/mapfiles/arrow-down.png" style="-moz-user-select: none; border: 0px none; padding: 0px; margin: -2px 0px 0px; position: absolute; right: 6px; top: 50%; width: 7px; height: 4px;"></div><div style="background-color: white; z-index: -1; padding-top: 2px; border-width: 0pt 1px 1px; border-style: none solid solid; border-color: -moz-use-text-color rgb(113, 123, 135) rgb(113, 123, 135); -moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none; box-shadow: 0pt 2px 4px rgba(0, 0, 0, 0.4); position: relative; text-align: left; display: none;"><div style="color: black; font-family: Arial,sans-serif; -moz-user-select: none; font-size: 13px; background-color: rgb(255, 255, 255); padding: 2px 5px 3px; font-weight: bold;" title="Show street map">Map</div><div style="color: black; font-family: Arial,sans-serif; -moz-user-select: none; font-size: 13px; background-color: rgb(255, 255, 255); padding: 2px 5px 3px;" title="Show satellite imagery">Satellite</div><div style="color: black; font-family: Arial,sans-serif; -moz-user-select: none; font-size: 13px; background-color: rgb(255, 255, 255); padding: 2px 5px 3px;">Open Street Map</div><div style="margin: 1px 0pt; border-top: 1px solid rgb(235, 235, 235);"></div><div style="color: rgb(184, 184, 184); font-family: Arial,sans-serif; -moz-user-select: none; font-size: 11px; background-color: rgb(255, 255, 255); padding: 1px 8px 3px 5px; direction: ltr; text-align: left; white-space: nowrap; display: none;" title="Zoom in to show 45-degree view"><span role="checkbox" style="position: relative; line-height: 0; font-size: 0pt; margin: 0pt 5px 0pt 0pt; display: inline-block; background-color: rgb(255, 255, 255); border: 1px solid rgb(241, 241, 241); border-radius: 1px 1px 1px 1px; width: 13px; height: 13px; box-shadow: none; vertical-align: middle;"><div style="position: absolute; left: 1px; top: -2px; width: 13px; height: 11px; overflow: hidden; display: none;"><img style="position: absolute; left: -52px; top: -44px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px; width: 68px; height: 67px;" src="http://maps.gstatic.com/mapfiles/mv/imgs8.png"></div></span><label style="vertical-align: middle; cursor: pointer;">45°</label></div></div></div></div></div>

    <!-- Search, Filters, Sorting bar -->
    <div class="prettyFileBar clearfix">
        <a href="#" class="showFilterBtn float_right corePrettyStyle btn">Categories</a><a href="#" data-sort-direction="asc" class="showSortingBtn float_right corePrettyStyle btn sortAsc">Sort</a>        <div class="prettyFileFilters" style="display: none;">
            <ul class="unstyled">
                <li><a href="#" data-category="food" class="food showing">food</a></li><li><a href="#" data-category="supermarket-3" class="supermarket-3 showing">supermarket</a></li><li><a href="#" data-category="uncategorised" class="uncategorised showing">uncategorized</a></li>            </ul>
            <p class="bar">
                <a href="#" class="cross">Close</a>
            </p>
        </div>

        <div class="prettyMapListSearch">
            <label style="display:none;">Search</label>
            <input type="text" value="Search..." class="prettySearchValue">
            <a class="doPrettySearch btn corePrettyStyle">Go</a>
        </div>
   </div>

    <!---Message bar---->
    <div style="display:none" class="prettyMessage"><span></span><a href="#" class="btn">Show all files</a></div>

    <!--The List -->
       <ul class="unstyled prettyListItems">
        <li class="corePrettyStyle prettylink map supermarket">
            <a class="viewLocationDetail" href="#">
                A Supermarket <span class="mapcategories">Categories: <span>supermarket </span></span>
            </a>
        </li>
        <li class="corePrettyStyle prettylink map supermarket">
            <a class="viewLocationDetail" href="#">
                A Supermarket <span class="mapcategories">Categories: <span>supermarket </span></span>
            </a>
        </li>
        <li class="corePrettyStyle prettylink map supermarket">
            <a class="viewLocationDetail" href="#">
                A Supermarket <span class="mapcategories">Categories: <span>supermarket </span></span>
            </a>
        </li>
        <li class="corePrettyStyle prettylink map supermarket">
            <a class="viewLocationDetail" href="#">
                A Supermarket <span class="mapcategories">Categories: <span>supermarket </span></span>
            </a>
        </li>
        </ul>

    <div class="prettyPagination">
        <a href="#" class="pfl_next btn corePrettyStyle">Next »</a>
        <span class="pagingInfo">Page <span class="currentPage">2</span> of <span class="totalPages">3</span></span>
        <a href="#" class="pfl_prev btn corePrettyStyle">« Prev</a>
    </div>
</div>