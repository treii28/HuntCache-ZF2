<?php
// example (self explained): how to use PHP Google Geocoder JSON Parser Lib v1
include('GGeocoderParserLib.v1.php');

// create an object to hold the Google Geocoder response 
$ggeo = get_ggeocoder_json('6 rue Grimaldi, 06000 NICE,FRANCE','','ro');

// some predefined fields parser is looking for
echo $ggeo->results['formatted_address'].'<br />';
echo $ggeo->results['latitude'].'<br />';
echo $ggeo->results['longitude'].'<br />';

// looking for a fields from address_components
$x=$ggeo->find_address_components('country');
echo '<pre>';print_r($x);echo '</pre>';

// see the object structure 
echo '<pre>';print_r($ggeo);echo '</pre>';
?>