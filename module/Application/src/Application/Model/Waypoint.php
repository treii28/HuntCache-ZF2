<?php

/**
 *
 * Waypoint class for PHP
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

class Application\Model\Waypoint extends Application\Model\ModelAbstract implements Application\Model\Waypoint_CoordinateInterface
{
    const PRIMARY_ID_KEY   = 'waypointId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'waypoint';
    protected static $_dataMapperName = 'WaypointMapper';

    protected static $VALID_POINT_TYPES = array('cache', 'parking', 'puzzle');
    public static $FORM_DEFAULTS = array(
        'pointType'       => 'cache',
        'public'     => 1
    );

    protected $waypointId = null;

    public $coordinate;

    protected static $_dataStruct = array(
        'pointType'		=> null,
        'name'			=> null,
        'title'			=> null,
        'description'	=> null,
        'long'			=> null,
        'lat'			=> null,
        'public'		=> null,
        'hint'			=> null
    );

    protected static $_dataMap = array(
        'pointType'		=> "pointType",    // type
        'title'			=> "title",
        'waypointLong'	=> "long", // long
        'waypointLat'	=> "lat",  // lat
        'public'		=> "public",
        'hint'			=> "hint"
    );

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    /**
     * synonymous method with new/_init included for deprecated support
     * @param array|null $data
     */
    public function Waypoint ($data=null)
    {
        $this->__construct($data);
    }

    // override some default accessors for validation where necessary

    /**
     * Set accessor to set pointType and verify it as an accepted type
     * @param string $str
     * @throws Exception  if length of $str is not a valid type
     */
    public function setPointType ($str='cache')
    {
        $str = strtolower((string)$str);
        if (in_array($str, self::$VALID_POINT_TYPES)) {
            $this->_data['pointType'] = $str;
            return $this->_data['pointType'];
        } else {
            //$this->free();
            //return false;
            throw new \Exception(__METHOD__." invalid point type: $str");
        }
    }

    /**
     * Set accessor for name property
     * @param string $str
     * @throws Exception  if length of $str is longer than 6 characters
     */
    public function setName ($str)
    {
        $str = (string) $str;
        if (strlen($str) <= 6) {
            $this->_data['name'] = $str;
            return $this->_data['name'];
        } else {
            //$this->free();
            //return false;
            throw new \Exception(__METHOD__." name must be 6 characters or less: $str");
        }
    }

    /**
     * @param string||Application\Model\Waypoint_Coordinate_Latitude $str
     * @return string
     */
    public function setLat($str) {
        if(!($this->coordinate instanceof Application\Model\Waypoint_Coordinate)) {
            $this->coordinate = new Application\Model\Waypoint_Coordinate();
        }
        if($str instanceof Application\Model\Waypoint_Coordinate_Latitude) {
            $this->coordinate->lat = $str;
        } else {
            $this->coordinate->lat = new Application\Model\Waypoint_Coordinate_Latitude($str);
        }
        $this->_data['lat'] = $this->coordinate->lat->val;
        return $this->_data['lat'];
    }

    public function getLat() {
        return $this->coordinate->lat->val;
    }

    /**
     * @param string||Application\Model\Waypoint_Coordinate_Longitude $str
     * @return string
     */
    public function setLong($str) {
        if(!($this->coordinate instanceof Application\Model\Waypoint_Coordinate)) {
            $this->coordinate = new Application\Model\Waypoint_Coordinate();
        }
        if($str instanceof Application\Model\Waypoint_Coordinate_Longitude) {
            $this->coordinate->lon = $str;
        } else {
            $this->coordinate->lon = new Application\Model\Waypoint_Coordinate_Longitude($str);
        }
        $this->_data['long'] = $this->coordinate->lon->val;
        return $this->_data['long'];
    }

    public function getLong() {
        return $this->coordinate->lon->val;
    }

    public function setCoord($lat=null,$lon=null) {
        if((!($this->coordinate instanceof Application\Model\Waypoint_Coordinate)) || (isset($lat) && isset($lon))) {
            $this->coordinate = new Application\Model\Waypoint_Coordinate($lat,$lon);
        }
        $this->_data['lat']  = $this->coordinate->lat->val;
        $this->_data['long'] = $this->coordinate->lon->val;
        return $this->getCoord();
    }

    public function getCoord() {
        return $this->coordinate;
    }

    /**
     * Set accessor for public property that forces type boolean
     * @param boolean $bool
     */
    function setPublic ($bool)
    {
        // Force bool type
        $bool = (boolean) ($bool);
        $this->_data['public'] = $bool;
        return $this->_data['public'];
    }

    public static function getValidPointTypes() {
        return self::$VALID_POINT_TYPES;
    }
}

