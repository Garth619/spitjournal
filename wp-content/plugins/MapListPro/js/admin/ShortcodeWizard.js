//VARIABLES
var pickerType = "criteria"; //Set initial pick type
var pathToAjax = "";

//FUNCTIONS
//case-insensitive version of :contains
$.extend($.expr[":"], {
    "containsNC": function(elem, i, match, array) {
            return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
    }
});

var ButtonDialog = {
    local_ed : 'ed',
    init : function(ed) {
            //ButtonDialog.local_ed = ed;
            tinyMCEPopup.resizeToInnerSize();
            var catsData = {
                action: 'get_all_maplocations'
            }

            pathToAjax = (ed['buttons']['maplist']['pluginurl']);

            //get location categories
            jQuery.post(pathToAjax, catsData, function(response) {
                $('#AllLocations').html(response).removeClass('loading');
            });
    },
    insert : function insertButton(ed){

        ////Validate the form first
        var isValid = true;

        //Quit out if invalid
        if(isValid === false) {
            return;
        }

        //Get categories or location to display
        var fileList = "";
        $('.file:checked').each(function(){
            if(fileList != ""){
                fileList += ',';
            }
            fileList += $(this).val();
        });

        var filesPerPage = jQuery('#FilesPerPage').val();

        //Get output var ready
        var output = '';

        //Build the output of our shortcode
        output = '[maplist ';

        //If criteria based list
        if(pickerType == "criteria"){
            //Add categories
            output += fileList !== "" ? 'categories="' + fileList + '" ' : '';
        }
        else{
            //Add file list
            output += fileList !== "" ? ' locationstoshow="' + fileList + '" ' : '';
        }

        //Show directions
        output += jQuery('#hidedirections').is(":checked") ? 'showdirections="false" ' : '';

        //geoenabled
        output += jQuery('#geoenabled').is(":checked") ? 'geoenabled="true" ' : '';

        //Add sorting
        output += jQuery('#DirectionToSortBy').val() != "ASC" ? 'orderdir="' + jQuery('#DirectionToSortBy').val() + '" ' : "";

        //misc options
        output += jQuery('#hidecategoriesonitems').is(":checked") ? 'hidecategoriesonitems="true" ' : '';
        output += jQuery('#hideviewdetailbuttons').is(":checked") ? 'hideviewdetailbuttons="true" ' : '';
        output += jQuery('#openinnew').is(":checked") ? 'openinnew="true" ' : '';
        output += jQuery('#keepzoomlevel').is(":checked") ? 'keepzoomlevel="true" ' : '';

        //Get lat long and zoom if needed
        if(!$('#autozoom').is(':checked')){
            //Start lat long
            output += 'startlatlong="' + jQuery('#startlat').val() + ',' + jQuery('#startlong').val() + '" ';

            //Zoom levels
            output += jQuery('#defaultzoom').val() != 'Automatic' ? 'defaultzoom="' + jQuery('#defaultzoom').val() + '" ' : "";
        }


        if(jQuery('#selectedzoomlevel').val() != 'nozoom'){
            output += 'selectedzoomlevel="' + jQuery('#selectedzoomlevel').val() + '" ';
        }

        //Search
        var searchType = jQuery('#searchtype').val();
        if(searchType == 'none'){
            //No search
            output += 'hidesearch="true" ';
        }
        else {
            if(searchType == 'simple'){
                //text only
                output += 'simplesearch="true" ';
            }
            else{
                if(searchType == 'combo'){
                    output += 'simplesearch="combo" ';
                    output += jQuery('#country').val() ? 'country="' + jQuery('#country').val() + '" ' : '';                    
                }
                else{
                    //location based
                    output += 'simplesearch="false" ';
                    output += jQuery('#country').val() ? 'country="' + jQuery('#country').val() + '" ' : '';
                }
            }
        }

        //Hide buttons
        output += jQuery('#hidefilter').is(":checked") ? 'hidefilter="true" ' : "";
        output += jQuery('#hidesort').is(":checked") ? 'hidesort="true" ' : "";

        //list style - listonly,maponly,both
        if( jQuery('.selected','#ListType').data('listtype') !== 'both') {
            output += 'viewstyle="' + jQuery('.selected','#ListType').data('listtype') + '" ';
        }

        //map position
        if(jQuery('.selected','#ListType').data('listtype') === 'both' && jQuery('.selected','#MapType').data('maptype') !== 'above'){
            output += 'mapposition="' + jQuery('.selected','#MapType').data('maptype') + '" ';            
        }

        if(jQuery('.selected','#ListType').data('listtype') !== 'listonly'){
            if(jQuery('.selected','#MarkerClustering').data('markerclustering')){
                output += 'clustermarkers="' + jQuery('.selected','#MarkerClustering').data('markerclustering') + '" ';    
            }            
        }

        //Open in new window?
        output += jQuery('#openinnew').is(":checked") ? 'openinnew="true" ' : "";

        output += 'locationsperpage="' + filesPerPage + '"]';

        // inserts the shortcode into the active editor
        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, output);

        // closes Thickbox
        tinyMCEPopup.close();
    }
};

tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);

