<?php
	/**
 *
 * Geocache class for PHP
 * ----------------------
 *
 * Part of the OpenCacheFormat Documentation / Examples
 * http://opencacheformat.sourceforge.net
 *
 * Copyright (c) 2006 Sascha Kimmel, tricos media (www.tricos.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
*/

	require_once "Geocaching/OCFParser.inc.php";
	require_once "Geocaching/Waypoint.inc.php";

	class Geocache
	{
		var $srcid;
		var $name;
		var $src;
		var $lang;
		var $owner;
		var $placed;
		var $homepage;
		var $logurl;
		var $url;
		var $type;
		var $size;
		var $title;
		var $country;
		var $state;
		var $city;
		var $long;
		var $lat;
		var $length;
		var $duration;
		var $difficulty;
		var $terrain;
		var $description;
		var $waypoints;
		var $attributes;

		var $fields=array('srcid', 'name', 'src', 'lang', 'owner', 'placed', 'homepage', 'logurl', 'url', 'type', 'size', 'title', 'country', 'state', 'city', 'long', 'lat', 'length', 'duration', 'difficulty', 'terrain', 'description', 'waypoints', 'attributes');

		var $ocfAttributes=array('wheelchair', 'camping', 'parking', 'publictransport', 'picnictables', 'drinkingwater', 'restrooms', 'telephone', 'stroller', 'museum', 'restaurant', 'night', 'winter', 'scenic','stealth', 'rocks', 'hunting', 'danger', 'plants', 'thorns', 'snakes', 'ticks', 'mine', 'dogs', 'bicycles', 'motorcycles','quads', 'offroad', 'snowmobiles', 'campfires', 'horses', 'fee', 'climbing', 'boat', 'scuba', 'kids', 'short', 'hike','climbing', 'wading', 'swimming', '247');

		var $lastError;

		function Geocache() { $this->__construct(); }

		function __construct() {}

		function free()
		{
			foreach($this->fields AS $field)
			{
				$this->$field=null;
			}
		}
		function loadCacheFromOCF($xmlFile, $cacheToLoad=0)
		{
			$this->free();

			$parser=new OCFParser();
			$ocfData=$parser->parse($xmlFile);

			if($ocfData)
			{
				if(isset($ocfData["caches"])&&isset($ocf["caches"][$cacheToLoad])&&$cache=$ocfData["caches"][$cacheToLoad]) {

					if($cache["srcid"]) $this->srcid=$cache["srcid"];

					// Shorten the string to 6 characters if required
					if($cache["name"]) {
						if(strlen($cache["name"]) > 6) {
							$this->name=substr($cache["name"], 0, 6);
						} else {
							$this->name=$cache["name"];
						}
					}

					if($cache["src"]) {
						$this->src=$cache["src"];
					}

					if($cache["lang"]) {
						$this->lang=$cache["lang"];
					}

					if($cache["owner"]) {
						$this->owner=$cache["owner"];
					}

					// It is a timestamp
					if($cache["placed"]) {
						$this->placed=$cache["placed"];
					}

					if($cache["homepage"]) {
						$this->homepage=$cache["homepage"];
					}

					if($cache["logurl"]) {
						$this->logurl=$cache["logurl"];
					}

					if($cache["url"]) {
						$this->url=$cache["url"];
					}

					if($cache["type"]) {
						$compareType=strtolower(trim($cache["type"]));
						if(in_array($compareType, array('traditional', 'puzzle', 'multi', 'event', 'letterbox'))) {
							$this->type=$compareType;
						} else {
							// Invalid type in OCF code
							$this->free();
							$this->lastError="Invalid cache type in OCF: ".$compareType;
							return false;
						}
					}

					if($cache["size"]) {
						$compareSize=strtolower(trim($cache["size"]));
						if(in_array($compareSize, array('micro', 'small', 'medium', 'large'))) {
							$this->size=$compareSize;
						} else {
							// Invalid size in OCF code
							$this->free();
							$this->lastError="Invalid cache size in OCF: ".$compareSize;
							return false;
						}
					}

					if($cache["title"]) {
						$this->title=$cache["title"];
					}

					if($cache["country"]) {
						$compareCountry=strtolower(trim($cache["country"]));
						if(strlen($compareCountry) == 2) {
							$this->country=$compareCountry;
						} else {
							// Invalid country
							$this->free();
							$this->lastError="Invalid country in OCF, must only have 2 letters: ".$compareCountry;
							return false;
						}
					}

					if($cache["state"]) {
						$this->state=$cache["state"];
					}

					if($cache["city"]) {
						$this->city=$cache["city"];
					}

					if($cache["long"]) {
						// Officially it is not allowed to use a comma, however we catch this here anyways
						if(strstr($cache["long"], ",")) {
							$cache["long"]=str_replace(",", ".", $cache["long"]);
						}
						if(preg_match("/^[0-9]{1,3}\.[0-9]+$/", $cache["long"])) {
							$this->long=$cache["long"];
						} else {
							// Invalid longitude value, must be decimal
							$this->free();
							$this->lastError="Invalid longitude in OCF, must be decimal: ".$cache["long"];
							return false;
						}
					}

					if($cache["lat"]) {
						// Officially it is not allowed to use a comma, however we catch this here anyways
						if(strstr($cache["lat"], ",")) {
							$cache["lat"]=str_replace(",", ".", $cache["lat"]);
						}
						if(preg_match("/^[0-9]{1,3}\.[0-9]+$/", $cache["lat"])) {
							$this->lat=$cache["lat"];
						} else {
							// Invalid latitude value, must be decimal
							$this->free();
							$this->lastError="Invalid latitude in OCF, must be decimal: ".$cache["lat"];
							return false;
						}
					}

					if($cache["length"]) {
						if(is_int($cache["length"])) {
							$this->length=$cache["length"];
						} else {
							// Invalid value
							$this->free();
							$this->lastError="Invalid length, must be integer: ".$cache["length"];
							return false;
						}
					}

					if($cache["duration"]) {
						if(is_int($cache["duration"])) {
							$this->duration=$cache["duration"];
						} else {
							// Invalid value
							$this->free();
							$this->lastError="Invalid duration, must be integer: ".$cache["duration"];
							return false;
						}
					}

					if(isset($cache["difficulty"])) {
						if(is_int($cache["difficulty"])) {
							if($cache["difficulty"] >= 0 && $cache["difficulty"] <= 10) {
								$this->difficulty=$cache["difficulty"];
							} else {
								// Invalid value, too large
								$this->free();
								$this->lastError="Invalid difficulty, must be between 0-10: ".$cache["difficulty"];
								return false;
							}
						} else {
							// Invalid value
							$this->free();
							$this->lastError="Invalid difficulty, must be an integer value between 0-10: ".$cache["difficulty"];
							return false;
						}
					}

					if(isset($cache["terrain"])) {
						if(is_int($cache["terrain"])) {
							if($cache["terrain"] >= 0 && $cache["terrain"] <= 10) {
								$this->terrain=$cache["terrain"];
							} else {
								// Invalid value, too large
								$this->free();
								$this->lastError="Invalid terrain, must be between 0-10: ".$cache["terrain"];
								return false;
							}
						} else {
							// Invalid value
							$this->free();
							$this->lastError="Invalid terrain, must be an integer value between 0-10: ".$cache["difficulty"];
							return false;
						}
					}

					if($cache["description"]) {
						$this->description=$cache["description"];
					}

					if(is_array($cache["waypoints"])) {

						$waypoints=array();
						foreach($cache["waypoints"] AS $waypoint)
						{
							$wp=new Waypoint();
							$wp->setPointType($waypoint["pointtype"]);
							$wp->setName($waypoint["name"]);
							if($waypoint["title"]) $wp->setTitle($waypoint["title"]);
							if($waypoint["description"]) $wp->setDescription($waypoint["description"]);
							$wp->setCoordinates($waypoint["long"], $waypoint["lat"]);
							if($waypoint["public"]) $wp->setPublic($waypoint["public"]);
							if($waypoint["hint"]) $wp->setHint($waypoint["hint"]);

							// Add to our reference array
							$waypoints[]=&$wp;
						}

						$this->waypoints=$waypoints;
						unset($waypoints);
					}
					if(is_array($cache["attributes"])) {

						$attributes=array();

						while(list($name, $value) = each($cache["attributes"]))
						{
							$name=strtolower($name);
							if(in_array($name, $this->ocfAttributes)) {
								$attributes[$name]=$value;
							} else {
								// Invalid attribute
								$this->free();
								$this->lastError="Invalid attribute not allowed in OCF 1.0: ".$cache["name"];
								return false;
							}
						}

						$this->attributes=$attributes;
						unset($attributes);
					}

					// Import successful
					return true;

				} else {
					// Cache to be loaded was not found!
					$this->lastError="Cache with offset $cacheToLoad was not found in the file";
					return false;
				}
			} else {
				// OCF data could not be parsed!
				if(!$this->lastError) $this->lastError="OCF data could not be parsed";
				return false;
			}
		}
		function getGPX($insertAllWaypoints=false)
		{
			// Check required fields
			if(!($this->name && $this->url && $this->lat && $this->long && $this->title)) {
				$this->lastError="getGPX requires that at least the following values are set: name, url, lat, long and title";
				return false;
			}

			$gpx='<gpx version="1.1" creator="OCFConverter">
<metadata>
	<desc>'.$this->xmlPrep($this->name).'</desc>
	<link href="'.$this->xmlPrep($this->url).'"/>
	<keywords>geocache</keywords>
</metadata>
';

if($insertAllWaypoints) {
	foreach($this->waypoints AS $wp) {
		list($long, $lat)=$wp->getCoordinates();
		$gpx.='<wpt lat="'.$lat.'" long="'.$long.'">
	<name>'.$this->xmlPrep($wp->getName()).'</name>
	<desc>'.$this->xmlPrep($wp->getDescription()).'</desc>
	<sym>GEOCACHE</sym>
	<type>GEOCACHE</type>
</wpt>
';
	}
} else {
	$gpx.='<wpt lat="'.$this->lat.'" long="'.$this->long.'">
	<name>'.$this->xmlPrep($this->name).'</name>
	<desc>'.$this->xmlPrep($this->title).'</desc>
	<sym>GEOCACHE</sym>
	<type>GEOCACHE</type>
</wpt>
';
}

$gpx.='
</gpx>';
		return $gpx;

		}
		function getKML($height=50)
		{
			// Check required fields
			if(!($this->name && $this->lat && $this->long && $this->title)) {
				$this->lastError="getKML requires that at least the following values are set: name, lat, long and title";
				return false;
			}

			$kml='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">
<Placemark>
  <description>'.$this->xmlPrep($this->title).'</description>
  <name>'.$this->xmlPrep($this->name).'</name>
  <LookAt>
	<longitude>'.$this->long.'</longitude>
	<latitude>'.$this->lat.'</latitude>
  </LookAt>
  <visibility>0</visibility>
  <Point>
	<altitudeMode>relativeToGround</altitudeMode>
	<coordinates>'.$this->long.','.$this->lat.','.(int)$height.'</coordinates>
  </Point>
</Placemark>
</kml>';

			return $kml;
		}
		function getLOC()
		{
			// Check required fields
			if(!($this->name && $this->url && $this->lat && $this->long && $this->title)) {
				$this->lastError="getLOC requires that at least the following values are set: name, url, lat, long and title";
				return false;
			}

			$loc='<?xml version="1.0" encoding="UTF-8"?>
<loc version="1.0" src="OCF">
<waypoint>
	<name id="'.$this->name.'">'.$this->xmlPrep($this->title).'</name>
	<coord lat="'.$this->lat.'" lon="'.$this->long.'"/>
	<type>Geocache</type>
	<link text="Cache Details">'.$this->xmlPrep($this->url).'</link>
</waypoint>
</loc>';

			return $loc;
		}
		function getOCF($encoding='iso-8859-1', $lbr="\n")
		{
			$xml='<?xml version="1.0" encoding="'.$encoding.'"?>'.$lbr;
			$xml.='<ocf xmlns="http://opencacheformat.sourceforge.net/xsd/1.0">'.$lbr;
			$xml.='	<version>1.0</version>'.$lbr;
			$xml.='	<date>'.gmdate("Y-m-d H:i:s").'</date>'.$lbr;
			$xml.='	<caches>'.$lbr;
			$xml.='		<cache>'.$lbr;
			if(isset($this->srcid)) {
				$xml.='			<srcid>'.$this->xmlPrep($this->srcid).'</srcid>'.$lbr;
			}
			$xml.='			<name>'.$this->xmlPrep($this->name).'</name>'.$lbr;
			$xml.='			<src>'.$this->xmlPrep($this->src).'</src>'.$lbr;
			$xml.='			<lang>'.$this->xmlPrep($this->lang).'</lang>'.$lbr;

			if(isset($this->owner)) {
				$xml.='			<owner>'.$this->xmlPrep($this->owner).'</owner>'.$lbr;
			}

			if(isset($this->placed)) {
				$xml.='			<placed>'.$this->xmlPrep($this->placed).'</placed>'.$lbr;
			}

			if(isset($this->homepage)) {
				$xml.='			<homepage>'.$this->xmlPrep($this->homepage).'</homepage>'.$lbr;
			}

			if(isset($this->logurl)) {
				$xml.='			<logurl>'.$this->xmlPrep($this->logurl).'</logurl>'.$lbr;
			}

			if(isset($this->url)) {
				$xml.='			<url>'.$this->url.'</url>'.$lbr;
			}

			$xml.='			<type>'.strtolower($this->type).'</type>'.$lbr;
			$xml.='			<size>'.strtolower($this->size).'</size>'.$lbr;
			$xml.='			<title>'.$this->xmlPrep($this->name).'</title>'.$lbr;

			if(isset($this->country)) {
				$xml.='			<country>'.$this->country.'</country>'.$lbr;
			}

			if(isset($this->state)) {
				$xml.='			<state>'.$this->xmlPrep($this->state).'</state>'.$lbr;
			}

			if(isset($this->city)) {
				$xml.='			<city>'.$this->xmlPrep($this->city).'</city>'.$lbr;
			}

			$xml.='			<long>'.$this->xmlPrep($this->long).'</long>'.$lbr;
			$xml.='			<lat>'.$this->xmlPrep($this->lat).'</lat>'.$lbr;

			if(isset($this->length)) {
				$xml.='			<length>'.$this->xmlPrep($this->length).'</length>'.$lbr;
			}

			if(isset($this->duration)) {
				$xml.='			<duration>'.$this->duration.'</duration>'.$lbr;
			}

			if(isset($this->difficulty)) {
				$xml.='			<difficulty>'.$this->difficulty.'</difficulty>'.$lbr;
			}

			if(isset($this->terrain)) {
				$xml.='			<terrain>'.$this->terrain.'</terrain>'.$lbr;
			}

			if(isset($this->description)) {
				$xml.='			<description>'.$this->xmlPrep($this->description).'</description>'.$lbr;
			}

			$xml.='			<waypoints>'.$lbr;

			if(is_array($this->waypoints)) {
				foreach($this->waypoints AS $wp)
				{
					if($wp->public)
					{
						$public="true";
					} else {
						$public="false";
					}
					$xml.='			<waypoint>'.$lbr;
					$xml.='				<pointtype>'.strtolower($wp->pointtype).'</pointtype>'.$lbr;
					$xml.='				<name>'.$this->xmlPrep($wp->name).'</name>'.$lbr;
					$xml.='				<title>'.$this->xmlPrep($wp->title).'</title>'.$lbr;

					if(isset($this->description)) {
						$xml.='				<description>'.$this->xmlPrep($wp->description).'</description>'.$lbr;
					}

					$xml.='				<long>'.$this->xmlPrep($wp->long).'</long>'.$lbr;
					$xml.='				<lat>'.$this->xmlPrep($wp->lat).'</lat>'.$lbr;

					if(isset($this->public)) {
						$xml.='				<public>'.$public.'</public>'.$lbr;
					}

					if(isset($this->hint)) {
						$xml.='				<hint>'.$this->xmlPrep($wp->hint).'</hint>'.$lbr;
					}

					$xml.='			</waypoint>'.$lbr;
			}
		}

		$xml.='		</waypoints>'.$lbr;
		$xml.='		<attributes>'.$lbr;

		if(is_array($this->attributes)) {
			while(list($n, $v) = each($this->attributes))
			{
					$xml.='			<attribute name="'.$n.'" value="'.$v.'"/>'.$lbr;
			}
		}

			$xml.='		</attributes>'.$lbr;
			$xml.='		</cache>'.$lbr;
			$xml.='	</caches>'.$lbr;
			$xml.='</ocf>'.$lbr;
			return $xml;
		}
		function sendLOC($filename="geocache.loc")
		{
			if($code=$this->getLOC()) {
				$this->_sendFileToBrowser($code, "application/xml-loc", $filename);
				return true;
			} else {
				return false;
			}
		}
		function sendGPX($filename="geocache.gpx")
		{
			if($code=$this->getGPX()) {
				$this->_sendFileToBrowser($code, "application/x-gps", $filename);
				return true;
			} else {
				return false;
			}
		}
		function sendKML($filename="geocache.kml")
		{
			if($code=$this->getGPX()) {
				$this->_sendFileToBrowser($code, "application/vnd.google-earth.kml+xml", $filename);
				return true;
			} else {
				return false;
			}
		}
		function sendOCF($filename="geocache.ocf")
		{
			if($code=$this->getLOC()) {
				$this->_sendFileToBrowser($code, "application/xml-ocf", $filename);
				return true;
			} else {
				return false;
			}
		}
		function _sendFileToBrowser($content, $mimeType, $filename)
		{
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; filename=".$filename);
			header("Cache-Control: no-cache");
			header("Expires: -1");
			header("Content-Type: ".$mimeType);
			echo $content;
		}
		function xmlPrep($str)
		{
			if(strstr($str, "<") || strstr($str, ">") || strstr($str, "&") || strstr($str, "'") || strstr($str, "\"")) {
				return "<![CDATA[".$str."]]>";
			}
			return $str;
		}
		function _deg2rad($deg)
		{
			$radians = 0.0;
			$radians = $deg * M_PI/180.0;
			return($radians);
		}
		function geoDistance($lat1, $lon1, $lat2, $lon2, $unit="k")
		{
			$theta = $lon1 - $lon2;
			$dist = sin($this->_deg2rad($lat1)) * sin($this->_deg2rad($lat2)) + cos($this->_deg2rad($lat1)) * cos($this->_deg2rad($lat2)) * cos($this->_deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtolower($unit);

			if ($unit == "k") {
				return ($miles * 1.609344);
			} else {
				return $miles;
			}
		}
		function getRealWaypoints()
		{
			$realWaypoints=array();
			if(is_array($this->waypoints)) {
				if(sizeof($this->waypoints) > 1) {
					for($i=0; $i<sizeof($this->waypoints); $i++)
					{
						if($this->waypoints[$i]->getPointType() == "parking") {
							continue;
						}
						$realWaypoints[]=&$this->waypoints[$i];
					}
					return $realWaypoints;
				}
			}
			return false;
		}
		function getLinearDistances($unit="k")
		{
			$realWaypoints=$this->getRealWaypoints();

			if(is_array($this->waypoints)) {

				// Only use generic stations, i.e. do not calculate the distances between parking and the first real waypoint
				if(sizeof($realWaypoints) < 2) {
					return 0;
				}

				$lengths=array();
				$lastLong = null;
				$lastLat  = null;
				for($i=0; $i<sizeof($realWaypoints); $i++)
				{
					list($long, $lat)=$realWaypoints[$i]->getCoordinates();

					if($i == 0) {
						$lastLong=$long;
						$lastLat=$lat;
						continue;
					}

					$length=$this->geoDistance($lastLat, $lastLong, $lat, $long, $unit);
					$lengths[]=$length;
					$lastLat=$lat;
					$lastLong=$long;
				}
				return $lengths;
			} else {
				return false;
			}
		}
		function getHikeLength($unit="k")
		{
			if($lengths=$this->getLinearDistances($unit)) {
				return array_sum($lengths);
			} else {
				return 0;
			}
		}

	}

?>