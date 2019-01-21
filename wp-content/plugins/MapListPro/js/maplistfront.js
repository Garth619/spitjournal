jQuery(document).ready(function($){    
    
    /*ViewModel*/
    function MapViewModel(mapObject,mapid) {

        var self = this;
        self.mapID = mapid;
        self.MapHolder = $('#MapListPro' + self.mapID);
        self.homelocation = '';
        self.geocodedLocation = ko.observable();
        
        //Sorting
        self.sortDirection = ko.observable(mapObject.options.orderdir);
        self.selectedSortType = ko.observable(mapObject.options.initialsorttype);

        //Used for unselected markers
        self.useCategoryFilter =  ko.observable(false);

        //DATA GET AND OBJECT CREATION
        //==============================================
        
        //Locations
        //=====================
        function Location(title,cssClass, categories, latitude, longitude, pinColor, pinImageUrl,pinShadowImageUrl,pinShadowOverrides, imageUrl, smallImageUrl, address, _mapMarker, description, locationUrl) {
            this.title = title;
            this.cssClass = cssClass;
            this.categories = categories;
            this.latitude = latitude;
            this.longitude = longitude;
            this.pinColor = 'blue';//GetPinColour;
            this.pinImageUrl = pinImageUrl;
            this.pinShadowImageUrl = pinShadowImageUrl;
            this.pinShadowOverrides = pinShadowOverrides;
            this.imageUrl = imageUrl;
            this.smallImageUrl = smallImageUrl;
            this.address = address;
            this._mapMarker = '';
            this.locationUrl = locationUrl;
            this.description = description;
            this.expanded = ko.observable(false);
            this.distanceAway = ko.observable();
            this.searchDistanceAway = ko.observable();
            this.friendlyDistance = ko.observable();
        }

        //Map the locations to KO objects
        self.mapLocations = ko.utils.arrayMap(mapObject.locations, function (item) {
            return new Location(item.title,item.cssClass, item.categories, item.latitude, item.longitude, item.pinColor, item.pinImageUrl,item.pinShadowImageUrl,item.pinShadowOverrides, item.imageUrl, item.smallImageUrl, item.address, item._mapMarker, item.description, item.locationUrl);
        });

        //Home location
        //========================
        if(mapObject.homelocation != ''){
            self.homelocation = mapObject.homelocation;
        }

        //Categories
        //==============================

        function Category(title, slug) {
            this.title = title;
            this.slug = slug;
            this.selected = ko.observable(false);
        }

        //Category click
        self.selectCategory = function(category) {
            category.selected(!category.selected());
            self.useCategoryFilter (true);
        };

        //Map the categories to KO object
        self.mapCategories = ko.utils.arrayMap(mapObject.categories, function (item) {
            return new Category(item.title, item.slug);
        });

        //Options
        //======================
        self.maximumResults = null;
        self.geoenabled = mapObject.options.geoenabled;
        self.geoHomePosition;
        self.userLocation = new Location();
        //The markers container
        self.markers = [];
        //Ths shadows for the markers
        self.shadows = [];
        
        //If home mode make it centre
        if(self.homelocation){
            var tempCentre = new google.maps.LatLng(self.homelocation.latitude, self.homelocation.longitude) ;             
        }
        else{
            var tempCentre = new google.maps.LatLng(51.62921, -0.7545)
        }


        self.mapOptions = {
            zoom: 16,
            center: tempCentre,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            panControl: false,
            zoomControl: true,
            mapTypeControl: true,
            //scrollwheel: false,
            scaleControl: true,
            streetViewControl: true,
            overviewMapControl: false//,
            //ADD STYLES HERE
            //styles :[ { "elementType": "geometry", "stylers": [ { "hue": "#22ff00" }, { "weight": 1.3 } ]} ]
        };

        //Geolocate
        //=========================
        //See if geo is neededmap
        if (self.geoenabled == 'true' && navigator.geolocation)
        {
            //Check to see available (from another map)
            if(self.geoHomePosition == null){
                navigator.geolocation.getCurrentPosition(getGeo,getGeoError);
            }
            else{
                getGeo(self.geoHomePosition);
            }
        }
        else{
            //If geo enabled but no navigator (<ie8)
            if(self.geoenabled == true){
                getGeoError();
            }
        }

        function getGeo(position)
        {
            //Set the global var for this first time through
            if(self.geoHomePosition == null){
                self.geoHomePosition = {latitude : position.coords.latitude, longitude : position.coords.longitude};
            }

            //If geo is for directions
            if(self.awaitingGeoDirections != null){
                self.showDirections(self.geoHomePosition.latitude + ',' + self.geoHomePosition.longitude,self.awaitingGeoDirectionsLatLng,self.awaitingGeoDirections);
                self.awaitingGeoDirections = null;
                self.awaitingGeoDirectionsLatLng = null;
            }
            else{
                ko.utils.arrayForEach(self.mapLocations, function(location) {
                    var distanceAway = calculateDistance(location.latitude,location.longitude,position.coords.latitude,position.coords.longitude);
                    location.distanceAway(distanceAway);
                });

                //Set sort to distance                
                self.selectedSortType('distance');
                self.sortList('distance')
            }

            showGeoMarker(position.coords.latitude,position.coords.longitude); 
        }        

        //Fallback geolocate uses ip location
        function getGeoError(position)
        {
            $.getJSON("http://www.geoplugin.net/json.gp?jsoncallback=?",
                function (data) {

                    //Set the global var for this first time through
                    if(self.geoHomePosition == null) {
                        self.geoHomePosition = { latitude: data['geoplugin_latitude'], longitude: data['geoplugin_longitude'] };
                    }

                    //If geo is for directions
                    if(self.awaitingGeoDirections != null){
                        self.showDirections(self.geoHomePosition.latitude + ',' + self.geoHomePosition.longitude,self.awaitingGeoDirectionsLatLng,self.awaitingGeoDirections);
                        self.awaitingGeoDirections = null;
                        self.awaitingGeoDirectionsLatLng = null;
                    }
                    else{
                        ko.utils.arrayForEach(self.mapLocations, function(location) {
                            var distanceAway = calculateDistance(location.latitude,location.longitude,data['geoplugin_latitude'],data['geoplugin_longitude']);

                            location.distanceAway(distanceAway);
                        });

                        //Set sort to distance                
                        self.selectedSortType('distance');
                        self.sortList('distance')
                    }

                    showGeoMarker(self.geoHomePosition.latitude,self.geoHomePosition.longitude);
                }
            )
        }

        function showGeoMarker(lat,lng){
                        //Create marker for geo
            if(!self.userLocation._mapMarker){

                var position = new google.maps.LatLng(lat, lng);
                var mapToUse = self.map;

                //Create marker
                //-----------------------------
                var marker = new google.maps.Marker({
                        map: mapToUse,
                        position: position,
                        content: '',
                        optimized: false,
                        animation: google.maps.Animation.DROP,
                });

                    //Set the marker
                    //-----------------------------u
                    self.userLocation._mapMarker = marker;
                }

                //Show this location
                self.userLocation._mapMarker.setAnimation(google.maps.Animation.DROP);
                self.userLocation._mapMarker.setVisible(true);  
        }


        if (mapObject.options.viewstyle != 'listonly') {
            //The map
            self.map = new google.maps.Map($('#map-canvas' + self.mapID, self.MapHolder)[0], self.mapOptions);

            //add Open Street Map
            self.map.mapTypes.set("OSM", new google.maps.ImageMapType({
                getTileUrl: function (coord, zoom) {
                        return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                },
                tileSize: new google.maps.Size(256, 256),
                name: "Open Street Map",
                maxZoom: 18
            }));

            //Default map view
            var mapTypeIdToUse;
            
            switch (mapObject.options.initialmaptype.toLowerCase()) {
                case "hybrid":
                    mapTypeIdToUse = google.maps.MapTypeId.HYBRID;
                    break;
                case "roadmap":
                    mapTypeIdToUse = google.maps.MapTypeId.ROADMAP;
                    break;
                case "satellite":
                    mapTypeIdToUse = google.maps.MapTypeId.SATELLITE;
                    break;
                case "osm":
                    mapTypeIdToUse = 'OSM';
                    break;
                default:
                    mapTypeIdToUse = google.maps.MapTypeId.HYBRID;
                    break;
            }

            self.map.setOptions({
                mapTypeControlOptions: {
                    mapTypeIds: [
                                google.maps.MapTypeId.ROADMAP,
                                google.maps.MapTypeId.SATELLITE,
                                'OSM'
                            ],
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                },
                mapTypeId : mapTypeIdToUse
            });

            //Create clusterer
            if (mapObject.options.clustermarkers == 'true') {
                var mcOptions = { gridSize: mapObject.options.clustergridsize, maxZoom: mapObject.options.clustermaxzoomlevel };
                self.markerClusterer = new MarkerClusterer(self.map,[],mcOptions);
            }

            //If home location set create the marker for it
            if(self.homelocation){
                if(!self.homelocation._mapMarker){

                    var image = new google.maps.MarkerImage(
                        self.homelocation.pinImageUrl
                    );

                    if(self.homelocation.pinShadowOverrides == null){
                        var shadow = new google.maps.MarkerImage(                        
                            self.homelocation.pinShadowImageUrl,
                            new google.maps.Size(50,50),
                            new google.maps.Point(0,0),
                            new google.maps.Point(18,31)//This sets the shadow position
                    );
                    }
                    else{
                        var shadow = new google.maps.MarkerImage(                        
                            self.homelocation.pinShadowImageUrl,
                            new google.maps.Size(parseInt(self.homelocation.pinShadowOverrides[0]),parseInt(self.homelocation.pinShadowOverrides[1])),
                            new google.maps.Point(0,0),
                            new google.maps.Point(parseInt(self.homelocation.pinShadowOverrides[2]),parseInt(self.homelocation.pinShadowOverrides[3]))//This sets the shadow position
                        );
                    }
                        

                    var position = new google.maps.LatLng(self.homelocation.latitude, self.homelocation.longitude);
                    var mapToUse = self.map;

                    //Create marker
                    //-----------------------------
                    var marker = new google.maps.Marker({
                            map: mapToUse,
                            position: position,
                            title: self.homelocation.title,
                            content: '',
                            icon: image,
                            optimized: false,
                            animation: google.maps.Animation.DROP,
                            shadow: shadow
                    });

                        //Set the marker
                        //-----------------------------u
                        self.homelocation._mapMarker = marker;
                    }

                    //Show this location
                    self.homelocation._mapMarker.setAnimation(google.maps.Animation.DROP);
                    self.homelocation._mapMarker.setVisible(true);
            }

            //Fit it all into view
            //self.bounds;

            if(maplistScriptParamsKo.disableInfoBoxes == 'true'){
                self.infowindow = new google.maps.InfoWindow({pixelOffset: new google.maps.Size(-13, 33)});
            }
            else{
                //Infobox
                self.infoBoxOptions = {
                    content: "",
                    //boxStyle : {
                      //    width : "500px"
                     //},
                    disableAutoPan: false,
                    alignBottom: true,
                    pixelOffset: new google.maps.Size(-203, -40),
                    zIndex: 1500,
                    boxClass:"infoWindowContainer",
                    closeBoxMargin: "10px 2px 2px 2px",
                    closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
                    infoBoxClearance: new google.maps.Size(40, 40),
                    isHidden: false,
                    pane: "floatPane",
                    enableEventPropagation: false
                };                
                
                self.infowindow = new InfoBox(self.infoBoxOptions);
            }

            //Close the infowindow
            google.maps.event.addListener(self.infowindow, 'closeclick', function () {
                if(mapObject.options.keepzoomlevel != 'true'){self.resetMapZoom();}
            });
        }


       //Show categories list
        self.showCategories = function(data, element) {
            var thisButton = $(element.currentTarget);
            thisButton.siblings('.prettyFileFilters ').slideToggle(200);
        };

        //Reset all
        //=====================
        self.resetAll = function() {
            //clear search
            self.query('');
            self.locationquery('');

            //Reset bounds
            self.bounds = '';

            //de-select all categories
            ko.utils.arrayForEach(self.mapCategories, function(item) {
                item.selected(false);
            });

            //Ignore categories again
            self.useCategoryFilter(false);
        };

        //Search
        //======================================

        //Search distance
        var Distance = function(value) {
            if (maplistScriptParamsKo.measurementUnits == 'METRIC') {
                this.label = value + ' ' + maplistScriptParamsKo.measurementUnitsMetricText;
            }
            else{
                this.label = value + ' ' + maplistScriptParamsKo.measurementUnitsImperialText;
            }

            //If combo search
            if (mapObject.options.simplesearch === 'combo') {
                this.label = maplistScriptParamsKo.distanceWithinText + ' ' + this.label + ' ' + maplistScriptParamsKo.distanceOfText;
            }

            this.value = value;
        };

        //The chosen distance
        self.chosenFromDistance = ko.observableArray();

        //TODO:Add this to admin
        //DISTANCE DROP DOWN MENU ITEMS
        self.distanceFilters = [
            new Distance(10),
            new Distance(15),
            new Distance(25),
            new Distance(30),
            new Distance(35)
        ];

        self.query = ko.observable('');
        self.locationquery = ko.observable('');

        //see if search param passed to page
        var terms = getParameterByName('locationSearchTerms');

        //TODO:Update to handle combo search
        //If search terms found
        if(terms != ''){
            //Query object for search
            if (mapObject.options.simplesearch == 'true') {
                self.query(terms);
            }
            else{
                var geocoder = new google.maps.Geocoder();
                var address = terms;

                //Add default country if set
                if (mapObject.options.country != '') {
                    address = address + ', ' + mapObject.options.country;
                }

                if (geocoder) {
                    geocoder.geocode({ 'address': address }, function (results, status) {
                       if (status == google.maps.GeocoderStatus.OK) {
                            //We got an address back so set this
                            self.geocodedLocation({lat:results[0].geometry.location.lat(),lng:results[0].geometry.location.lng()});
                            //Set the query string for checking
                            self.locationquery(address);
                            self.sortDirection('dec');
                            self.selectedSortType('distance');
                            self.sortList('distance');                           
                       }
                       else {
                           //Geocode fail
                           //TODO:Add front end message here for failure?
                          console.log("Geocoding failed: " + status);
                       }
                    });
                 }
            }
        }

        self.unitSystemFriendly = maplistScriptParamsKo.measurementUnits == "METRIC" ? 'Kms' : 'Miles';

        //Update location search
        self.locationSearch = function(data,element){

            var locationTerms = ($(element.currentTarget).siblings('.prettySearchValue')).val();
            var geocoder = new google.maps.Geocoder();
            var address = locationTerms;

            //Add default country if set
            if (mapObject.options.country != '') {
                address = address + ', ' + mapObject.options.country;
            }

            if (geocoder) {
                geocoder.geocode({ 'address': address }, function (results, status) {
                   if (status == google.maps.GeocoderStatus.OK) {
                       //We got an address back so set this
                       self.geocodedLocation({lat:results[0].geometry.location.lat(),lng:results[0].geometry.location.lng()});
                       //Set the query string for checking
                       self.locationquery(address);
                        //Set sort to distance  
                        self.sortDirection('dec');
                        self.selectedSortType('distance');
                        self.sortList('distance');
                   }
                   else {
                       //Geocode fail
                       //TODO:Add front end message here for failure?
                      console.log("Geocoding failed: " + status);
                   }
                });
             }
        }

        //Update combo search
        self.comboSearch = function(data,element){
            var searchTerms = ($(element.currentTarget).siblings('.prettySearchValue')).val();
            
            //Add this check for ie9 placeholder issues
            var searchTermsPlaceHolder = ($(element.currentTarget).siblings('.prettySearchValue')).attr('placeholder');
            if(searchTerms == searchTermsPlaceHolder){
                searchTerms = '';
            }
            
            var locationTerms = ($(element.currentTarget).siblings('.prettySearchLocationValue')).val();
            
            //Add this check for ie9 placeholder issues
            var locationTermsPlaceHolder = ($(element.currentTarget).siblings('.prettySearchLocationValue')).attr('placeholder');
            if(locationTerms == locationTermsPlaceHolder){
                locationTerms = '';
            }

            var geocoder = new google.maps.Geocoder();
            var address = locationTerms;

            //Add default country if set
            if (mapObject.options.country != '' && locationTerms.length > 0) {
                address = address + ', ' + mapObject.options.country;
            }

            if (geocoder && locationTerms.length > 0) {
                geocoder.geocode({ 'address': address }, function (results, status) {
                   if (status == google.maps.GeocoderStatus.OK) {
                       //We got an address back so set this
                       self.geocodedLocation({lat:results[0].geometry.location.lat(),lng:results[0].geometry.location.lng()});
                       //Set the query string for checking
                       self.locationquery(address);

                       self.query(searchTerms);
                        //Set sort to distance                
                        self.sortDirection('dec');
                        self.selectedSortType('distance');
                        self.sortList('distance');                      
                   }
                   else {
                       //Geocode fail
                       //TODO:Add front end message here for failure?
                      console.log("Geocoding failed: " + status);
                      //Fallback to just text
                      self.query(searchTerms);
                   }
                });
             }
             else{
                 self.query(searchTerms);
             }
        }

        //NOTE: Add the following to delay search .extend({ throttle: 500 });

        //Clear search
        self.clearSearch = function() {
            $('.prettySearchValue', self.MapHolder).val('');
            if ($('.prettySearchLocationValue', self.MapHolder).length) {
                $('.prettySearchLocationValue', self.MapHolder).val('');
            }

            self.resetAll();
        };

        /** Binding to make content appear with 'fade' effect */
        ko.bindingHandlers['slideIn'] = {
            'update': function(element, valueAccessor) {                
                var options = valueAccessor();
                if(options() === true){
                  $(element).slideDown(300);
                }
            }
        };
        
        /** Binding to make content disappear with 'fade' effect */
        ko.bindingHandlers['slideOut'] = {
            'update': function(element, valueAccessor) {
                var options = valueAccessor();
                if(options() === false){
                  $(element).slideUp(300);
                }
            }
        };

        //Item click
        //======================================
        self.locationClick = function(location,element){
            //Close all locations
            $.each(self.mapLocations,function(index, thislocation){
                if(location != thislocation){
                    thislocation.expanded(false);    
                }                
            })

            //location.selected(true);

            var targetItem =  $(element.currentTarget);
            var clickedItem =  $(element.target);

            if (mapObject.options.viewstyle != 'listonly') {
                self.showInfoWindow(location);
            }

            var parentItem = clickedItem.closest('.mapLocationDetail');

            //If this is a link in the detail area then exit
            if(parentItem.length){
                return true;
            }

            //Get detail div
            var mapLocationDetail = targetItem.children('.mapLocationDetail');

            //show it or hide it
            //HIDE IT
            if(mapLocationDetail.is(':visible')){
                
                location.expanded(false);
                
                if (mapObject.options.viewstyle != 'listonly') {
                    self.infowindow.close();

                    if(mapObject.options.keepzoomlevel != 'true'){
                        self.resetMapZoom();
                    }
                }
                
                //Clear directions
                (targetItem.find('.mapLocationDirectionsHolder')).html('');
                self.directionsRenderer.setMap(null);

            }
            else{
                //SHOW IT
                location.expanded(true);               
            }
        }

        //Paging
        //======================================
        self.pageSize = ko.observable(mapObject.options.locationsperpage);   //Items per page
        self.pageIndex = ko.observable(0);  //Current page       

        //Next clicked
        self.nextPage = function(data,element){
            var locations = self.filteredLocations();
            var size = self.pageSize();
            var start = parseInt(self.pageIndex()) * parseInt(self.pageSize());

            //Range check
            if((parseInt(start) + parseInt(size)) < locations.length){
                self.pageIndex(parseInt(self.pageIndex()) + 1);
            }
            else{
              return false;
            }
        };

        //Prev clicked
        self.prevPage = function() {
            var size = self.pageSize();
            var start = self.pageIndex() * self.pageSize();

            //Range check
            if ((parseInt(start) - parseInt(size)) >= 0) {
                self.pageIndex(self.pageIndex() - 1);
            } else {
                return false;
            }
        };

        //Sorting
        //===================



        if (self.geoenabled == 'true' || mapObject.options.simplesearch != 'true') {
            //show sort list click
            $('.showSortingBtn', self.MapHolder).click(function () {
               var clicked = $(this);

               clicked.siblings('.prettyFileSorting').slideToggle(200);
               return false;
           });
        }

        //Sort click
        self.sortList = function(sorttype, element) {
            if(element != null){
                //Update UI
                var thisButton = $(element.currentTarget);
                var sortType = thisButton.data('sorttype');
            }else{
                var sortType = sorttype;
            }
            

            if (sortType == self.selectedSortType()) {
                //No change to type
                //...so change direction
                if (self.sortDirection() == 'asc') {
                    self.sortDirection('dec');
                } else {
                    self.sortDirection('asc');
                }
            } else {
                self.selectedSortType(sortType);
                self.sortDirection('asc');
            }

            if(element != null){
                //Reverse arrow on button
                thisButton.toggleClass('sortAsc');
            }else{
                //Go get the button if distance sort as this can be called by other functions
                if(self.selectedSortType() == 'distance'){                       
                    var distanceSortButton = $(".prettyFileSorting li:nth-child(2) a",self.MapHolder);
                    if(distanceSortButton.length){
                        distanceSortButton.toggleClass('sortAsc');
                    }
                }
            }
        };

        //================================================
        //Sort algorithms
        //================================================
        
        //DISTANCE SORT
        // ascending sort
        function asc_bydistance(a, b){
            if(self.locationquery()){
                return (parseFloat(b.searchDistanceAway()) < parseFloat(a.searchDistanceAway()) ? 1 : -1);
            }
            else{
                return (parseFloat(b.distanceAway()) < parseFloat(a.distanceAway()) ? 1 : -1);
            }

        }
        
        // decending sort
        function dec_bydistance(a, b){
            if(self.locationquery()){
                return (parseFloat(b.searchDistanceAway()) > parseFloat(a.searchDistanceAway()) ? 1 : -1);
            }
            else{
                return (parseFloat(b.distanceAway()) > parseFloat(a.distanceAway()) ? 1 : -1);
            }
        }

        //TITLE SORT
        // accending sort
        function asc_bytitle(a, b){
            return b.title.toLowerCase() == a.title.toLowerCase() ? 0 : (b.title.toLowerCase() < a.title.toLowerCase() ? -1 : 1)
        }
        // decending sort
        function dec_bytitle(a, b){
            return b.title.toLowerCase() == a.title.toLowerCase() ? 0 : (b.title.toLowerCase() > a.title.toLowerCase() ? -1 : 1)
        }

        //Get url parameters
        function getParameterByName(name)
        {
          name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
          var regexS = "[\\?&]" + name + "=([^&#]*)";
          var regex = new RegExp(regexS);
          var results = regex.exec(window.location.search);
          if(results == null)
            return "";
          else
            return decodeURIComponent(results[1].replace(/\+/g, " "));
        }

        //Get distance between two items
        var calculateDistance = function(p1lat, p1long, p2lat, p2long) {
            //Convert degrees to radians
            var rad = function(x) { return x * Math.PI / 180; }

            //Haversine formula
            var R;

            if ('METRIC' == 'METRIC') {
                R = 6372.8; // approximation of the earth's radius of the average circumference in km
            } else {
                R = 3961.3 //Radius in miles)
            }


            var dLat = rad(p2lat - p1lat);
            var dLong = rad(p2long - p1long);

            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(rad(p1lat)) * Math.cos(rad(p2lat)) * Math.sin(dLong / 2) * Math.sin(dLong / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c;

            return d.toFixed(1);
        };

        //Get directions
        //===================
        self.directionsType = "DRIVING";
        self.directionsService = new google.maps.DirectionsService();
        self.directionsRenderer = new google.maps.DirectionsRenderer({draggable: true});

        self.awaitingGeoDirections = null;
        self.awaitingGeoDirectionsLocation = null;

        self.getDirectionsClick = function(location,element){
           var thisButton = $(element.currentTarget);
           var endLocation = location.latitude + ',' +  location.longitude;

           //If geo
           if(thisButton.hasClass('getdirectionsgeo')){
               //See if home set already
               if(self.geoHomePosition){
                   //from,to,button
                   self.showDirections(self.geoHomePosition.latitude + ',' + self.geoHomePosition.longitude,endLocation,thisButton);
               }
               else{
                    self.awaitingGeoDirections = thisButton;
                    self.awaitingGeoDirectionsLatLng = endLocation;

                    //See if geo is needed
                    if (navigator.geolocation)
                    {
                        //Check to see available (from another map)
                        if(self.geoHomePosition == null){
                            navigator.geolocation.getCurrentPosition(getGeo,getGeoError);
                        }
                        else{
                            getGeo(self.geoHomePosition);
                        }
                    }
                    else{
                        //If geo enabled but no navigator (<ie8)
                        getGeoError();
                    }
               }
           }
           else{
                //The start/end locations
                var locationEntryField = thisButton.siblings('.directionsPostcode');
                var startLocation = locationEntryField.val();

                //If no location entered show error
                if(startLocation == ''){
                    locationEntryField.addClass('error');
                }
                else{
                    locationEntryField.removeClass('error');
                    //Show directions with our data
                    self.showDirections(startLocation,endLocation,thisButton);
                }


           }

           return false;
        }

        self.showDirections = function(from, to, buttonClicked) {
            if (mapObject.options.viewstyle != 'listonly') {
                //Link Renderer to the map
                self.directionsRenderer.setMap(self.map);
            }

            //The directions list div
            var directionsHolder = buttonClicked.siblings('.mapLocationDirectionsHolder');

            //Travel mode
            //Default to driving mode
            var travelMode = google.maps.TravelMode.DRIVING;

            if (self.directionsType == "WALKING") {
                travelMode = google.maps.TravelMode.WALKING;
            } else if (self.directionsType == "BICYCLING") {
                travelMode = google.maps.TravelMode.BICYCLING;
            } else if (self.directionsType == "TRANSIT") {
                travelMode = google.maps.TravelMode.TRANSIT;
            }

            //Measurement units to use
            var unitSystem;
            if (maplistScriptParamsKo.measurementUnits == "METRIC") {
                unitSystem = google.maps.UnitSystem.METRIC;
            } else {
                unitSystem = google.maps.UnitSystem.IMPERIAL;
            }

            //Request object
            var request = {
                origin: from,
                destination: to,
                travelMode: travelMode,
                unitSystem: unitSystem
            };

            self.directionsRenderer.setPanel(directionsHolder[0]);

            self.directionsService.route(request, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    self.directionsRenderer.setDirections(response);
                    //Don't add the print button twice
                    if(!$(directionsHolder[0]).next().hasClass('printDirections')){
                        $(directionsHolder[0]).after('<a href="#" class="printDirections corePrettyStyle">' + maplistScriptParamsKo.printDirectionsMessage + '</a>');    
                    }
                    
                    //hide the infowindow
                    self.infowindow.close();
                } else {
                    console.log(status);
                }
            });
        };

        //Print button
        $('body').on('click','.printDirections',function(){
            var content = $(this).prev().html();
            printContent(content);
            return false;
        });

        //Print directions
        function printContent(content) {
            newwin = window.open('', 'printwin', '');
            newwin.document.write('<HTML>\n<HEAD>\n');
            newwin.document.write('<TITLE>Print Page</TITLE>\n');
            newwin.document.write('<script>\n');
            newwin.document.write('function chkstate(){\n');
            newwin.document.write('if(document.readyState=="complete"){\n');
            newwin.document.write('setTimeout("window.close()", 10); \n');
            newwin.document.write('}\n');
            newwin.document.write('else{\n');
            newwin.document.write('setTimeout("chkstate()",2000)\n');
            newwin.document.write('}\n');
            newwin.document.write('}\n');
            newwin.document.write('function print_win(){\n');
            newwin.document.write('window.print();\n');
            newwin.document.write('chkstate();\n');
            newwin.document.write('}\n');
            newwin.document.write('<\/script>\n');
            newwin.document.write('</HEAD>\n');
            newwin.document.write('<BODY onload="print_win()">\n');
            newwin.document.write(content);
            newwin.document.write('</BODY>\n');
            newwin.document.write('</HTML>\n');
            newwin.document.close();
        };

        //Main object
        //===========================

        //Filtered locations
        self.filteredLocations = ko.computed(function () {
            //Search query
            var locations = '';

            //TODO:Switch this to check for default string
            if(self.query().length > 0 || self.locationquery().length > 0){
                //Place or text search?
                if (mapObject.options.simplesearch == 'true') {
                    //TEXT SEARCH
                    var search = self.query().toLowerCase();
                    locations = ko.utils.arrayFilter(self.mapLocations, function(location) {
                        var locationFound = false

                        //Search title and description
                        if(location.title.toLowerCase().indexOf(search) != -1 || location.description.toLowerCase().indexOf(search) != -1 ){
                            locationFound = true;
                        }

                        //Optional search categories
                        $.each(location.categories, function(index, locationCat) {
                            if (locationCat.title.toLowerCase().indexOf(search) != -1) {
                                locationFound = true;
                            }
                        });

                        if(locationFound == true){
                            return location;
                        }

                    });
                }
                else if (mapObject.options.simplesearch == 'false') {
                    //LOCATION mapObject.options
                    var maxDistance = self.chosenFromDistance();
                    var geocodedLocation = self.geocodedLocation();

                    locations = ko.utils.arrayFilter(self.mapLocations, function(location) {
                        var distanceAway = calculateDistance(location.latitude,location.longitude,geocodedLocation.lat,geocodedLocation.lng);

                        location.searchDistanceAway(distanceAway);

                         if(parseInt(location.searchDistanceAway()) < maxDistance){
                             return location;
                         }
                    });
                }
                else{
                    //COMBO SEARCH
                    if(self.locationquery().length > 0){
                        //LOCATION SEARCH
                        var maxDistance = self.chosenFromDistance();
                        var geocodedLocation = self.geocodedLocation();

                        locations = ko.utils.arrayFilter(self.mapLocations, function(location) {
                            var distanceAway = calculateDistance(location.latitude,location.longitude,geocodedLocation.lat,geocodedLocation.lng);

                            location.searchDistanceAway(distanceAway);

                             //Search title and description
                             if(parseInt(location.searchDistanceAway()) < maxDistance){
                                 return location;
                             }
                        });
                    }else{
                        locations = self.mapLocations;
                    }

                    //TEXT SEARCH
                    var search = self.query().toLowerCase();
                    locations = ko.utils.arrayFilter(locations, function(location) {
                        var locationFound = false

                        //Search title and description
                        if(location.title.toLowerCase().indexOf(search) != -1 || location.description.toLowerCase().indexOf(search) != -1 ){
                            locationFound = true;
                        }

                        //Optional search categories
                        $.each(location.categories, function(index, locationCat) {
                            if (locationCat.title.toLowerCase().indexOf(search) != -1) {
                                locationFound = true;
                            }
                        });

                        if(locationFound == true){
                            return location;
                        }
                    });
                }
            }
            else{
                //HOME LOCATION SET
                //Check to see if homelocation set as we use this for distance calc
                if(self.homelocation != null && self.homelocation != ''){
                        locations = ko.utils.arrayFilter(self.mapLocations, function(location) {
                            var distanceAway = calculateDistance(location.latitude,location.longitude,self.homelocation.latitude,self.homelocation.longitude);
                            location.distanceAway(distanceAway);
                            self.sortDirection('dec');
                            self.selectedSortType('distance');
                            self.sortList('distance'); 
                            return location;
                        });



                }else{                    
                    //No search terms, so send all locations through
                    locations = self.mapLocations;
                }
            }

            //CATEGORY FILTERING
            //===========================
            //Only filter if one (or more) has been selected
            if(self.useCategoryFilter() == true){
                //Filter by selected cats
                var categorisedLocations = [];
                $.each(locations, function(index, location) {
                    //DISTANCE TEXT
                    //=============================
                    //Also set distance text in same query to avoid looping twice
                    if (self.locationquery()) {
                        //Show distance from search
                        location.friendlyDistance(' (' + location.searchDistanceAway() + ' ' + self.unitSystemFriendly + ')');
                    } else {
                        //Don't show distance on non-geo maps
                        if (self.geoenabled == 'true' || (self.homelocation != null && self.homelocation != '')) {
                            //Show distance from home
                            if (location.distanceAway()) { //If geo has been got
                                location.friendlyDistance(' (' + location.distanceAway() + ' ' + self.unitSystemFriendly + ')');
                            } else {
                                location.friendlyDistance('');
                            }

                        } else {
                            location.friendlyDistance('');
                        }
                    }

                    //Get all selected categories
                    var selectedCategories = ko.utils.arrayFilter(self.mapCategories, function(category) {
                        if (category.selected()) {
                            return category;
                        }
                    });

                    var found = false;
                    //loop selectedCategories
                    $.each(selectedCategories, function(index, category) {

                        //loop location categories
                        $.each(location.categories, function(index, locationCat) {
                            if (category.slug == locationCat.slug) {
                                categorisedLocations.push(location);
                                found = true;
                                return false;
                            }
                        });
                        //Break out of outer loop
                        if (found) {
                            return false;
                        }
                    });
                });
            }
            else{
                categorisedLocations = locations;    
            }

            //Sort by sort selection
            if(self.sortDirection() == 'asc' && self.selectedSortType() == 'title'){
                categorisedLocations.sort(asc_bytitle);
            }
            else if(self.sortDirection() == 'dec' && self.selectedSortType() == 'title'){
                categorisedLocations.sort(dec_bytitle);
            }
            else if(self.sortDirection() == 'asc' && self.selectedSortType() == 'distance'){
                categorisedLocations.sort(asc_bydistance);
            }
            else if(self.sortDirection() == 'dec' && self.selectedSortType() == 'distance'){
                categorisedLocations.sort(dec_bydistance);
            }
            
            //Reduce results number if needed
            if (mapObject.options.limitresults != -1) {
                categorisedLocations = categorisedLocations.slice(0, parseInt(mapObject.options.limitresults));
            }
            
            //If only one result expand it
            if (categorisedLocations.length == 1) {
                categorisedLocations[0].expanded(true);
            }

            //Reset paging
            self.pageIndex(0);

            return categorisedLocations;
        }, self).extend({ throttle: 10 });;

        //Map binding
        //============================
        ko.bindingHandlers.map = {           
            update: function (element, valueAccessor, allBindingsAccessor, viewModel) {

                //Hide all markers
                $.each(viewModel.mapLocations, function (index, location) {
                    if (location._mapMarker) {
                        location._mapMarker.setVisible(false);
                    }
                });
                
                // First get the latest data that we're bound toself
                var value = valueAccessor();

                var search = viewModel.query();
                // Next, whether or not the supplied model property is observable, get its current value
                var valueUnwrapped = ko.utils.unwrapObservable(value);
                
                if(valueUnwrapped.length <= 0){
                    return false;
                }

                $.each(valueUnwrapped, function (index, location) {
                    //if marker is not already set on the location
                    if(!location._mapMarker){

                        //var shape = {
                        //    coord: [19,2,21,3,23,4,23,5,24,6,24,7,25,8,25,9,25,10,25,11,25,12,25,13,25,14,25,15,25,16,24,17,24,18,23,19,23,20,22,21,22,22,21,23,20,24,20,25,19,26,18,27,18,28,17,29,17,30,14,30,14,29,13,28,13,27,12,26,11,25,11,24,10,23,9,22,9,21,8,20,8,19,7,18,7,17,6,16,6,15,6,14,6,13,6,12,6,11,6,10,6,9,6,8,7,7,7,6,8,5,8,4,10,3,12,2,19,2],
                        //    type: 'poly'
                        //};

                        var image = new google.maps.MarkerImage(
                            location.pinImageUrl//,
                            //new google.maps.Size(52,32),
                            //new google.maps.Point(0,0),
                            //new google.maps.Point(16,32)
                        );

                        if(location.pinShadowOverrides == null){
                            var shadow = new google.maps.MarkerImage(                        
                                location.pinShadowImageUrl,
                                new google.maps.Size(50,50),
                                new google.maps.Point(0,0),
                                new google.maps.Point(18,31)//This sets the shadow position
                        );
                        }
                        else{
                            var shadow = new google.maps.MarkerImage(                        
                                location.pinShadowImageUrl,
                                new google.maps.Size(parseInt(location.pinShadowOverrides[0]),parseInt(location.pinShadowOverrides[1])),
                                new google.maps.Point(0,0),
                                new google.maps.Point(parseInt(location.pinShadowOverrides[2]),parseInt(location.pinShadowOverrides[3]))//This sets the shadow position
                            );
                        }
                        

                        var position = new google.maps.LatLng(location.latitude, location.longitude);
                        var mapToUse = self.map;

                        if (mapObject.options.clustermarkers == 'true') {
                            mapToUse = null;
                        }

                        //Create marker
                        //-----------------------------
                        var marker = new google.maps.Marker({
                                map: mapToUse,
                                position: position,
                                title: location.title,
                                content: '',
                                icon: image,
                                optimized: false,
                                animation: google.maps.Animation.DROP,
                                shadow: shadow//,
                                //shape: shape
                        });

                        //Click the marker
                        //-----------------------------
                        google.maps.event.addListener(marker, 'click', function () {
                            //Show the bubble
                            viewModel.showInfoWindow(location);
                            //Expand the list item
                            location.expanded(true);
                        });

                        //Set the marker
                        //-----------------------------u
                        location._mapMarker = marker;
                        viewModel.markers.push(marker);
                    }

                    //Show this location
                    location._mapMarker.setAnimation(google.maps.Animation.DROP);
                    location._mapMarker.setVisible(true);
                });
                
                //Set zoom
                viewModel.resetMapZoom();
            }
        };

        //Default start pos
        self.centrePoint = '';
        self.defaultZoom = '';

        self.resetMapZoom = function () {

            //Clear cluster markers
            if (mapObject.options.clustermarkers == 'true') {
                self.markerClusterer.clearMarkers();
            }

            if (mapObject.options.startlat) {
                //MANUAL LOCATION

                //If centre point doesn't exist yet set it
                if (self.centrePoint == '') {
                    self.centrePoint = new google.maps.LatLng(mapObject.options.startlat, mapObject.options.startlong);
                    self.defaultZoom = parseInt(mapObject.options.defaultzoom);
                }

                //Update clusterer if we need it
                if (mapObject.options.clustermarkers == 'true') {
                    ko.utils.arrayForEach(self.filteredLocations.peek(), function(location) {
                        self.markerClusterer.addMarker(location._mapMarker);
                    });
                }
                
                
                self.map.panTo(self.centrePoint);
                self.map.setZoom(self.defaultZoom);
            } else {
                //AUTO LOCATION

                //Bounds object
                self.bounds = new google.maps.LatLngBounds();

                ko.utils.arrayForEach(self.filteredLocations.peek(), function(location) {
                    self.bounds.extend(location._mapMarker.position);

                    //If home location is in use make sure that it's in bounds
                    if(self.homelocation){
                        self.bounds.extend(self.homelocation._mapMarker.position);                        
                    }

                    //If geo is used use it in bounds
                    if(self.userLocation._mapMarker){
                        self.bounds.extend(self.userLocation._mapMarker.position);                        
                    }                    

                    if (mapObject.options.clustermarkers == 'true') {
                        self.markerClusterer.addMarker(location._mapMarker);
                    }
                });

                //Fit these bounds to the map
                self.map.fitBounds(self.bounds);

                //Move to default position and zoom if no results
                if (self.bounds.isEmpty()) {
                    self.map.setZoom(self.defaultZoom);
                    self.map.panTo(self.centrePoint);
                }
            }
        };


        if (mapObject.options.viewstyle != 'listonly') {

            //Set centrepoint for future use (no results found etc.)
            //Fitbounds happens async so need this to get zoom
            var boundsChangeBoundsListener = google.maps.event.addListener(self.map, 'bounds_changed', function (event) {
                if (!self.defaultZoom) {
                    self.defaultZoom = self.map.getZoom();

                    //If home mode make it centre
                    if(self.homelocation){
                        self.centrePoint = new google.maps.LatLng(self.homelocation.latitude, self.homelocation.longitude) ;
                    }
                    else{
                        self.centrePoint = self.map.getCenter();    
                    }
                    
                    google.maps.event.removeListener(boundsChangeBoundsListener);
                }
            });
        }

        //Show the infowindow
        self.showInfoWindow = function(location) {
            var marker = location._mapMarker;
            var position = new google.maps.LatLng(location.latitude, location.longitude);
            //TODO:Create builder for this and move it server sidelis
            var content = "<div class='infoWindow'>";
                content += "<h3>" + location.title + "</h3>";
                content += '<div class="infowindowContent">';
                    if (location.imageUrl) {
                        content += "<img src='" + location.imageUrl + "' class='locationImage'/>";
                    }

                    content += location.description;

                    if (location.locationUrl != '' && maplistScriptParamsKo.hideviewdetailbuttons != 'true') {
                        content += "<a class='viewLocationPage btn corePrettyStyle' " + (mapObject.options.openinnew == false ? "" : "target='_blank'") + " href='" + location.locationUrl + "' >" + maplistScriptParamsKo.viewLocationDetail + "</a>";
                    }                
                content += "</div>";
            content += "</div>";

            self.infowindow.setContent(content);

            //Zoom to selected zoom level
            if (mapObject.options.selectedzoomlevel != '' && mapObject.options.keepzoomlevel != 'true') {
                self.map.setZoom(parseInt(mapObject.options.selectedzoomlevel));
            }            

            self.infowindow.open(self.map, marker);

            //Move to marker
            self.map.panTo(position);
            return true;
        };

        //Show clear button
        self.showClearButton = ko.computed(function(){
            return self.query.length > 0 || self.locationquery().length > 0;
        });

        //Should paging show
        self.pagingVisible = function(){
            return Math.ceil(self.filteredLocations().length / self.pageSize()) > 0;
        };
        
        //Disable/Enable next button
        self.nextPageButtonCSS = ko.computed(function(){
            if((self.pageIndex() + 1) * self.pageSize() >= self.filteredLocations().length){
                return "disabled";
            }
            else{
                return "";
            }
        });

        //Disable/Enable prev button
        self.prevPageButtonCSS = ko.computed(function(){
            if(self.pageIndex() == 0){
                return "disabled";
            }else{
                return "";
            }
        });
        
        //Pages count
        self.totalPages = ko.computed(function(){
            return Math.ceil(self.filteredLocations().length / self.pageSize() );
        })

        //Human current page
        self.currentPageNumber = ko.computed(function(){
            return self.pageIndex() + 1;
        })        

        
        //Any locations
        self.anyLocationsAvailable = ko.computed(function(){
            return self.filteredLocations().length == 0;
        });


        //Paged locations
        //Needs to be separate as map markers are not paged
        self.pagedLocations = ko.computed(function () {
            var locations = self.filteredLocations();
            
            var start = self.pageIndex() * self.pageSize();

            //Next page
            return locations.slice(start, parseInt(start) + parseInt(self.pageSize()));
        }, self);
    }

    //Convert json to object
    var dataFromServer = maplistScriptParamsKo.KOObject;

    //Create an array of maps in case we need to fire anything
    var MapListProMaps = [];

    // Activates knockout.js
    $.each(dataFromServer, function (index, value) {
        var newMap = new MapViewModel(value, value.id);
        MapListProMaps.push(newMap);
        ko.applyBindings(newMap, document.getElementById('MapListPro' + value.id));
    });

    //Remove loading from all messages
    //TODO:Move this into object
    $('.prettyListItems.loading').removeClass('loading');

    // If you need to resize the map because it's in an accordion etc. and it's not showing the correct size
    // do this (change the [0] to the index of the map you need to redraw):
    // google.maps.event.trigger(MapListProMaps[0].map, "resize");

});