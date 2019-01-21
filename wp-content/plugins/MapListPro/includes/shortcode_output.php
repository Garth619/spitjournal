<?php
    //Get all options
    extract($options);
    $measurementUnits = (get_option('maplist_measurmentunits') == 'METRIC' ? 'Kms' : 'Miles');
    $categorylabel = (get_option('maplist_category_name') == '' ? __('Categories','maplistpro') : get_option('maplist_category_name'));

    //html to return to page
    $mlpoutputhtml = '';
    $numberOfLocations = $this->numberOfLocations;
    $countertemp = self::$counter;

    $mlpoutputhtml .= "<div class='prettyMapList $mapposition' id='MapListPro$countertemp'>";


    if($viewstyle == 'both' || $viewstyle == 'maponly'){

        $mlpoutputhtml .= "<!--The Map -->";
        $mlpoutputhtml .= "<div id='map-canvas$countertemp' class='mapHolder'></div>";

        $mlpoutputhtml .= "<!-- hidden div that gets bound -->";
        $mlpoutputhtml .= '<div data-bind="map: $data.filteredLocations()"></div>';
    }

    if($viewstyle == 'both' || $viewstyle == 'listonly'){


    $mlpoutputhtml .= "<div id='ListContainer'>";
    $mlpoutputhtml .= "<!-- Search, Filters, Sorting bar -->";
    $mlpoutputhtml .= "<div class='prettyFileBar clearfix'>";
        $mlpoutputhtml .= "<!-- Category button -->";

        if($hidefilter != 'true'){
            $mlpoutputhtml .= "<a class='showFilterBtn float_right corePrettyStyle btn' href='#' data-bind='click:showCategories'>" . $categorylabel. "</a>";
        }

        $mlpoutputhtml .= "<!-- Sorting button -->";

        if($hidesort != 'true'){
            if($geoenabled == 'true' || $simplesearch != 'true'){

                $mlpoutputhtml .= "<a class='showSortingBtn float_right corePrettyStyle sortAsc btn' href='#'>" . __('Sort','maplistpro'). "</a>";
                
                    $mlpoutputhtml .= "<div class='prettyFileSorting dropDownList'>";
                        $mlpoutputhtml .= "<ul class='unstyled'>";
                            $mlpoutputhtml .= "<li><a href='#' data-sorttype='title' data-bind='" . 'click:$root.sortList' . "'>" . __('Title','maplistpro'). "</a></li>";
                            $mlpoutputhtml .= "<li><a href='#' data-sorttype='distance' data-bind='" . 'click:$root.sortList' . "'>" . __('Distance','maplistpro'). "</a></li>";
                        $mlpoutputhtml .= "</ul>";                           
                    $mlpoutputhtml .= "</div>";
            }
            else{
                $mlpoutputhtml .= "<a data-sorttype='title' class='showSortingBtn float_right corePrettyStyle sortAsc btn' href='#' data-bind='click:sortList'>" . __('Sort','maplistpro'). "</a>";
            }
        }

        if(!$hidefilter != 'true'){

            $mlpoutputhtml .= "<div class='prettyFileFilters dropDownList'>";
                $mlpoutputhtml .= "<ul class='unstyled' data-bind='foreach: {data: mapCategories}'>";
                    $mlpoutputhtml .= "<li>";
                        $mlpoutputhtml .= "<a data-bind='css: {" . '"showing"' . ": selected}, text: " . '$data.title, click: $parent.selectCategory' . "' href='#'></a>";
                    $mlpoutputhtml .= "</li>";
                $mlpoutputhtml .= "</ul>";
            $mlpoutputhtml .= "</div>";

        }

        if($hidesearch != 'true'){

            $mlpoutputhtml .= "<div class='prettyMapListSearch $simplesearch'>";

                if($simplesearch == 'true'){
                    //TEXT SEARCH
                    $mlpoutputhtml .= "<label>" . __('Search locations','maplistpro'). "</label>";
                    $mlpoutputhtml .= "<input type='text' class='prettySearchValue' data-bind='value: query, valueUpdate:" . '"keyup"' . "' autocomplete='off' value='$this->searchTextDefault'>";
                }
                else{
                    if($simplesearch == 'combo'){
                        //COMBO SEARCH
                        $mlpoutputhtml .= "<input type='text' class='prettySearchValue' autocomplete='off' placeholder='$this->searchTextDefault' value=''>";
                        $mlpoutputhtml .= "<select class='distanceSelector' name='distanceSelector' id='distanceSelector' data-bind='options: distanceFilters, optionsText:function(item){return item.label}, optionsValue: function(item){return item.value}, value: chosenFromDistance'></select>";
                        $mlpoutputhtml .= "<input type='text' class='prettySearchLocationValue' autocomplete='off' placeholder='$this->searchLocationTextDefault' value=''>";
                        $mlpoutputhtml .= "<a class='doPrettySearch btn corePrettyStyle' data-bind='click:comboSearch'>" . __('Go','maplistpro'). "</a>";
                    }
                    else{
                        //LOCATION SEARCH
                        //TODO:Add default value in
                        $mlpoutputhtml .= "<label class='hidden'>" . __('Find locations near','maplistpro'). "</label>";
                        $mlpoutputhtml .= "<input type='text' class='prettySearchValue' autocomplete='off' placeholder='$this->searchLocationTextDefault' value=''>";
                        $mlpoutputhtml .= "<select class='distanceSelector' name='distanceSelector' id='distanceSelector' data-bind='options: distanceFilters, optionsText:function(item){return item.label}, optionsValue: function(item){return item.value}, value: chosenFromDistance'></select>";
                        $mlpoutputhtml .= "<a class='doPrettySearch btn corePrettyStyle' data-bind='click:locationSearch'>" . __('Go','maplistpro'). "</a>";
                    }

                }
                $mlpoutputhtml .= "<a class='clearSearch btn corePrettyStyle' data-bind='visible: showClearButton, click: clearSearch'>" . __('Clear','maplistpro'). "</a>";
            $mlpoutputhtml .= "</div>";
        }
        $mlpoutputhtml .= "</div>";

    if($numberOfLocations == 0){
        $mlpoutputhtml .= "<p class='prettyMessage'>" . __('No locations found.','maplistpro') . "</p>";
    }
    else{
    $mlpoutputhtml .= "<!--Message bar-->";
    $mlpoutputhtml .= "<div class='prettyMessage' data-bind='visible: anyLocationsAvailable' style='display:none;'><span>" . __('No matching locations','maplistpro'). " </span><a class='btn' href='#' data-bind='click:clearSearch'>" . __('Show all locations','maplistpro'). "</a></div>";

    $mlpoutputhtml .= "<!--The List -->";
    $mlpoutputhtml .= "<ul class='unstyled prettyListItems loading' data-bind='foreach: {data: pagedLocations}'>";
        $mlpoutputhtml .= "<li class='corePrettyStyle prettylink map location' data-bind='css: " . '$data.cssClass' . ",click: " . '$root.locationClick' . "'>";
            $mlpoutputhtml .= "<a href='#' class='viewLocationDetail clearfix'>";
                $mlpoutputhtml .= "<!-- ko if: " . '$data.smallImageUrl' . " -->";
                    $mlpoutputhtml .= "<img src='#' data-bind='attr:{src: " . '$data.smallImageUrl' . "}' class='smallImage' />";
                $mlpoutputhtml .= "<!-- /ko -->";
                $mlpoutputhtml .= "<span data-bind='html:" . '$data.title' . "'></span>";
                $mlpoutputhtml .= "<span data-bind='text:" . '$data.friendlyDistance' . "'></span>";

                if($hidecategoriesonitems != "true"){
                    $mlpoutputhtml .= "<span class='mapcategories'>" . __('Categories','maplistpro'). ": 
                    <!-- ko foreach: " . '$data.categories' . " -->
                        <span data-bind='{text: " . '$data.title' . "}'></span>" .
                        "<span data-bind='{if: " . '$index() != ($parent.categories.length - 1)' . "}'>,</span>" .
                    "<!-- /ko --></span>";
                }

            $mlpoutputhtml .= "</a>";
            $mlpoutputhtml .= "<!--Expanded item-->";
            $mlpoutputhtml .= "<div class='mapLocationDetail clearfix' style='display:none;' data-bind='slideIn: " . '$data.expanded' . ",slideOut: " . '$data.expanded' . "'>";
                $mlpoutputhtml .= "<div class='mapDescription clearfix'>";
                    $mlpoutputhtml .= "<!-- ko if: " . '$data.imageUrl' . " -->";
                        $mlpoutputhtml .= "<img src='#' data-bind='attr:{src: " . '$data.imageUrl' . "}' class='featuredImage float_left' />";
                    $mlpoutputhtml .= "<!-- /ko -->";
                    $mlpoutputhtml .= "<div class='description float_left' data-bind='{html:" . '$data.description' . "}'></div>";
                $mlpoutputhtml .= "</div>";
                
                if($hideviewdetailbuttons != "true"){
                    $mlpoutputhtml .= "<!-- ko if: " . '$data.locationUrl' . "-->";
                        $mlpoutputhtml .= "<a href='#' class='viewLocationPage btn corePrettyStyle' data-bind='attr:{href:" . '$data.locationUrl' . "}'" . ($openinnew == false ? "" : "target='_blank'") . ">" . __('View location detail','maplistpro'). "</a>";
                    $mlpoutputhtml .= "<!-- /ko -->";
                }
                
                if($showdirections == 'true'){
                    $mlpoutputhtml .= "<!-- Directions -->";
                    $mlpoutputhtml .= "<div class='getDirections'>" . __('Get directions from','maplistpro'). " <input class='directionsPostcode' type='text' value='' size='10'/>";
                        $mlpoutputhtml .= "<a href='#' class='getdirections btn corePrettyStyle' data-bind='click:" . '$root.getDirectionsClick' . "'>" . __('Go','maplistpro'). "</a>";
                        $mlpoutputhtml .= "<a href='#' class='getdirectionsgeo btn corePrettyStyle' data-bind='click:" . '$root.getDirectionsClick' . "'>" . __('Geo locate me','maplistpro'). "</a>";
                        $mlpoutputhtml .= "<div class='mapLocationDirectionsHolder'></div>";
                    $mlpoutputhtml .= "</div>";
                }
            $mlpoutputhtml .= "</div>";
           $mlpoutputhtml .= "</li>";
    $mlpoutputhtml .= "</ul>";

        //If less than a page of results
        if($numberOfLocations > $locationsperpage){
            $mlpoutputhtml .= "<div class='prettyPagination'>";
                $mlpoutputhtml .= "<a class='pfl_next btn corePrettyStyle' href='#' data-bind='click: nextPage,css:nextPageButtonCSS'>" . __('Next','maplistpro'). " &raquo;</a>";
                $mlpoutputhtml .= '<div data-bind="visible: pagingVisible" class="pagingInfo">';
                    $mlpoutputhtml .= __('Page','maplistpro'). " <span class='currentPage' data-bind='text: currentPageNumber'></span> " . __('of','maplistpro') . " <span data-bind='text: totalPages' class='totalPages'></span>";
                $mlpoutputhtml .= "</div>";
                $mlpoutputhtml .= "<a class='pfl_prev btn corePrettyStyle' data-bind='click: prevPage,css:prevPageButtonCSS' href='#'>&laquo; " . __('Prev','maplistpro'). "</a>";
            $mlpoutputhtml .= "</div>";
        }
        }//If numlocations = 0
        $mlpoutputhtml .= "</div>";//ListContainer
    }


$mlpoutputhtml .= "</div>"; //prettyMapList

$this->mlpoutputhtml = $mlpoutputhtml;
self::$counter++;