$(document).ready(function(){
    //Date picker
    $('.datepicker').datepicker();
    //Search error
    var noneFoundMessage = $('#noneFoundMessage');
    //File Container
    var fileContainer = $('#CategoryList');
    //Clear search
    var clearSearch = $('#ClearSearch');
    var allFiles = "";

    //Hide all others, and uncheck
    var searchButton = $('#DoSearch');
    var searchBox = $('#SearchFiles');

    //Fire search on enter key
    searchBox.keydown(function (e){
        if(e.keyCode === 13){
            searchButton.click();
        }
    });

    //Show search country on tick
    $('#searchtype').change(function(){
        if($(this).val() == 'locationbased' || $(this).val() == 'combo'){
            $('#CountryContainer').fadeIn(400);
        }
        else{
            $('#CountryContainer').fadeOut(400);
        }
    });

    //Show/hide manual map problems
    $('#autozoom').click(function(){
        if($(this).is(":checked")){
            $('#manualzoomsettings').fadeOut(400);
            $('#LatLongError').addClass('hidden');

        }
        else{
            $('#manualzoomsettings').fadeIn(400);
        }
    });

    searchButton.click(function(){
        //Check for a search term
        if(searchBox.val() !== ""){
            searchBox.removeClass("error");
            allFiles = $('li',fileContainer);
            filteredFiles = allFiles.filter(':containsNC(' + searchBox.val() + ')');
            if(filteredFiles.length > 0){
                allFiles.hide();
                filteredFiles.show();
                noneFoundMessage.addClass('hidden');
            }
            else{
                allFiles.hide();
                noneFoundMessage.removeClass('hidden');
            }

            clearSearch.removeClass('hidden');
        }
        else{
            searchBox.addClass("error");
        }
    });

    clearSearch.click(function(){
        //Hide button
        $(this).addClass('hidden');
        //Clear value
        searchBox.val('');
        //Show all files
        allFiles.show();
        //Clear error
        noneFoundMessage.addClass('hidden')
    });


   //Map content type choice
   $('a','#MapTypeChoice').click(function(e){
        if($(this).hasClass('pickFiles')){
            $('#PickLocations').removeClass('hidden');

            pickerType =  "pickfiles";

            var data = {
                action: 'get_all_maplocations'
            };
            //get attached files
            $.post(pathToAjax, data, function(response) {
                //Remove loading, add all files
                $('#AllLocations').removeClass('loading').html(response);
            });
        }
        else{
            $('#GenerateList').removeClass('hidden');

            pickerType =  "criteria";

            var data = {
                action: 'get_all_mapcategories'
            };
            //get attached files
            $.post(pathToAjax, data, function(response) {
                //Remove loading, add all files
                $('#CategoryList').removeClass('loading').html(response);
            });
        }
       scroller.animate({left: '-=600'},200);
       return false;
   });

    //Map list type
    $('a','#ListType').click(function(e){
        $('a','#ListType').removeClass('selected');
        $(this).addClass('selected');

        //Disable map position choice if just map or list
        if(!$(this).hasClass('both')){
            $('#MapPosition').addClass('disabled');
        }
        else{
            $('#MapPosition').removeClass('disabled');
        }
        
        //Clustering needed on all map types
        if($(this).hasClass('listonly')){
            $('#MarkerClustering').addClass('disabled');
        }
        else{
            $('#MarkerClustering').removeClass('disabled');
        }

        return false;
    });

    $('a','#MapType').click(function(){
        //If list only no need for this
        if($('.selected','#ListType').data('listtype') === 'listonly'){return false;}

        $('a','#MapType').removeClass('selected');
        $(this).addClass('selected');
        return false;
    });

    $('a','#MarkerClustering').click(function(){
        //If list only no need for this
        if($('.selected','#ListType').data('listtype') === 'listonly'){return false;}

        $('a','#MarkerClustering').removeClass('selected');
        $(this).addClass('selected');
        return false;
    });

        //Get scroller
        var scroller = $('#Scroller','#ScrollContainer');

       //Next button
       $('.next').click(function(e){
           var clicked = $(this);

            isValid = true;

            //Check that a location has been picked
            if(pickerType == "pickfiles"){
                //Check to see if a file has been picked
                if($('.file:checked').length == 0){
                    //Error
                    $('#FileError').removeClass('hidden');
                    isValid = false;
                }
                else{
                    //Is valid
                    $('#FileError').addClass('hidden');
                    isValid = true;
                }
            }


            //Check lat long and zoom set
            if(clicked.hasClass('goToFinal') && !$('#autozoom').is(':checked')){
                if($('#startlat').val() && $('#startlong').val() && $('#defaultzoom').val()){
                    //Is valid
                    $('#LatLongError').addClass('hidden');
                    isValid = true;
                }
                else{
                    //Not valid
                    $('#LatLongError').removeClass('hidden');
                    isValid = false;
                }
            }

            //Scroll if all ok
            if(isValid == true){
                scroller.animate({left: '-=600'},200);
            }

            return false;

       });

       //Back button
       $('.back').click(function(){
           //Hide both panels on back to start
           if($(this).hasClass('goToFirst')){
                $('#PickLocations,#GenerateList').addClass('hidden');
           }

           scroller.animate({left: '+=600'},200);
           return false;
       });
    }
);