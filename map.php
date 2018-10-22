<?php
// CONFIGURATION
$google_maps_api_key = "";
$root_server = "https://tomato.na-bmlt.org";

$meetings_respone = get($root_server . "/main_server/client_interface/json/?switcher=GetSearchResults&data_field_key=latitude,longitude,weekday_tinyint,start_time,meeting_name,root_server_uri");
$meetings = json_decode($meetings_respone, true);
foreach($meetings as &$val) {
    // Google API needs Latitude/Longitude to be named lat/lng and must be floats
    $val['lat'] = floatval($val['latitude']);
    $val['lng'] = floatval($val['longitude']);
    unset($val['latitude'], $val['longitude']);
}
foreach($meetings as $key => $value) {
    $keyfind = array_search($meetings[$key]['root_server_uri'], array_column($rootServers, 'root_server_url'));
    $meetings[$key]['sbname'] = $rootServers[$keyfind]['name'];
}
//$unique_meetings = unique_by_keys($meetings,array("lat","lng"));
$meetings_json = json_encode($meetings);
?>
<script type="text/javascript">
    var map;
    function CenterControl(controlDiv, map) {

        // Set CSS for the control border.
        var controlUI = document.createElement('div');
        controlUI.style.backgroundColor = '#fff';
        controlUI.style.border = '2px solid #fff';
        controlUI.style.borderRadius = '3px';
        controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
        controlUI.style.cursor = 'pointer';
        controlUI.style.marginBottom = '22px';
        controlUI.style.textAlign = 'center';
        controlUI.title = 'Click to go to table view';
        controlDiv.appendChild(controlUI);

        // Set CSS for the control interior.
        var controlText = document.createElement('div');
        controlText.style.color = 'rgb(25,25,25)';
        controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
        controlText.style.fontSize = '22px';
        controlText.style.lineHeight = '38px';
        controlText.style.paddingLeft = '5px';
        controlText.style.paddingRight = '5px';
        controlText.innerHTML = 'Table View';
        controlUI.appendChild(controlText);
        controlUI.addEventListener('click', function() {
            displayTallyMap();
        });

    }
    function initMap() {
        var map = new google.maps.Map(document.getElementById('tallyMap'), {
            zoom: 3,
            center: {
                lat: 36.975594,
                lng: -99.688277
            }
        });
        var clusterMarker = [];
        var centerControlDiv = document.createElement('div');
        var centerControl = new CenterControl(centerControlDiv, map);

        // Create the DIV to hold the control and call the CenterControl()
        // constructor passing in this DIV.
        centerControlDiv.index = 1;
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);
        var infoWindow = new google.maps.InfoWindow();

        // Create OverlappingMarkerSpiderfier instsance
        var oms = new OverlappingMarkerSpiderfier(map, { markersWontMove: true, markersWontHide: true });

        oms.addListener('format', function(marker, status) {
            var iconURL = status == OverlappingMarkerSpiderfier.markerStatus.SPIDERFIED ? 'images/NAMarkerR.png' :
                status == OverlappingMarkerSpiderfier.markerStatus.SPIDERFIABLE ? 'images/NAMarkerB.png' :
                status == OverlappingMarkerSpiderfier.markerStatus.UNSPIDERFIABLE ? 'images/NAMarkerR.png' :
                null;
            var iconSize = new google.maps.Size(22, 32);
            marker.setIcon({
                url: iconURL,
                size: iconSize,
                scaledSize: iconSize
            });
        });


        // This is necessary to make the Spiderfy work
        oms.addListener('click', function(marker) {
            infoWindow.setContent(marker.desc);
            infoWindow.open(map, marker);
        });
        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var markers = locations.map(function(location, i) {
            var weekdays = ['ERROR', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var marker_html = '<dt><strong>';
            marker_html += location.meeting_name;
            marker_html += '</strong></dt>';
            marker_html += '<dd><em>';
            marker_html += weekdays[parseInt ( location.weekday_tinyint )];
            var time = location.start_time.toString().split(':');
            var hour = parseInt ( time[0] );
            var minute = parseInt ( time[1] );
            var pm = 'AM';
            if ( hour >= 12 ) {
                pm = 'PM';
                if ( hour > 12 ) {
                    hour -= 12;
                };
            };
            hour = hour.toString();
            minute = (minute > 9) ? minute.toString() : ('0' + minute.toString());
            marker_html += ' ' + hour + ':' + minute + ' ' + pm;
            marker_html += '</em></dd>';
            var url = location.root_server_uri + 'semantic';
            marker_html += '<dd><em><a href="' + url + '">';
            marker_html += location.sbname;
            marker_html += '</a></em></dd>';

            var marker = new google.maps.Marker({
                position: location,
                map: map
            });

            // needed to make Spiderfy work
            oms.addMarker(marker);

            // needed to cluster marker
            clusterMarker.push(marker);
            google.maps.event.addListener(marker, 'click', function(evt) {
                infoWindow.setContent(marker_html);
                infoWindow.open(map, marker);
            })
            return marker;
        });

        // Add a marker clusterer to manage the markers.
        new MarkerClusterer(map, clusterMarker, {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m', maxZoom: 15});
    }
    var locations = <?php echo $meetings_json; ?>

</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key ?>&callback=initMap">
</script>
