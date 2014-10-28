<?php

require_once('DataTypes.inc.php');

$nCoord = new Geo_Coordinate("84° 23' 25.7712345\" N","120° 17' 54.23283453\" W");
/**
$nLat = new Geo_Latitude('84.390492');
$nLon = new Geo_Longitude('120.298398W');
$nLat = new Geo_Latitude("84° 23' 25.7712\" N");
$nLon = new Geo_Longitude("120° 17' 54.2328\" W");

echo var_export($nLat,1)."\n";
echo var_export($nLon,1)."\n";

$latDMS = $nLat->getAsDMS();
$lonDMS = $nLon->getAsDMS();

echo $latDMS."\n";
echo $lonDMS."\n";
*/

echo var_export($nCoord,1)."\n";
printf("%s, %s\n", $nCoord->lat->getAsDMS(), $nCoord->lon->getAsDMS());