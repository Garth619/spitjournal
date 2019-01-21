jQuery(document).ready(function ($) {
    /**
	 * Initialize Google map
	 */
    
    if ($('#GoogleMap').length) {
        
        var marker = null;
        var infowindow = new google.maps.InfoWindow();

        var addMapLocationTitle = $('#title'),
        addMapLocationDescription = $('.addMapLocationDescription'),
        addMapLocationAddress = $('#maplist_address'),
        latLongUpdate = $('#UpdateMap'),
        latField = $('#maplist_latitude'),
        longField = $('#maplist_longitude');

        //Create the map
        //TODO: Get lat/lng from settings
        var latlng = new google.maps.LatLng(maplocationdata.defaultEditMapLocationLat, maplocationdata.defaultEditMapLocationLong);
        var myOptions = {
            zoom: parseInt(maplocationdata.defaultEditMapZoom),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map($("#GoogleMap")[0], myOptions);
        map.setCenter(latlng);

        //Lat long update button
        latLongUpdate.click(function () {
            addMapListMarker(map, new google.maps.LatLng(parseFloat(latField.val()), parseFloat(longField.val())), '');
            latField.val();
            longField.val();
            return false;
        });

        //Map search
        var input = $('#MapSearchInput')[0];
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        //Make return key search
        $("#MapSearchInput").keypress(function (event) {
            if (event.which == 13) {    // make enter key just search map and stop it from submitting form
                event.preventDefault();
            }
        });

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            // infowindow.close();
            var place = autocomplete.getPlace();
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);  // Why 17? Because it looks good.
            }

            var address = '';
            if (place.address_components) {            
                address = [
                (place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
                //If place name not already part add it
                console.log(place.address_components[0].short_name + place.address_components[1].short_name);
                console.log(place.name);
                if((place.address_components[0].short_name + ' ' + place.address_components[1].short_name) != place.name){
                    address = place.name + ', ' + address; 
                }
            }

            var html = address;

            addMapListMarker(map, place.geometry.location, html);

            //If title is empty fill it
            if (addMapLocationTitle.val() == "") {
                $('#title-prompt-text').addClass('screen-reader-text');
                addMapLocationTitle.val(place.name);
            }

            //Fill the description box
            if (typeof (tinymce) != "undefined") {
                if (tinymce.activeEditor != null && tinymce.activeEditor.isHidden() != true) {
                    //Check that editor is empty
                    if (tinymce.activeEditor.getContent() == "" || tinymce.activeEditor.getContent() == null) {
                        tinymce.activeEditor.setContent(html)
                    }
                }
                else {
                    //Check to make sure it is empty
                    if ($('#maplist_description').val() == "") {
                        $('#maplist_description').val(html);
                    }
                }
            }

            updateMapFields(place.geometry.location, html);

            return false;

        });

        if (latField.val() != '' && longField.val() != '') {
            addMapListMarker(map, new google.maps.LatLng(latField.val(), longField.val()), '');
        }

        //Map clicked
        google.maps.event.addListener(map, 'click', function (event) {
            //$('#maplist_latitude').val(event.latLng.lat());
            //$('#maplist_longitude').val(event.latLng.lng()); 

            //Content for infowindow
            var html = '';

            //Try to get address from lat/lng
            reverseGeo(map, event.latLng);
        });
    }

    function reverseGeo(map, point) {
        //Try to get address from lat/lng
        var html = 'Chosen location';
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'latLng': point }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK && results[0]) {
                html = results[0].formatted_address;
                addMapListMarker(map, point, html);

            }
            else {
                addMapListMarker(map, point, 'Clicked location');
            }

            updateMapFields(point, html);
        });
    }

    function updateMapFields(point, addressText) {
        var addMapLocationAddress = $('#maplist_address'),
        latLongUpdate = $('#UpdateMap'),
        latField = $('#maplist_latitude'),
        longField = $('#maplist_longitude');

        if (addMapLocationAddress.length) {
            addMapLocationAddress.val(addressText);
        }

        if (latField.length && longField.length) {
            latField.val(point.lat());
            longField.val(point.lng());
        }
    }

    function addMapListMarker(map, point, html) {
        map.panTo(point);
        map.setZoom(16);

        if (marker == null) {
            marker = new google.maps.Marker({
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                position: point
            });
        }
        else {
            marker.setPosition(point);
        }

        infowindow.setContent(html);
        infowindow.open(map, marker);

        //Dragged marker stop
        google.maps.event.addListener(marker, 'dragend', function () {
            $('#maplist_latitude').val(marker.getPosition().lat());
            $('#maplist_longitude').val(marker.getPosition().lng());

            reverseGeo(map, marker.getPosition());
        });
    }
});