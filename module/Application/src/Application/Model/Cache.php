<?php
/**
 * Geocache class for PHP and Zend Framework
 *
 * Part of the OpenCacheFormat Documentation / Examples
 * http://opencacheformat.sourceforge.net
 *
 * @package Application\Model\Cache
 * @author Sascha Kimmel (converted to Zend by Scott Webster Wood)
 * @version 0.1
 * @copyright (c) 2006 Sascha Kimmel, tricos media (www.tricos.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
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

//require_once "Geocaching/OCFParser.inc.php";
//require_once "Geocaching/Waypoint.inc.php";

namespace Application\Model;

class Cache extends ModelAbstract implements Waypoint\CoordinateInterface,Sub\StateInterface,Sub\CountryInterface
{
    const PRIMARY_ID_KEY  = 'srcid';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'cache';
    protected static $_dataMapperName = 'CacheMapper';

    public static $VALID_CACHE_TYPES  = array('traditional','puzzle','multi','event','letterbox');
    public static $VALID_CACHE_SIZES  = array('micro','small','medium','large');
    public static $FORM_DEFAULTS = array(
        'type'       => 'traditional',
        'size'       => 'small',
        'countryId'  => 220,
        'difficulty' => 1,
        'terrain'    => 1
    );

    /**
     * OCF properties data
     * @var array  internal storage of OCF properties
     */
    protected static $_dataStruct = array(
        'srcid'			=> null,		'name'			=> null,
        'src'			=> null,		'lang'			=> null,
        'owner'			=> null,		'placed'		=> null,
        'homepage'		=> null,		'logurl'		=> null,
        'url'			=> null,		'type'			=> null,
        'size'			=> null,		'title'			=> null,
        'countryId'		=> null,		'stateId'		=> null,
        'city'			=> null,		'long'			=> null,
        'lat'			=> null,		'length'		=> null,
        'duration'		=> null,		'difficulty'	=> null,
        'terrain'		=> null,		'description'	=> null,
    );

    protected static $_dataMap = array(
        'src'			=> "src",
        'lang'			=> "lang",
        'owner'			=> "owner",
        'placed'		=> "placed",
        'homepage'		=> "homepage",
        'logurl'		=> "logurl",
        'url'			=> "url",
        'type'			=> "type",
        'size'			=> "size",
        'title'			=> "title",
        'city'			=> "city",
        'long'			=> "long",       // cacheLong
        'lat'			=> "lat",        // cacheLat
        'length'		=> "length",
        'duration'		=> "duration",
        'difficulty'	=> "difficulty",
        'terrain'		=> "terrain",
        'cdate'         => "cdate",
        'mdate'         => "mdate"
    );

    protected static $_subs = array(
        'state'   => "Application\Model\StateMapper",
        'country' => "Application\Model\CountryMapper"
    );

    protected $coordinate;

    protected $date;
    protected $version;

    public $state;
    public $country;

    public $cdate;
    public $mdate;

    /**
     * array of waypoint data
     * @var array
     */
    private $waypoints	= array();
    /**
     * array of applicable attributes
     * @var array
     */
    private $attributes	= array();

    /**
     * Error / status record keeping
     * @var string  last error message
     */
    public $_lastError;

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    /**
     * synonymous method with new/_init included for deprecated support
     * @param array|null $data
     */
    public function Geocache ($data=null)
    {
        $this->__construct($data);
    }

    /**
     * append a waypoint to the list of waypoints
     * @param array|Waypoint $data   array of key/value pairs or a Waypoint object
     */
    public function addWaypoint($data) {
        if($data instanceof Waypoint) {
            array_push($this->waypoints, $data);
        } elseif (is_array($data)) {
            $newWP = new Waypoint($data);
            array_push($this->waypoints, $newWP);
        }
        return $this->getWaypoints();
    }

    public function addAttribute($data) {
        if($data instanceof Attribute) {
            array_push($this->attributes, $data);
        } elseif (is_array($data)) {
            $newAttr = new Attribute($data);
            array_push($this->attributes, $newAttr);
        }
        return $this->getAttributes();
    }

    /**
     * Convert degrees to radians
     * @param float $deg
     * @return float   radian value
     */
    public function _deg2rad ($deg)
    {
        $radians = 0.0;
        $radians = $deg * M_PI / 180.0;
        return ($radians);
    }


    /**
     * Get waypoints as array of links directly to waypoint properties
     * @return array   links to elements of waypoint property array
     */
    public function getRealWaypoints ()
    {
        $realWaypoints = array();
        $wpnts = $this->waypoints;
        if (is_array($wpnts)) {
            if (sizeof($wpnts) > 1) {
                for ($i = 0; $i < sizeof($wpnts); $i ++) {
                    if ($wpnts[$i]->getPointType() == "parking") {
                        continue;
                    }
                    $realWaypoints[] = &$this->waypoints[$i];
                }
                return $realWaypoints;
            }
        }
        return false;
    }

    /**
     * Get distances between consecutive locations for instances with 2 or more waypoints
     * @param string $unit
     * @see Track_Leg::geoDistance()
     * @return array  distances between consecutive points based on unit
     *	(or false when less than 2 waypoints or unset)
     */
    public function getLinearDistances ($unit = "s")
    {
        $realWaypoints = $this->getRealWaypoints();
        if (is_array($this->waypoints)) {
            // Only use generic stations, i.e. do not calculate the distances between parking and the first real waypoint
            if (sizeof($realWaypoints) < 2) {
                return false;
            }
            $lengths = array();
            for ($i = 0; $i < sizeof($realWaypoints); $i++) {
                $coord = $realWaypoints[$i]->coordinate;
                $lastCoord = null;
                if ($i == 0) {
                    $lastCoord = $coord;
                    continue;
                }
                $length = Track_Leg::geoDistance($lastCoord,$coord,$unit);
                array_push($lengths,$length);
                $lastCoord = $coord;
            }
            return $lengths;
        } else {
            return false;
        }
    }

    /**
     * Calculate the total distance for a series of waypoints
     * @param string $unit
     * @see Track_Leg::geoDistance()
     * @return float  total distance based on unit
     */
    public function getHikeLength ($unit = "k")
    {
        if ($lengths = $this->getLinearDistances($unit)) {
            return array_sum($lengths);
        } else {
            return 0;
        }
    }

    // name must be under 6 characters in length
    public function setName($str) {
        $this->_data['name'] = substr((string)$str,0,6);
        return $this->_data['name'];
    }

    public function setType($str) {
        $str = (string)$str;
        if(in_array($str, self::$VALID_CACHE_TYPES)) {
            $this->_data['type'] = $str;
        } else {
            throw new \Exception(__METHOD__." invalid cache type '$str' specified");
        }
        return $this->_data['type'];
    }

    public function setSize($str) {
        $str = (string) $str;
        if(in_array($str, self::$VALID_CACHE_SIZES)) {
            $this->_data['size'] = $str;
        } else {
            throw new \Exception(__METHOD__." invalid cache size '$str' specified");
        }
        return $this->_data['size'];
    }

    public function setCountry($data=null) {
        $this->country = CountryMapper::autoDefine($data);
        $this->_data['countryId'] = $this->country->getId();
        return $this->country;
    }

    public function getCountry() {
        if(!($this->country instanceof Country) && (isset($this->_data['countryId']) && (intval($this->_data['countryId']) > 0))) {
            $this->setCountry(intval($this->_data['countryId']));
        }
        if($this->country instanceof Country) {
            return $this->country;
        } else {
            return false;
        }
    }

    public function setCountryById($id=null) {
        $this->country = CountryMapper::getCountryById($id);
        $this->_data['countryId'] = $this->country->getId();
        return $this->getCountryId();
    }

    public function getCountryId() {
        if($this->country instanceof Country) {
            return $this->country->getId();
        } elseif(isset($this->_data['countryId'])) {
            return $this->country->_data['countryId'];
        } else {
            return false;
        }
    }

    public function setCountryByName($name) {
        $this->country = CountryMapper::getCountryByName($name);
        $this->_data['countryId'] = $this->country->getId();
        return $this->getCountryName();
    }
    public function getCountryName() {
        if($this->country instanceof Country) {
            return $this->country->getName();
        } else {
            return false;
        }
    }

    public function setState($data) {
        $this->state = StateMapper::autoDefine($data);
        $this->_data['stateId'] = $this->state->getId();
        return $this->getState();
    }

    public function getState() {
        if(!($this->state instanceof State) && (isset($this->_data['stateId']) && (intval($this->_data['stateId']) > 0))) {
            $this->setState(intval($this->_data['stateId']));
        }
        if($this->state instanceof State) {
            return $this->state;
        } else {
            return false;
        }
    }

    public function setStateById($id) {
        $this->state = StateMapper::getStateById($id);
        $this->_data['stateId'] = $this->state->getId();
        return $this->getStateId();
    }

    public function getStateId() {
        if($this->state instanceof State) {
            return $this->state->getId();
        } elseif(isset($this->_data['stateId'])) {
            return $this->_data['stateId'];
        } else {
            return false;
        }
    }

    public function setStateByName($name) {
        $this->state = StateMapper::getStateByName($name);
        $this->_data['stateId'] = $this->state->getId();
        return $this->getStateName();
    }
    public function getStateName() {
        if($this->state instanceof State) {
            return $this->state->getName();
        } else {
            return false;
        }
    }

    public function setLength($num) {
        $num = intval($num);
        if($num>=0) {
            $this->_data['length'] = $num;
            return $this->_data['length'];
        } else {
            throw new \Exception(__METHOD__." length '$num' must be a positive integer");
        }
    }

    public function setDuration($num) {
        $num = intval($num);
        if($num>=0) {
            $this->_data['duration'] = $num;
            return $this->_data['duration'];
        } else {
            throw new \Exception(__METHOD__." duration '$num' must be a positive integer");
        }
    }

    public function setDifficulty($num) {
        $num = intval($num);
        if(($num>=0)&&($num<=10)) {
            $this->_data['difficulty'] = $num;
            return $this->_data['difficulty'];
        } else {
            throw new \Exception(__METHOD__." difficulty '$num' must be an integer from 0 to 10");
        }
    }

    public function setTerrain($num) {
        $num = intval($num);
        if(($num>=0)&&($num<=10)) {
            $this->_data['terrain'] = $num;
            return $this->_data['terrain'];
        } else {
            throw new \Exception(__METHOD__." terrain '$num' must be an integer from 0 to 10");
        }
    }

    public function setCoord($lat=null,$lon=null) {
        if((!($this->coordinate instanceof Waypoint\Coordinate)) || (isset($lat) && isset($lon))) {
            $this->coordinate = new Waypoint\Coordinate($lat,$lon);
        }
        $this->_data['lat']  = $this->coordinate->lat->val;
        $this->_data['long'] = $this->coordinate->lon->val;
        return $this->getCoord();
    }

    public function getCoord() {
        return $this->coordinate;
    }

    public function setLat($str) {
        if(!($this->coordinate instanceof Waypoint\Coordinate)) {
            $this->setCoord();
        }
        if($str instanceof Waypoint\Coordinate_Latitude) {
            $this->coordinate->lat = $str;
        } else {
            $this->coordinate->lat = new Waypoint\Coordinate_Latitude($str);
        }
        $this->_data['lat'] = $this->coordinate->lat->val;
        return $this->getLat();
    }

    public function getLat() {
        return $this->coordinate->lat->val;
    }

    public function setLong($str) {
        if(!($this->coordinate instanceof Waypoint\Coordinate)) {
            $this->coordinate = new Waypoint\Coordinate();
        }
        if($str instanceof Waypoint\Coordinate_Longitude) {
            $this->coordinate->lon = $str;
        } else {
            $this->coordinate->lon = new Waypoint\Coordinate_Longitude($str);
        }
        $this->_data['long'] = $this->coordinate->lon->val;
        return $this->getLong();
    }

    public function getLong() {
        return $this->coordinate->lon->val;
    }

    /**
     * get waypoints as track
     * @return Track
     */
    public function getAsTrack() {
        $nTrk = new Track();
        foreach($this->waypoints as $wp) {
            $nTrk->addWaypoint(new Waypoint\Coordinate($wp->getLat(),$wp->getLong()));
        }
        return $nTrk;
    }

    public function getTotalTrackDistance() {
        if(count($this->waypoints < 2)) {
            return 0;
        }
        $tTrk = $this->getAsTrack();
    }

    public static function _cleanCoord($str) {
        $str = str_replace(",",".",(string) $str);
        $str = preg_replace('/^\s*[\+]?([^\s]+)\s*$/', '$1', $str);
        return $str;
    }

    public static function _validCoord($str) {
        $valid = (boolean) preg_match("/^\s*[\-\+]?[0-9]{1,3}\.[0-9]+\s*$/", (string) $str);
        return $valid;
    }
}

