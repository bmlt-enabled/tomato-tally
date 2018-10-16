<?php
// CONFIGURATION
$google_maps_api_key = "";
$root_server = "https://tomato.na-bmlt.org";

$meetings_respone = get($root_server . "/main_server/client_interface/json/?switcher=GetSearchResults&data_field_key=latitude,longitude,weekday_tinyint,start_time,meeting_name");
$meetings = json_decode($meetings_respone, true);
$unique_meetings = unique_by_keys($meetings,array("latitude","longitude"));
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
        controlText.style.fontSize = '16px';
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
        var centerControlDiv = document.createElement('div');
        var centerControl = new CenterControl(centerControlDiv, map);

        // Create the DIV to hold the control and call the CenterControl()
        // constructor passing in this DIV.
        centerControlDiv.index = 1;
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);
        // Create an array of alphabetical characters used to label the markers.
        var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var infoWin = new google.maps.InfoWindow();
        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var markers = locations.map(function(location, i) {
            var infoHTML = '<dt><strong>';
            infoHTML += location.name;
            infoHTML += '</strong></dt>';
            infoHTML += '<dd><em>';
            infoHTML += location.day;
            infoHTML += ' ' + location.time;
            infoHTML += '</em></dd>';
            infoHTML += '<dd><em><a href="';
            infoHTML += location.sburi + 'semantic' + '">';
            infoHTML += location.sbname;
            infoHTML += '</a></em></dd>';

            var marker = new google.maps.Marker({
                position: location,
                label: labels[i % labels.length]
            });
            google.maps.event.addListener(marker, 'click', function(evt) {

                infoWin.setContent(infoHTML);
                infoWin.open(map, marker);
            })
            return marker;
        });

        // Add a marker clusterer to manage the markers.
        var markerCluster = new MarkerClusterer(map, markers, {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
        });


    }
    var locations = [
        <?php
        foreach ($unique_meetings as $meeting) {
            $label = $meeting['latitude'] . ", " . $meeting['longitude'];
            echo '{lat: ' . $meeting['latitude'] . ', lng: ' . $meeting['longitude'] . ', day: \'' . $GLOBALS['days_of_the_week'][$meeting['weekday_tinyint']] . '\', time: \'' .  date("g:i a", strtotime($meeting['start_time'])) . '\', name: \'' . strtr($meeting['meeting_name'], "'", " ") . '\'},' . "\n";
        }
        ?>
    ]
</script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key ?>&callback=initMap">
</script>
