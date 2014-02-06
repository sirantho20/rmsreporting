<?php
set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include ('lib/gMap.php');
include 'lib/geoSite.php';
$gsite = new geoSite('dump/bts.csv', 'dump/fgu.csv', '');
//print_r($gsite->populateSites());die();
$map = new gMap();
$map->setDivId('test1');
$map->setDirectionDivId ( 'route' );
$map->setCenter('Ghana');
$map->setSize('100%', '100%');
$map->setZoom(8);
$map->setLang('en');
$map->setDefaultHideMarker(false);
$map->addArrayMarkerByCoords($gsite->populateResults(), 'bts', 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|FFFF00');
$map->addArrayMarkerByCoords($gsite->populateSites(), 'sites', 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|0B610B');
//$map->addMarkerByCoords(6.905572, 0.3863167, 'Tafi Atome','<strong>Hello</strong>','http://maps.gstatic.com/intl/fr_fr/mapfiles/ms/micons/red-pushpin.png');
$map->generate();
echo  $map->getGoogleMap();


?>
<html>
    <head>

    </head>
    <body>
        <div id="test1">
            
        </div>
    </body>
</html>