<?php
/*
    OCF Parser class for PHP
    ------------------------

    Part of the OpenCacheFormat Documentation / Examples
    http://opencacheformat.sourceforge.net

    Copyright (c) 2006 Sascha Kimmel, tricos media (www.tricos.com)

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be included
    in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
    OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
    IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
    CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
    TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
    SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

    */

class OCFParser
{
    var $lastError;

    function parse($xmlFile)
    {
        $ocfData=null;

        $this->lastError=null;

        if (version_compare(phpversion(), "5", ">=")) {

            // PHP5 OCF parser
            if(function_exists("simplexml_load_file")) {
                $data=array();
                $caches=array();

                if (file_exists($xmlFile)) {
                    echo "opening '$xmlFile'\n";
                    if($xml = simplexml_load_file($xmlFile)) {

                        // <version>
                        if($version=(string)$xml->version) {
                            $data["version"] = $version;
                        }

                        // <date>
                        if($date=(string)$xml->date) {
                            if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2})\:([0-9]{2})\:([0-9]{2})/", $date, $m)) {
                                $data["date"] = $date;
                                $data["date"]=mktime ($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
                            }
                        }

                        // <cache>
                        foreach($xml->caches[0]->cache AS $cache) {

                            if($cache->srcid) {
                                $data["srcid"]=(string)$cache->srcid;
                            }

                            if($cache->name) {
                                $data["name"]=(string)$cache->name;
                            }

                            if($cache->src) {
                                $data["src"]=(string)$cache->src;
                            }

                            if($cache->lang) {
                                $data["lang"]=strtolower((string)$cache->lang);
                            }

                            if($cache->owner) {
                                $data["owner"]=(string)$cache->owner;
                            }

                            if($cache->placed) {
                                list($y, $m, $d) = explode("-", (string)$cache->placed);
                                $data["placed"]=mktime(0, 0, 0, $m, $d, $y);
                            }

                            if($cache->homepage) {
                                $data["homepage"]=(string)$cache->homepage;
                            }

                            if($cache->logurl) {
                                $data["logurl"]=(string)$cache->logurl;
                            }

                            if($cache->homepage) {
                                $data["homepage"]=(string)$cache->homepage;
                            }

                            if($cache->url) {
                                $data["url"]=(string)$cache->url;
                            }

                            if($cache->type) {
                                $data["type"]=strtolower((string)$cache->type);
                            }

                            if($cache->size) {
                                $data["size"]=strtolower((string)$cache->size);
                            }

                            if($cache->title) {
                                $data["title"]=(string)$cache->title;
                            }

                            if($cache->country) {
                                $data["country"]=strtolower((string)$cache->country);
                            }

                            if($cache->state) {
                                $data["state"]=strtolower((string)$cache->state);
                            }

                            if($cache->city) {
                                $data["city"]=(string)$cache->city;
                            }

                            if($cache->long) {
                                $data["long"]=(float)$cache->long;
                            }

                            if($cache->lat) {
                                $data["lat"]=(float)$cache->lat;
                            }

                            if($cache->length) {
                                $data["length"]=(int)$cache->length;
                            }

                            if($cache->duration) {
                                $data["duration"]=(int)$cache->duration;
                            }

                            if($cache->difficulty) {
                                $data["difficulty"]=(int)$cache->difficulty;
                            }

                            if($cache->terrain) {
                                $data["terrain"]=(int)$cache->terrain;
                            }

                            if($cache->description) {
                                $data["description"]=(string)$cache->description;
                            }

                            // <waypoint>
                            $wps=array();
                            $wp=array();
                            foreach($cache->waypoints[0]->waypoint AS $waypoint) {

                                if($waypoint->pointtype) {
                                    $wp["pointtype"]=strtolower((string)$waypoint->pointtype);
                                }

                                if($waypoint->name) {
                                    $wp["name"]=(string)$waypoint->name;
                                }

                                if($waypoint->title) {
                                    $wp["title"]=(string)$waypoint->title;
                                }

                                if($waypoint->description) {
                                    $wp["description"]=(string)$waypoint->description;
                                }

                                if($waypoint->long) {
                                    $wp["long"]=(float)$waypoint->long;
                                }

                                if($waypoint->lat) {
                                    $wp["lat"]=(float)$waypoint->lat;
                                }

                                if($waypoint->public) {
                                    $wp["public"] = (strtolower((string)$waypoint->public) == "true" || strtolower((string)$waypoint->public) == 1);
                                }

                                if($waypoint->hint) {
                                    $wp["hint"]=(string)$waypoint->hint;
                                }

                                $wps[]=$wp;
                            }

                            if(sizeof($wps)) {
                                $data["waypoints"]=$wps;
                            }
                            unset($wps);
                            unset($wp);
                        }

                        // <attribute>
                        $attributes=array();
                        foreach($cache->attributes[0]->attribute AS $attribute) {
                            $useName=null;
                            $useValue=null;
                            foreach($attribute->attributes() AS $a => $b) {

                                if($a == "name") {
                                    $useName=(string)$b;
                                }
                                if($a == "value") {
                                    $useValue=(int)$b;
                                }

                                if(isset($useName) && isset($useValue)) {
                                    $attributes[strtolower($useName)] = $useValue;
                                }
                            }
                        }

                        if(sizeof($attributes)) {
                            $data["attributes"] = $attributes;
                        }

                        $ocfData=$data;
                        unset($data);
                    } else {
                        // Invalid XML
                        $this->lastError="Could not parse XML (invalid)";
                    }
                } else {
                    // File does not exist
                    $this->lastError="The given file ".$xmlFile." does not exist!";
                }
            } else {
                // simplexml not supported!
                $this->lastError="You are using PHP 5, but you PHP build does not include simplexml support.";
            }
        } else {
            // PHP 4 - PHP must have been compiled with a least libxml-2.4.14
            if(function_exists('domxml_xmltree')) {
                if($xmlData=file_get_contents($xmlFile)) {
                    if($dom=domxml_xmltree($xmlData)) {

                        // <version>1.0</version>
                        if($versionNodes = $dom->get_elements_by_tagname('version')) {
                            $OCFVERSION=$versionNodes[0]->get_content();
                        }

                        // <date>2006-05-17T09:30:47.0Z</date>
                        if($dateNodes = $dom->get_elements_by_tagname('date')) {
                            $preDate=$dateNodes[0]->get_content();
                            // Convert from the XML dateTime format to a PHP timestamp
                            if(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2})\:([0-9]{2})\:([0-9]{2})/', $preDate, $m)) {
                                $OCFDATE=mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
                            }
                        }

                        // <caches><cache>.*</cache></caches>
                        if($cdCaches = $dom->get_elements_by_tagname('cache')) {

                            $caches=array();

                            foreach($cdCaches AS $cache) {

                                $data=array();

                                if($cacheElements=$cache->child_nodes()) {
                                    foreach($cacheElements AS $cacheElement) {
                                        if($tag=$cacheElement->tagname) {

                                            $content=$cacheElement->get_content();

                                            switch($tag) {

                                                case "srcid":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "name":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "src":
                                                    $data[$tag]=strtoupper($content);
                                                    break;

                                                case "lang":
                                                    $data[$tag]=strtolower($content);
                                                    break;

                                                case "owner":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "placed":
                                                    list($year, $month, $day)=explode("-", $content);
                                                    $data[$tag]=mktime(0, 0, 0, $month, $day, $year);
                                                    break;

                                                case "homepage":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "logurl":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "url":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "type":
                                                    $data[$tag]=strtolower($content);
                                                    break;

                                                case "size":
                                                    $data[$tag]=strtolower($content);
                                                    break;

                                                case "title":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "country":
                                                    $data[$tag]=strtolower($content);
                                                    break;

                                                case "state":
                                                    $data[$tag]=strtolower($content);
                                                    break;

                                                case "city":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "long":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "lat":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "length":
                                                    $data[$tag]=(int)$content;
                                                    break;

                                                case "duration":
                                                    $data[$tag]=(int)$content;
                                                    break;

                                                case "difficulty":
                                                    $data[$tag]=(int)$content;
                                                    break;

                                                case "terrain":
                                                    $data[$tag]=(int)$content;
                                                    break;

                                                case "description":
                                                    $data[$tag]=$content;
                                                    break;

                                                case "waypoints":
                                                    if($cacheElement->has_child_nodes()) {

                                                        $waypoints=array();
                                                        if($wps = $dom->get_elements_by_tagname('waypoint')) {
                                                            foreach($wps AS $node) {
                                                                if($node->has_child_nodes()) {
                                                                    $waypoint=array();
                                                                    foreach($node->child_nodes() AS $wpNode) {

                                                                        $wpNodeContent=$wpNode->get_content();

                                                                        switch($wpNode->tagname)
                                                                        {
                                                                            case "name":
                                                                                $waypoint["name"] = $wpNodeContent;
                                                                                break;

                                                                            case "pointtype":
                                                                                $waypoint["pointtype"] = $wpNodeContent;
                                                                                break;

                                                                            case "title":
                                                                                $waypoint["title"] = $wpNodeContent;
                                                                                break;

                                                                            case "description":
                                                                                $waypoint["description"] = $wpNodeContent;
                                                                                break;

                                                                            case "long":
                                                                                $waypoint["long"] = $wpNodeContent;
                                                                                break;

                                                                            case "lat":
                                                                                $waypoint["lat"] = $wpNodeContent;
                                                                                break;

                                                                            case "public":
                                                                                $ck=strtolower($wpNodeContent);
                                                                                $waypoint["public"] = ($ck=="true" || $ck == 1);
                                                                                break;

                                                                            case "hint":
                                                                                $waypoint["hint"] = $wpNodeContent;
                                                                                break;
                                                                        }
                                                                    }
                                                                    $waypoints[]=$waypoint;
                                                                }
                                                            }
                                                        }

                                                        $data['waypoints']=$waypoints;
                                                        unset($waypoints);
                                                        unset($waypoint);
                                                    }
                                                    break;

                                                case "attributes":
                                                    if($cacheElement->has_child_nodes()) {

                                                        $attributes=array();

                                                        foreach($cacheElement->child_nodes() AS $node) {
                                                            if($node->tagname == "attribute") {
                                                                if($node->has_attribute("name") && $node->has_attribute("value")) {
                                                                    $attributes[strtolower($node->get_attribute('name'))] = (int) $node->get_attribute("value");
                                                                }
                                                            }
                                                        }

                                                        if(sizeof($attributes)) {
                                                            $data['attributes']=$attributes;
                                                            unset($attributes);
                                                        }
                                                    }
                                                    break;
                                            }
                                        }

                                    }

                                    $caches[]=$data;
                                    unset($data);

                                }
                            }
                        }

                        $ocfData=array("version" => $OCFVERSION, "date" => $OCFDATE, "caches" => $caches);
                    } else {
                        // Invalid XML
                        $this->lastError="Could not parse XML (invalid)";
                    }
                } else {
                    // File was not found
                    $this->lastError="The given file ".$xmlFile." does not exist!";
                }
            } else {
                // PHP does not support the domxml functions!
                $this->lastError="You are using PHP 4, but you PHP build does not include domxml support.";
            }
        }

        if($ocfData) return $ocfData; else return false;
    }

    private function _parseChildNodes($cacheElements)
    {
        $data = array();
        foreach($cacheElements AS $cacheElement) {
            if(isset($cacheElement->tagname)) {
                $tag=$cacheElement->tagname;

                $content=$cacheElement->get_content();

                switch($tag) {

                    case "srcid":
                        $data[$tag]=$content;
                        break;

                    case "name":
                        $data[$tag]=$content;
                        break;

                    case "src":
                        $data[$tag]=strtoupper($content);
                        break;

                    case "lang":
                        $data[$tag]=strtolower($content);
                        break;

                    case "owner":
                        $data[$tag]=$content;
                        break;

                    case "placed":
                        list($year, $month, $day)=explode("-", $content);
                        $data[$tag]=mktime(0, 0, 0, $month, $day, $year);
                        break;

                    case "homepage":
                        $data[$tag]=$content;
                        break;

                    case "logurl":
                        $data[$tag]=$content;
                        break;

                    case "url":
                        $data[$tag]=$content;
                        break;

                    case "type":
                        $data[$tag]=strtolower($content);
                        break;

                    case "size":
                        $data[$tag]=strtolower($content);
                        break;

                    case "title":
                        $data[$tag]=$content;
                        break;

                    case "country":
                        $data[$tag]=strtolower($content);
                        break;

                    case "state":
                        $data[$tag]=strtolower($content);
                        break;

                    case "city":
                        $data[$tag]=$content;
                        break;

                    case "long":
                        $data[$tag]=$content;
                        break;

                    case "lat":
                        $data[$tag]=$content;
                        break;

                    case "length":
                        $data[$tag]=(int)$content;
                        break;

                    case "duration":
                        $data[$tag]=(int)$content;
                        break;

                    case "difficulty":
                        $data[$tag]=(int)$content;
                        break;

                    case "terrain":
                        $data[$tag]=(int)$content;
                        break;

                    case "description":
                        $data[$tag]=$content;
                        break;

                    case "waypoints":
                        if($cacheElement->has_child_nodes()) {

                            $waypoints=array();
                            if($wps = $cacheElement->get_elements_by_tagname('waypoint')) {
                                foreach($wps AS $node) {
                                    if($node->has_child_nodes()) {
                                        $waypoint=array();
                                        foreach($node->child_nodes() AS $wpNode) {

                                            $wpNodeContent=$wpNode->get_content();

                                            switch($wpNode->tagname)
                                            {
                                                case "name":
                                                    $waypoint["name"] = $wpNodeContent;
                                                    break;

                                                case "pointtype":
                                                    $waypoint["pointtype"] = $wpNodeContent;
                                                    break;

                                                case "title":
                                                    $waypoint["title"] = $wpNodeContent;
                                                    break;

                                                case "description":
                                                    $waypoint["description"] = $wpNodeContent;
                                                    break;

                                                case "long":
                                                    $waypoint["long"] = $wpNodeContent;
                                                    break;

                                                case "lat":
                                                    $waypoint["lat"] = $wpNodeContent;
                                                    break;

                                                case "public":
                                                    $ck=strtolower($wpNodeContent);
                                                    $waypoint["public"] = ($ck=="true" || $ck == 1);
                                                    break;

                                                case "hint":
                                                    $waypoint["hint"] = $wpNodeContent;
                                                    break;
                                            }
                                        }
                                        $waypoints[]=$waypoint;
                                    }
                                }
                            }

                            $data['waypoints']=$waypoints;
                            unset($waypoints);
                            unset($waypoint);
                        }
                        break;

                    case "attributes":
                        if($cacheElement->has_child_nodes()) {

                            $attributes=array();

                            foreach($cacheElement->child_nodes() AS $node) {
                                if($node->tagname == "attribute") {
                                    if($node->has_attribute("name") && $node->has_attribute("value")) {
                                        $attributes[strtolower($node->get_attribute('name'))] = (int) $node->get_attribute("value");
                                    }
                                }
                            }

                            if(sizeof($attributes)) {
                                $data['attributes']=$attributes;
                                unset($attributes);
                            }
                        }
                        break;
                }
            }

        }
    }
}

?>