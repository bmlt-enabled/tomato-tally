
<?php
/*******************************************************************************************/
/**
    \brief  BMLTTally is a utility app that quickly polls a list of Root Servers, and displays their
            information in the form of a table, and a map.

            This started life as a "quick n' dirty one-off," so it does not cleave to the standards
            of the rest of the BMLT project.

        This file is part of the Basic Meeting List Toolbox (BMLT).

        Find out more at: https://bmlt.app

        BMLT is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as
        published by the Free Software Foundation, either version 3
        of the License, or (at your option) any later version.

        BMLT is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
        See the GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this code.  If not, see <http://www.gnu.org/licenses/>.
*/
$version = "1.3.0";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>BMLT Live Tally</title>
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="css/tally.css">
        <script type="text/javascript" src="js/jquery-3.3.1.js"></script>
        <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
        <script type="text/javascript">
            jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                "reference-pre": function ( ref ) {
                    ref = ref.split(".");
                    ref = (parseInt(ref[0]) * 1000000) + (parseInt(ref[1]) * 1000) + (parseInt(ref[2]));
                    return parseInt(ref);
                }
            })

            jQuery(document).ready(function(){
                jQuery('#tallyHo').DataTable( {
                    "lengthMenu": [[-1], ["All"]],
                    "bPaginate": false,
                    "bInfo" : false,
                    "bFilter": false,
                    columnDefs : [
                        { targets: 2, type: 'reference' }
                    ]
                } );
            } );
        </script>
        <link rel="shortcut icon" href="https://bmlt.magshare.net/wp-content/uploads/2014/11/FavIcon.png" type="image/x-icon" />
    </head>
    <body>
        <div id="tallyMan">
            <a href="https://bmlt.magshare.net"><img class="masthead" src="https://bmlt.magshare.net/wp-content/uploads/2014/01/cropped-BMLT-Blog-Logo1.png" /></a>
            <h2>Tally of Known BMLT Root Servers</h2>
            <div id="tallyLegend" style="display:  block;">
                <p id="tallyMo">*Bold green version number indicates server is suitable to use the <a href="https://itunes.apple.com/us/app/na-meeting-list-administrator/id1198601446">NA Meeting List Administrator</a> app.</p>
                <p id="tallyMo2">Remember that the server must be <a href="https://letsencrypt.org">SSL/HTTPS</a>, in addition to <a href="https://bmlt.magshare.net/semantic/semantic-administration/">Semantic Administration being enabled</a>.</p>
                <p id="tallyMo3">If <strong>BOTH</strong> of these conditions are not met, then you cannot use the admin app.</p>
                <div id="tallyMapButton"><a href="javascript:tallyManTallyMan.displayTallyMap();"><!-- Display Coverage Map --></a></div>
            </div>
            <table id="tallyLogTable" cellspacing="0" cellpadding="0" border="0" style="display:none"></table>
            <table id="tallyHo"  class="display" style="width:100%">
                <thead id="tallyHead">
                    <tr>
                        <td class="tallyName">Server Name</td>
                        <td id="tallySSL_Header">Is SSL?</td>
                        <td id="tallyVersion_Header">Version*</td>
                        <td id="tallyRegion_Header">Number of Regions</td>
                        <td id="tallyArea_Header">Number of Areas</td>
                        <td id="tallyMeeting_Header" class="selected">Number of Meetings</td>
                    </tr>
                </thead>
                <tbody id="tallyBody">
<?php 
$root_server = "https://tomato.na-bmlt.org";

//$coordinates_respone = get($root_server . "/main_server/client_interface/json/?switcher=GetSearchResults&data_field_key=latitude,longitude");
//$coordinates = json_decode($coordinates_respone, true);

$rootServers_respone = get($root_server . "/rest/v1/rootservers/");
$rootServers = json_decode($rootServers_respone, true);
$sortByName = array();
foreach ($rootServers as $key => $row)  {
    $sortByName[$key] = $row['name'];
}
array_multisort($sortByName, SORT_ASC, $rootServers);				
$serversAdminApp = "0";
$totalMeetings = "0";
$totalRegions = "0";
$totalAreas = "0";

foreach ($rootServers as $rootServer) {
    $serverInfo = json_decode($rootServer[server_info], true);
    $isSSLtxt = (strpos(strtolower($rootServer['root_server_url']), "https") !== false ? "YES" : "NO");
    $isSSL = (strpos(strtolower($rootServer['root_server_url']), "https") !== false ? true : false);
    $semanticAdmintxt = $serverInfo[0]['semanticAdmin'] == "1" ? 'YES' : 'NO';
    $isAdminOn = $serverInfo[0]['semanticAdmin'] == "1" ? true : false;
    $totalMeetings += $rootServer['num_meetings'];
    $totalRegions += $rootServer['num_regions'];
    $totalAreas += $rootServer['num_areas'];

    if ( $isSSL && ($serverInfo[0]['versionInt'] >= 2008012) && $isAdminOn ) {
        $validServer = 'validServer';
        $serversAdminApp++;
    } else {
        $validServer = 'invalidServer';
    };

    if ( $isSSL ) {
        $validSSL = 'validSSL';
    } else {
        $validSSL = 'inValidSSL';
    };
?>
                <tr>
                    <td class="tallyName"><a href="https://www.nerna.org/main_server/" class="tallyClick" target="_blank"><?php echo $rootServer['name'] ?></a> (<a href="<?php echo $rootServer['root_server_url'] . "semantic" ?>" class="tallySemanticClick" target="_blank">Semantic Workshop Link</a>)</td>
                    <td class="tallySSL <?php echo $validSSL ?>"><?php echo $isSSLtxt ?></td>
                    <td class="tallyVersion tallyCoverage <?php echo $validServer ?> tallyCoverage"><?php echo $serverInfo[0]['version'] ?></td>
                    <td class="tallyRegion"><?php echo $rootServer['num_regions'] ?></td>
                    <td class="tallyArea"><?php echo $rootServer['num_areas'] ?></td>
                    <td class="tallyMeeting"><?php echo $rootServer['num_meetings'] ?></td>
                </tr>
<?php 
} ?>
                <tfoot>
                <tr class="tallyTotal">
                    <td class="tallyName" colspan="3">TOTAL (<?php echo count($rootServers) ?> Servers, <?php echo $serversAdminApp ?> Can use the admin app)</td>
                    <td class="tallyRegion"><?php echo $totalRegions ?></td>
                    <td class="tallyArea"><?php echo $totalAreas ?></td>
                    <td class="tallyMeeting"><?php echo $totalMeetings ?></td>
                </tr>
                </tfoot>
                </tbody>
            </table>
    <div id="tallyVersion"><?php echo "Version: " . $version ?></div>
    </div>
    </body>
</html>

<?php

/*

$unique_coordinates = array_unique($coordinates, SORT_REGULAR);
echo "Latitude, Longitude\n";
foreach ($unique_coordinates as $coordinate) {
	echo $coordinate['latitude'] . ", " . $coordinate['longitude'] . "\n";

}

*/

function get($url) {
    error_log($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +tally' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $errorno = curl_errno($ch);
    curl_close($ch);
    if ($errorno > 0) {
        throw new Exception(curl_strerror($errorno));
    }
    return $data;
}
