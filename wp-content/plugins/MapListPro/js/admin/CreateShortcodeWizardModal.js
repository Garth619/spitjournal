(function() {  
    console.log(ajaxurl);
    tinymce.create('tinymce.plugins.maplist', {  
        init : function(ed, url) {  
          
            ed.addButton('maplist', {
                title : 'Add a Map Location or list',  
                image : url.replace('/js/admin','')+'/images/maplist.png',  
		cmd : 'maplistpro',
                pluginurl : ajaxurl
            });  
			
            // Register commands
            ed.addCommand('maplistpro', function() {
                    ed.windowManager.open({
                            file : url.replace('/js/admin','')+'/includes/admin/shortcode_wizard.php', // file that contains HTML for our modal window
                            //file : ajaxurl.replace('admin-ajax.php','') + 'edit.php?post_type=maplist&page=createmapshortcode', // TODO: Make this a wordpress page
                            width : 600 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
                            height : 500 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
                            inline : 1
                    }, {
                            plugin_url : url
                    });
            });
        }
        
    });  
    tinymce.PluginManager.add('maplist', tinymce.plugins.maplist);  
}
)();

jQuery.urlParam = function(url,name){
    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(url);
    if (results == null){return ''}
    return results[1] || 0;
} 