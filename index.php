
<?php
/*******************************************************************************************/
/**
    \brief  Tomato Tally is based off BMLTTally but polls Tomato instead of individual roots.

            BMLTTally is a utility app that quickly polls a list of Root Servers, and displays their
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
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript">
            jQuery.extend(jQuery.fn.dataTableExt.oSort, {
                "reference-pre": function (ref) {
                    ref = ref.split(".");
                    ref = (parseInt(ref[0]) * 1000000) + (parseInt(ref[1]) * 1000) + (parseInt(ref[2]));
                    return parseInt(ref);
                }
            });

            jQuery(document).ready(function(){
                jQuery('#tallyHo').DataTable({
                    "bPaginate": false,
                    "bInfo" : false,
                    "bFilter": false,
                    columnDefs : [
                        { targets: 2, type: 'reference' }
                    ]
                });
            });
        </script>
        <link rel="shortcut icon" href="https://bmlt.app/wp-content/uploads/2014/11/FavIcon.png" type="image/x-icon" />
    </head>
    <body>
        <div id="tallyMan">
            <a href="https://bmlt.magshare.net"><img class="masthead" src="https://bmlt.app/wp-content/uploads/2014/01/cropped-BMLT-Blog-Logo1.png" /></a>
            <h2>Tally of Known BMLT Root Servers</h2>
            <div id="tallyLegend" style="display:  block;">
                <p id="tallyMo">*Bold green version number indicates server is suitable to use the <a href="https://itunes.apple.com/us/app/na-meeting-list-administrator/id1198601446">NA Meeting List Administrator</a> app.</p>
                <p id="tallyMo2">Remember that the server must be <a href="https://letsencrypt.org">SSL/HTTPS</a>, in addition to <a href="https://bmlt.app/semantic/semantic-administration/">Semantic Administration being enabled</a>.</p>
                <p id="tallyMo3">If <strong>BOTH</strong> of these conditions are not met, then you cannot use the admin app.</p>
                <div id="tallyMapButton"><a href="javascript:displayTallyMap();">Display Coverage Map</a></div>
            </div>
            <table id="tallyHo"  class="display" style="width:95%">
                <thead id="tallyHead">
                    <tr>
                        <td class="tallyName">Server Name</td>
                        <td id="tallySSL_Header"><a href="#">Is SSL?</a></td>
                        <td id="tallyVersion_Header"><a href="#">Version*</a></td>
                        <td id="tallyRegion_Header"><a href="#">Number of Regions</a></td>
                        <td id="tallyArea_Header"><a href="#">Number of Areas</a></td>
                        <td id="tallyMeeting_Header" class="selected"><a href="#">Number of Meetings</a></td>
                    </tr>
                </thead>
                <tbody id="tallyBody">
<?php
$root_server = "https://tomato.na-bmlt.org";

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
    $serverStatus = 'serverUp';
    /*
    try {
        $getRootServerInfo = get($rootServer['root_server_url'] . "client_interface/json/?switcher=GetServerInfo");
    } catch (Exception $e) {
        error_log('Caught exception: ' . $e->getMessage());
    }
    $rootServerInfo = json_decode($getRootServerInfo);
    if (strlen($rootServerInfo[0]->versionInt) <= '7') {
        $serverStatus = 'serverUp';
    } else {
        $serverStatus = 'serverDown';
    }
    error_log($serverStatus);
    */

    if ($isSSL && ($serverInfo[0]['versionInt'] >= 2008012) && $isAdminOn) {
        $validServer = 'validServer';
        $serversAdminApp++;
    } else {
        $validServer = 'invalidServer';
    }

    if ($isSSL) {
        $validSSL = 'validSSL';
    } else {
        $validSSL = 'inValidSSL';
    }

?>
                <tr>
                    <td class="tallyName <?php echo $serverStatus ?>"><a href="<?php echo $rootServer['root_server_url'] ?>" class="tallyClick" target="_blank"><?php echo $rootServer['name'] ?></a> (<a href="<?php echo $rootServer['root_server_url'] . "semantic" ?>" class="tallySemanticClick" target="_blank">Semantic Workshop Link</a>)</td>
                    <td class="tallySSL <?php echo $validSSL ?>"><?php echo $isSSLtxt ?></td>
                    <td class="tallyVersion tallyCoverage <?php echo $validServer ?> tallyCoverage"><?php echo $serverInfo[0]['version'] ?></td>
                    <td class="tallyRegion"><?php echo $rootServer['num_regions'] ?></td>
                    <td class="tallyArea"><?php echo $rootServer['num_areas'] ?></td>
                    <td class="tallyMeeting"><?php echo number_format($rootServer['num_meetings']) ?></td>
                </tr>
<?php 
} ?>
                <tfoot>
                <tr class="tallyTotal">
                    <td class="tallyName" colspan="3">TOTAL (<?php echo count($rootServers) ?> Servers, <?php echo $serversAdminApp ?> Can use the admin app)</td>
                    <td class="tallyRegion"><?php echo $totalRegions ?></td>
                    <td class="tallyArea"><?php echo number_format($totalAreas) ?></td>
                    <td class="tallyMeeting"><?php echo number_format($totalMeetings) ?></td>
                </tr>
                </tfoot>
                </tbody>
            </table>
            <div id="tallyVersion"><?php echo "Version: " . $version ?></div>
        </div>
        <div id="tallyMap" style="display: none"><?php include 'map.php'; ?></div>
        <script type="text/javascript" src="js/TomatoTally.js"></script>
    </body>
</html>

<?php

function get($url) {
    error_log($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +tally');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $errorno = curl_errno($ch);
    curl_close($ch);
    if ($errorno > 0) {
        throw new Exception(curl_strerror($errorno));
    }
    return $data;
}
