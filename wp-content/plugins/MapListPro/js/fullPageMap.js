jQuery(document).ready(function($){
    
    function FullPageMap() {
        
        //Convert json to object
       var location = ko.utils.parseJson(maplistFrontScriptParams.location);       

        //Self reference
        var self = this;
        
        //Map options
        self.mapOptions = {
           zoom: 15,
           center: new google.maps.LatLng(location.latitude, location.longitude),
           mapTypeId: google.maps.MapTypeId.ROADMAP,
           panControl: false,
           zoomControl: true,
           mapTypeControl: true,
           scaleControl: true,
           streetViewControl: true,
           overviewMapControl: false
       };
       
       self.map = new google.maps.Map($('#SingleMapLocation')[0], self.mapOptions);

       //Marker
        var shadow = new google.maps.MarkerImage(
            maplistFrontScriptParams.pluginurl + 'images/pins/shadow.png',
            new google.maps.Size(52,32),
            new google.maps.Point(0,0),
            new google.maps.Point(16,32)
        );

        self.position = new google.maps.LatLng(location.latitude, location.longitude);

        self.marker = new google.maps.Marker({
                  map: self.map,
                  position: self.position,
                  title: location.title,
                  content: '',
                  animation: google.maps.Animation.DROP,
                  shadow: shadow
          });

       
        if (maplistFrontScriptParams.disableInfoBoxes == 'true') {
            //Infowindow
            self.infowindow = new google.maps.InfoWindow({ pixelOffset: new google.maps.Size(-13, 33) });
        }
        else {
            //Infobox
            self.infoBoxOptions = {
                content: "",
                boxStyle : {
                    width : "500px"
                },             
                disableAutoPan: false,
                maxWidth: 400,
                alignBottom: true,
                pixelOffset: new google.maps.Size(-250, -40),
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
               
        //TODO:Create builder for this and move it server side
        self.content = "<div class='infoWindow'>";
            self.content += "<h3>" + location.title + "</h3>";
            self.content += '<div class="infowindowContent">';
                if(location.imageUrl){
                    self.content += "<img src='" + maplistFrontScriptParams.pluginurl + "includes/timthumb.php?src=" + location.imageUrl + "&w=100&h=100' class='locationImage'/>";
                }
                self.content += "<p>" + location.description + "</p>";
            self.content += "</div>";
        self.content += "</div>";

        self.infowindow.setContent(self.content);
        self.infowindow.open(self.map, self.marker);          
        
        //Click the marker
        //-----------------------------
         google.maps.event.addListener(self.marker, 'click',function(){
             //Show the bubble
             self.infowindow.open(self.map, self.marker);
         });        
    }
    
    var fullPageMap = new FullPageMap();
})
