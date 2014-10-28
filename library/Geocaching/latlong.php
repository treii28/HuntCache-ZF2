<?php
/**
 * File name or class: brief description goes here
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

// A Library of Latitude/Longitude functions for various calculations:

// A number of functions need to know the mean radius of the Earth for its
//  calculations. You need to set this constant to that value, in whatever
//  unit you wish the calculations to be carried out in.  For reference, it
//  is 6371m; however we will use its value in miles.
define('EARTH_R', 3956.09);

// Function: _deg2rad_multi
// Desc: A quick helper function.  Many of these functions have to convert
//   a value from degrees to radians in order to perform math on them.
function _deg2rad_multi() {
    // Grab all the arguments as an array & apply deg2rad to each element
    $arguments = func_get_args();
    return array_map('deg2rad', $arguments);
}

// Function: latlon_convert
// Function: latlon_convert
// Desc:  This is a conversion function to help transform more standard
//   notation coordinates into the form needed by these functions.  This
//   allows entry as separate Degree, Minute and Second.  It also accepts
//   a 'N', 'S', 'E', or 'W'  All parameters (except for degree) are
//   optional and can be floats.
//   You might enter 77 34 45.5, or 75 54.45644
function latlon_convert($degrees, $minutes = 0, $seconds = 0, $dir = '') {
    // Prepare the final value and keep adding to it:
    $final = $degrees;

    // Add in the minutes & seconds, properly converted to decimal values:
    // Uses the fact that there are 60 minutes in a degree,
    //  and 3600 seconds in a degree.
    $final += $minutes / 60.0;
    $final += $seconds / 3600.0;

    // If the direction is West or South, make sure this is negative
    //  in case someone forgot and put a -degree and said South:
    if (($dir == 'W') || ($dir == 'S')) {
        $final = abs($final) * -1.0;
    }

    return $final;
}

// Function: latlon_distance_great_circle
// Desc:  Calculate the shortest distance between two pairs of coordinates.
//   This calculates a great arc around the Earth, assuming that the Earth
//   is a sphere.  There is some error in this, as the earth is not
//   perfectly a sphere, but it is fairly accurate.
function latlon_distance_great_circle($lat_a, $lon_a, $lat_b, $lon_b) {
    // Convert our degrees to radians:
    list($lat1, $lon1, $lat2, $lon2) =
            _deg2rad_multi($lat_a, $lon_a, $lat_b, $lon_b);

    // Perform the formula and return the value
    return acos(
        ( sin($lat1) * sin($lat2) ) +
                ( cos($lat1) * cos($lat2) * cos($lon2 - $lon1) )
    ) * EARTH_R;
}

// Function: latlon_bearing_great_circle
// Desc:  This function calculates the initial bearing you need to travel
//   from Point A to Point B, along a great arc.  Repeated calls to this
//   could calculate the bearing at each step of the way.
function latlon_bearing_great_circle($lat_a, $lon_a, $lat_b, $lon_b) {
    // Convert our degrees to radians:
    list($lat1, $lon1, $lat2, $lon2) =
            _deg2rad_multi($lat_a, $lon_a, $lat_b, $lon_b);

    // Run the formula and store the answer (in radians)
    $rads = atan2(
        sin($lon2 - $lon1) * cos($lat2),
            (cos($lat1) * sin($lat2)) -
                    (sin($lat1) * cos($lat2) * cos($lon2 - $lon1)) );

    // Convert this back to degrees to use with a compass
    $degrees = rad2deg($rads);

    // If negative subtract it from 360 to get the bearing we are used to.
    $degrees = ($degrees < 0) ? 360 + $degrees : $degrees;

    return $degrees;
}

// Function: latlon_distance_rhumb
// Desc:  Calculates the distance between two points along a Rhumb line.
//   Rhumb lines are a line between two points that uses a constant
//   bearing.  They are slightly longer than a great circle path; however,
//   much easier to navigate.
function latlon_distance_rhumb($lat_a, $lon_a, $lat_b, $lon_b) {
    // Convert our degrees to radians:
    list($lat1, $lon1, $lat2, $lon2) =
            _deg2rad_multi($lat_a, $lon_a, $lat_b, $lon_b);

    // First of all if this a true East/West line there is a special case:
    if ($lat1 == $lat2) {
        $mid = cos($lat1);
    } else {
        $delta = log( tan(($lat2 / 2) + (M_PI / 4))
                / tan(($lat1 / 2) + (M_PI / 4)) );
        $mid = ($lat2 - $lat1) / $delta;
    }

    // Calculate difference in longitudes, and if over 180, go the other
    //  direction around the Earth as it will be a shorter distance:
    $dlon = abs($lon2 - $lon1);
    $dlon = ($dlon > M_PI) ? (2 * M_PI - $dlon) : $dlon;
    $distance = sqrt( pow($lat2 - $lat1,2) +
            (pow($mid, 2) * pow($dlon, 2)) ) * EARTH_R;

    return $distance;
}

// Function: latlon_bearing_rhumb
// Desc:  Calculates the bearing for the Rhumb line between two points.
function latlon_bearing_rhumb($lat_a, $lon_a, $lat_b, $lon_b) {
    // Convert our degrees to radians:
    list($lat1, $lon1, $lat2, $lon2) =
            _deg2rad_multi($lat_a, $lon_a, $lat_b, $lon_b);

    // Perform the math & store the values in radians.
    $delta = log( tan(($lat2 / 2) + (M_PI / 4))
            / tan(($lat1 / 2) + (M_PI / 4)) );
    $rads = atan2( ($lon2 - $lon1), $delta);

    // Convert this back to degrees to use with a compass
    $degrees = rad2deg($rads);

    // If negative subtract it from 360 to get the bearing we are used to.
    $degrees = ($degrees < 0) ? 360 + $degrees : $degrees;

    return $degrees;
}

/**
// Prepare for output
echo '<pre>';

// Use the conversion function to make two values decimal degree format.
$home_lat = latlon_convert(30, 25.773, 0, 'N');
$home_lon = latlon_convert(77, 06.272, 0, 'W');

// Echo them out, they should be:  Lat = 30.42955, Lon = -77.104533333333
echo "Converted Coordinates:  Lat = {$home_lat}, Lon = {$home_lon}\n\n";

// Prepare another set of coordinates.
$z_lat = 37.318776;
$z_lon = -122.008452;

// Calculate the great arc distance between these: 2540.3642964141 miles
$distance = latlon_distance_great_circle(
    $home_lat, $home_lon, $z_lat, $z_lon);
echo "Great Circle distance between 'home' and 'z': {$distance} miles\n\n";

// Calculate the initial bearing for the great arc: 292.92619009409 degrees
$bearing = latlon_bearing_great_circle(
    $home_lat, $home_lon, $z_lat, $z_lon);
echo "Great Circle bearing from 'home' to 'z': {$bearing} degrees\n\n";

// Calculate the distance if following a Rhumb line: 2561.6194141687 miles
$rdistance = latlon_distance_rhumb($home_lat, $home_lon, $z_lat, $z_lon);
echo "Rhumb Line distance between 'home' and 'z': {$rdistance} miles\n\n";

// Now calculate the bearing for this Rhumb line: 280.48113814226 degrees
$rbearing = latlon_bearing_rhumb($home_lat, $home_lon, $z_lat, $z_lon);
echo "Rhumb Line bearing from 'home' to 'z': {$rbearing} degrees\n\n";

 */
?>

