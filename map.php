<?php
// CONFIGURATION
$google_maps_api_key = "";
$root_server = "https://tomato.na-bmlt.org";

$coordinates_respone = get($root_server . "/main_server/client_interface/json/?switcher=GetSearchResults&data_field_key=latitude,longitude");
$coordinates = json_decode($coordinates_respone, true);
$unique_coordinates = array_unique($coordinates, SORT_REGULAR);
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>BMLT - Meetings</title>
    <style>
        html, body {
            padding: 0;
            margin: 0;
            height: 100%;
        }
        #map {
            height: 100%;
        }
    </style>
</head>
<body>
<div id="map"></div>
<script>
    function initMap() {
        var mylocation = {lat: 42.2625932, lng: -71.8022934};
        var map = new google.maps.Map(document.getElementById('map'), {
            center: mylocation
        });

        var locations = [];

        <?php
        foreach ($unique_coordinates as $coordinate) {
                $label = $coordinate['latitude'] . ", " . $coordinate['longitude'];
                ?>

                addMarker({lat: <?php echo $coordinate['latitude']?>, lng: <?php echo $coordinate['longitude'] ?>}, map, "<?php echo $label ?>", "red");
                locations.push(new google.maps.LatLng(<?php echo $coordinate['latitude']?>, <?php echo $coordinate['longitude'] ?>));
        <?php
            }
        ?>

        autoZoom(locations, map);
    }

    function addMarker(location, map, content, icon_color) {
        var marker = new google.maps.Marker({
            position: location,
            icon: "https://maps.google.com/mapfiles/ms/icons/" + icon_color + "-dot.png",
            map: map,
            title: content,
            animation: google.maps.Animation.DROP});
            infoWindow = new google.maps.InfoWindow({
            	content: content
            });
            google.maps.event.addDomListener(window, 'load', function() {
                google.maps.event.addListener(marker, 'click', function() {
                    infoWindow.open(map, marker);
                });
            });
    }

    function autoZoom(locations, map) {
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0, locations_length = locations.length; i < locations_length; i++) {
            bounds.extend(locations[i]);
        }

        map.fitBounds(bounds);
    }
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key?>&callback=initMap">
</script>
</body>
</html>

<?php 

function get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bmltgeo' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $errorno = curl_errno($ch);
    curl_close($ch);
    if ($errorno > 0) {
        throw new Exception(curl_strerror($errorno));
    }
    return $data;
}

?>
