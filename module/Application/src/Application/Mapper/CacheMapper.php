<?php
/**
 * Geocache class Mapper for PHP and Zend Framework
 *
 * Part of the OpenCacheFormat Documentation / Examples
 * http://opencacheformat.sourceforge.net
 *
 * @package Application\Model\CacheMapper
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

class Application\Model\CacheMapper extends Application\Model\MapperAbstract
{
    const DB_TABLE_NAME = 'caches';
    const MODEL_NAME	= 'Application\Model\Cache';
    protected static $_dbTable = null;

    const DEFAULT_LINE_BREAK = "\n";
    private static $lbr = self::DEFAULT_LINE_BREAK;

    public function __construct() {
        parent::__construct();
    }

    private static function _lbc($chr=self::DEFAULT_LINE_BREAK)
    {
        $chr = (string) $chr;
        if($chr !== '') {
            self::$lbr = $chr;
        }
        return self::$lbr;
    }

    /**
     * @static
     * @param bool $subs
     * @return int|mixed|void
     * @throws \Exception
     */
    public static function save($subs=true) {
        $subs = (boolean) ($subs);
        $data = $ocm->getData();
        $rows = 0;

        if(isset($data['srcid'])) {
            if($data['srcid']>0) {
                if(self::cacheExists($data['srcid'])) {
                    // update record
                    $data['mdate'] = date( 'Y-m-d H:i:s', time());
                    $rows = self::_getDbTable(__CLASS__)->update(
                        $data,
                        array('srcid = ?' => $data['srcid'])
                    );
                }
            } else {
                throw new \Exception(__METHOD__." srcid must be a positive integer: ".$data['srcid']);
            }
        } else {
            // insert a new record
            $data['cdate'] = date( 'Y-m-d H:i:s', time());
            $data['mdate'] = date( 'Y-m-d H:i:s', time());
            $rows = self::_getDbTable()->insert($data);
        }
        if($subs) {
            foreach($ocm->getWaypoints() as $wpt) {
                $waypointMapper = new Application\Model\WaypointMapper();
                $rows += $waypointMapper->save($wpt);
            }
            foreach($ocm->getAttributes() as $attr) {
                $attrTable = new Zend_Db_Table('cacheAttributes');
                // TODO need to save attribute links once attributes table is built and populated
            }
        }
    }

    /**
     * checks to see if records for a given id exist in the database
     * @static
     * @param Application\Model\Cache $data
     * @return boolean  returns true if record of id exists
     * @throws \Exception
     */
    public static function cacheExists(Application\Model\Cache $data) {
        $model = self::_getDataType();
        $idName = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idName);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $rec = self::getCacheByName($data->getName());
            return (boolean) ($rec instanceof $model);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    /**
     * Retrieve a cache by primary id
     * @param integer $id
     * @throws Exception if not found
     * @return Application\Model\Attribute
     */
    public static function getCacheById($id=null) {
        $id = intval($id);
        if(!($id>0)) {
            $id = self::DEFAULT_ID;
        }

        return parent::getModelById($id);
    }

    public static function getCacheByName($name) {
        $name = (string) $name;
        if($name != '') {
            $table = self::_getDbTable();
            $where = $table->getAdapter()->quoteInto('name = ?', $name);
            $rs = $table->fetchAll(
                $table->select()
                        ->where($where)
            );

            if(count($rs)==1) {
                $row = $rs->current();
                return self::_createModelFromRow($row);
            } else {
                return false;
                //throw new \Exception(__METHOD." row not found for name = '$name'");
            }
        } else {
            throw new \Exception(__METHOD__." name '$name' must be string");
        }
    }

    // redundant method
    /**
     * retrieve all attributes as array of cache names
     * @return array
     */
    public static function getCacheList() {
        $model = self::_getDataType();
        $LblNm   = $model::_getLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($LblNm)
        );
        $labels = array();
        foreach($rs as $row) {
            array_push($labels, $row->$LblNm);
        }
        return $labels;
    }

    /**
     * @static
     * @return array
     */
    public static function getCacheNames() {
        $model = self::_getDataType();
        $pId   = $model::_getPrimaryIdKey();
        $LblNm = $model::_getLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($pId,$LblNm)
        );
        $names = array();
        foreach ($rs as $_r) {
            $names[$_r->$pId] = $_r->$LblNm;
        }
        return $names;
    }

    /**
     * create a Cache model by parsing OCF formated XML data
     * @param Application\Model\Cache $ocm
     * @param string $encoding
     */
    public static function parseOCF ($xmlFile, $cacheToLoad = 0)
    {
        if(!file_exists($xmlFile)) {
            throw new \Exception(__METHOD__." file '$xmlFile' does not exist");
        }
        $ocmData = simplexml_load_file($xmlFile);

        if (!($ocmData instanceof SimpleXMLElement)) {
            throw new \Exception(__METHOD__." simplexml was not able to parse '$xmlFile'");
        }

        $caches = array();
        if (isset($ocmData->caches)&&isset($ocmData->caches->cache)) {
            if(is_array($ocmData->caches->cache)) {
                foreach($ocmData->caches->cache as $cache) {
                    array_push($caches, self::_parseOCFCaches($cache));
                }

            } else {
                array_push($caches, self::_parseOCFCaches(array($ocmData->caches->cache)));
            }
        }
        return $caches;
    }
    private static function _parseOCFCaches($caches) {
        $cacheModels = array();
        foreach($caches as $cache) {
            $ocm = new Application\Model\Cache();
            foreach (
                array(
                    'srcid','src','lang','owner','placed','homepage','logurl','url','title','state','city','description',
                    'name','country','lat','long','length','duration','difficulty','terrain'
                ) as $_m) {
                if (isset($cache->$_m)) {
                    $setMethod = 'set'.ucfirst($_m);
                    $ocm->$setMethod($cache->$_m);
                }
            }
            // manipulate some settings format
            foreach(array('type','size') as $_m) {
                if(isset($cache->$_m)) {
                    $setMethod = 'set'.ucfirst($_m);
                    $_lt = strtolower(trim($cache->$_m));
                    $ocm->$setMethod($_lt);
                }
            }

            if(isset($cache->waypoints)&&isset($cache->waypoints->waypoint)) {
                if(is_array($cache->waypoints->waypoint)) {
                    foreach($cache->waypoints->waypoint as $_wp) {
                        $ocm->addWaypoint(self::_parseOCFWaypoint($_wp));
                    }
                } else {
                    $ocm->addWaypoint(self::_parseOCFWaypoint($cache->waypoints->waypoint));
                }
            }

            if (isset($cache->attributes)&&isset($cache->attributes->attribute)) {
                if(is_array($cache->attributes->attribute)) {
                    foreach($cache->attributes->attribute as $attribute) {
                        $cache->addAttribute(new Application\Model\Attribute($attribute->attributes()));
                    }
                } else {
                    $cache->addAttribute(new Application\Model\Attribute($cache->attributes->attribute));
                }
            }
            array_push($cacheModels, $cache);
        }
        return $cacheModels;
    }

    public static function _parseOCFWaypoint($waypoint) {
        $wp = new Application\Model\Waypoint();
        foreach(array('pointtype','name','title','description','public','hint','long','lat') as $_m) {
            $method = 'set'.ucfirst($_m);
            if(isset($waypoint->$_m)) {
                $wp->$method($waypoint->$_m);
            }
        }
        return $wp;
    }

    /**
     * get GPX formatted XML content from an existing Cache model instance
     * @static
     * @param Application\Model\Cache $ocm
     * @param bool $insertAllWaypoints
     * @return bool|string
     */
    public static function getGPX (Application\Model\Cache $ocm, $insertAllWaypoints = false)
    {
        // Check required fields
        if (! ($ocm->getName() && $ocm->getUrl() && $ocm->getLat() && $ocm->getLong() &&
                $ocm->getTitle())) {
            $ocm->_lastError = "getGPX requires that at least the following values are set: name, url, lat, long and title";
            return false;
        }
        $gpx = '<gpx version="1.1" creator="OCFConverter">
<metadata>
	<desc>' . self::xmlPrep($ocm->getName()) . '</desc>
	<link href="' . self::xmlPrep( $ocm->getUrl() ) . '"/>
	<keywords>geocache</keywords>
</metadata>
';
        if ($insertAllWaypoints) {
            foreach ( $ocm->getWaypoints()  as $wp) {
                $lat = $wp->coordinate->lat->val;
                $long = $wp->coordinate->lon->val;
                $gpx .= '<wpt lat="' . $lat . '" long="' . $long . '">
	<name>' .
                        self::xmlPrep($wp->getName()) . '</name>
	<desc>' .
                        self::xmlPrep($wp->getDescription()) . '</desc>
	<sym>GEOCACHE</sym>
	<type>GEOCACHE</type>
</wpt>
';
            }
        } else {
            $gpx .= '<wpt lat="' . $ocm->getLat() . '" long="' . $ocm->getLong() . '">
	<name>' . self::xmlPrep($ocm->getName()) . '</name>
	<desc>' .
                    self::xmlPrep($ocm->getTitle()) . '</desc>
	<sym>GEOCACHE</sym>
	<type>GEOCACHE</type>
</wpt>
';
        }
        $gpx .= '
</gpx>';
        return $gpx;
    }

    /**
     * get KML formatted XML content from an existing Cache model instance
     * @param Application\Model\Cache $ocm
     * @param int $height
     */
    public static function getKML (Application\Model\Cache $ocm, $height = 50)
    {
        // Check required fields
        if (! ($ocm->getName() && $ocm->getLat() && $ocm->getLong() && $ocm->getTitle())) {
            $ocm->_lastError = "getKML requires that at least the following values are set: name, lat, long and title";
            return false;
        }
        $kml = '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.0">
<Placemark>
  <description>' .
                self::xmlPrep($ocm->getTitle()) . '</description>
  <name>' . self::xmlPrep($ocm->getName()) . '</name>
  <LookAt>
	<longitude>' . $ocm->getLong() . '</longitude>
	<latitude>' . $ocm->getLat() . '</latitude>
  </LookAt>
  <visibility>0</visibility>
  <Point>
	<altitudeMode>relativeToGround</altitudeMode>
	<coordinates>' .
                $ocm->getLong() . ',' . $ocm->getLat() . ',' . intval($height) . '</coordinates>
  </Point>
</Placemark>
</kml>';
        return $kml;
    }

    /**
     * get LOC formatted XML content from an existing Cache model instance
     * @param Application\Model\Cache $ocm
     */
    public static function getLOC (Application\Model\Cache $ocm)
    {
        // Check required fields
        if (! ($ocm->getName() && $ocm->getUrl() && $ocm->getLat() && $ocm->getLong() &&
                $ocm->getTitle())) {
            $ocm->_lastError = "getLOC requires that at least the following values are set: name, url, lat, long and title";
            return false;
        }
        $loc = '<?xml version="1.0" encoding="UTF-8"?>
<loc version="1.0" src="OCF">
<waypoint>
	<name id="' .
                $ocm->getName() . '">' . self::xmlPrep($ocm->getTitle()) . '</name>
	<coord lat="' . $ocm->getLat() . '" lon="' .
                $ocm->getLong() . '"/>
	<type>Geocache</type>
	<link text="Cache Details">' .
                self::xmlPrep($ocm->getUrl()) . '</link>
</waypoint>
</loc>';
        return $loc;
    }

    /**
     * get OCF formatted XML content from an existing Cache model instance
     * @param Application\Model\Cache $ocm
     * @param string $encoding
     */
    public static function getOCF (Application\Model\Cache $ocm, $encoding = 'iso-8859-1')
    {
        $LF = self::_lbc();
        $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>' . $LF;
        $xml .= '<ocf xmlns="http://opencacheformat.sourceforge.net/xsd/1.0">' . $LF;
        $xml .= '	<version>1.0</version>' . $LF;
        $xml .= '	<date>' . gmdate("Y-m-d H:i:s") . '</date>' . $LF;
        $xml .= '	<caches>' . $LF;
        $xml .= '		<cache>' . $LF;
        if ($ocm->getId()>0) {
            $xml .= '			<srcid>' . self::xmlPrep($ocm->getSrcid()) . '</srcid>' . $LF;
        }

        foreach(
            array(
                'name','src','lang','type','size','type','size','title','long','lat',
                'owner','placed','homepage','logurl','url','country','state','city','length','duration','difficulty','terrain','description'
            ) as $_k) {
            $method = "get".ucfirst($_k);
            if ((string)$ocm->method()!=='') {
                $xml .= '			<' . $_k . '>' . $ocm->method() . '</' . $_k . '>' . $LF;
            }
        }

        $xml .= '			<waypoints>' . $LF;
        if (is_array($ocm->getWaypoints())) {
            foreach ($ocm->getWaypoints() as $wp) {
                if ($wp->getPublic()) {
                    $public = "true";
                } else {
                    $public = "false";
                }
                $xml .= '			<waypoint>' . $LF;
                foreach(array('pointType','name','title','description','long','lat','public','hint') as $_k) {
                    $method = "get".ucfirst($_k);
                    if ((string)$ocm->method()!=='') {
                        $xml .= '				<' . strtolower($_k) . '>' . strtolower($wp->$method()) . '</' . strtolower($_k) . '>' . $LF;
                    }
                }
                $xml .= '			</waypoint>' . $LF;
            }
        }
        $xml .= '		</waypoints>' . $LF;
        $xml .= '		<attributes>' . $LF;
        if (is_array($ocm->getAttributes())) {
            while (list ($n, $v) = each($ocm->getAttributes())) {
                $xml .= '			<attribute name="' . $n . '" value="' . $v .
                        '"/>' . $LF;
            }
        }
        $xml .= '		</attributes>' . $LF;
        $xml .= '		</cache>' . $LF;
        $xml .= '	</caches>' . $LF;
        $xml .= '</ocf>' . $LF;
        return $xml;
    }

    /**
     * get LOF formatted content appropriate for sending over a web server
     *   attaches appropriate headers and wrappers to the file
     * @param Application\Model\Cache $ocm
     * @param string $filename
     */
    public static function sendLOC (Application\Model\Cache $ocm, $filename = "geocache.loc")
    {
        if ($code = self::getLOC($ocm)) {
            self::_sendFileToBrowser($code, "application/xml-loc", $filename);
            return true;
        } else {
            return false;
        }
    }

    /**
     * get GPX formatted content appropriate for sending over a web server
     *   attaches appropriate headers and wrappers to the file
     * @param Application\Model\Cache $ocm
     * @param string $filename
     */
    public static function sendGPX (Application\Model\Cache $ocm, $filename = "geocache.gpx")
    {
        if ($code = self::getGPX($ocm)) {
            self::_sendFileToBrowser($code, "application/x-gps", $filename);
            return true;
        } else {
            return false;
        }
    }

    /**
     * get KML formatted content appropriate for sending over a web server
     *   attaches appropriate headers and wrappers to the file
     * @param Application\Model\Cache $ocm
     * @param string $filename
     */
    public static function sendKML (Application\Model\Cache $ocm, $filename = "geocache.kml")
    {
        if ($code = self::getGPX($ocm)) {
            self::_sendFileToBrowser($code,
                "application/vnd.google-earth.kml+xml", $filename);
            return true;
        } else {
            return false;
        }
    }

    /**
     * get OCF formatted content appropriate for sending over a web server
     *   attaches appropriate headers and wrappers to the file
     * @param Application\Model\Cache $ocm
     * @param string $filename
     */
    public static function sendOCF (Application\Model\Cache $ocm, $filename = "geocache.ocf")
    {
        if ($code = self::getLOC($ocm)) {
            self::_sendFileToBrowser($code, "application/xml-ocf", $filename);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Wrap content for displaying ina  web browser with appropriate headers
     * @param string $content			text data to be wrapped
     * @param string $mimeType			MIME type of file being sent
     * @param string $filename			filename to label on the attachment
     */
    public static function _sendFileToBrowser ($content, $mimeType, $filename)
    {
        header("Pragma: no-cache");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Cache-Control: no-cache");
        header("Expires: -1");
        header("Content-Type: " . $mimeType);
        echo $content;
    }

    /**
     * wrapper for XML CDATA element creation
     * @static
     * @param string $str
     * @return string
     */
    public static function xmlPrep ($str)
    {
        if (strstr($str, "<") || strstr($str, ">") || strstr($str, "&") ||
                strstr($str, "'") || strstr($str, "\"")) {
            return "<![CDATA[" . $str . "]]>";
        }
        return $str;
    }
}

