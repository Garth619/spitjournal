/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

(function($) {
	
    $(document).ready(function(){
        var pluginUrl = maplistScriptParams.pluginUrl;
        
        var iconPicker = $('#IconPicker');
        var iconItems = $('> li',iconPicker);
        
        //Sort icons by position
        iconPicker.append(iconItems.sort(asc_byposition));
        
        //Sort the items into their correct order on load
        ////==============================================
        //Set the position and activeiconid data attribute for each item
        iconItems.each(function(){
            var thisItem = $(this);
            var data = ($('input',thisItem).val()).split(",");
            
            //If no icon or position is set
            //TODO:Pull default icon from settings 
            if(data.length == 1){
                data = [30,'default/mapmarker1.png','default/shadow.png'];
            }
        });               
           
        //Make the list sortable
        iconPicker.sortable({
             items: "> li",
             helper : 'clone',
             axis:'y',
             cancel : 'input,.iconChooser,.mapCategoryIcons',
             stop: function(event,ui){
             
                //Re-get iconitems so it's in correct order
                iconItems = $('> li',iconPicker); 

                //Set the sort levels on all fields            
                iconItems.each(function(index){
                    //Split current val to array
                    var itemDetail = $('input',$(this)).val().split(",");
                    //Set new position
                    itemDetail[0] = index + 1;                    
                    //Set the field value
                     $('input',$(this)).val(itemDetail.join());
                });
            }
        });
         
        //Expand the items on click
        $('label',iconPicker).click(function(){
            $(this).siblings('.iconChooser').toggle(200);
            return false;
        });
        
        $('a',iconPicker).click(function(){
            var clicked = $(this);
            var iconChooser = $(this).parents('.iconChooser');
            var iconChooserParent = iconChooser.parent();
            var shadowIcon = '';
            if(clicked.data('iconshadow') == true){
                shadowIcon = clicked.data('iconfolder') + '/shadow.png';
            }
            else{
                shadowIcon = 'none';
            }
            
            //Set the val with position, img, shadow
            iconChooser.prev('input').val(iconChooserParent.data('position')+ ',' + clicked.data('iconfolder') + '/' +  clicked.data('iconimage') + ',' + shadowIcon);
            var newVal = iconChooserParent.data('position')+ ',' + clicked.data('iconfolder') + '/' +  clicked.data('iconimage') + ',' + shadowIcon;
            //Add shadow overrides if any
            newVal += clicked.data('shadowoverrides') != undefined ? (',' + clicked.data('shadowoverrides')) : '';
            console.log(newVal);
            iconChooser.prev('input').val(newVal);
            //Update the current active icon
            $('.currentIcon',iconChooserParent).css('background-image' , 'url(' + pluginUrl + '/images/pins/' + clicked.data('iconfolder') + '/' +  clicked.data('iconimage') + ')');
            
            return false;
        });
          
    });
	
    function asc_byposition(a, b){
        return ($(b).data('position') < $(a).data('position') ? 1 : -1);
    }

})( jQuery );


