<?php
	/*
	Waypoint class for PHP
	----------------------

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

	class Waypoint
	{
		var $pointtype;
		var $name;
		var $title;
		var $description;
		var $long;
		var $lat;
		var $public;
		var $hint;

		var $fields=array('pointtype', 'name', 'title', 'description', 'long', 'lat', 'public', 'hint');

		function Waypoint() { $this->__construct(); }
		function __construct() {}

		function free()
		{
			foreach($this->fields AS $field)
			{
				$this->$field=null;
			}
		}

		function setPointType($str)
		{
			$str=strtolower($str);
			if(in_array($str, array('cache', 'parking', 'puzzle'))) {
				$this->pointtype=$str;
			} else {
				$this->free();
				return false;
			}
		}

		function setName($str)
		{
			if(strlen($str) <= 6) {
				$this->name=$str;
			} else {
				$this->free();
				return false;
			}
		}

		function setTitle($str)
		{
			$this->title=$str;
		}

		function setDescription($str)
		{
			$this->description=$str;
		}

		function setCoordinates($long, $lat)
		{
			if(preg_match("/^[0-9]{1,3}\.[0-9]+$/", $long) && preg_match("/^[0-9]{1,3}\.[0-9]+$/", $lat)) {
				$this->lat=$lat;
				$this->long=$long;
			} else {
				// Invalid latitude or longitude value, must be decimal
				$this->free();
				return false;
			}
		}

		function setPublic($bool)
		{
			// Force bool type
			if($bool) {
				$this->public=true;
			} else {
				$this->public=false;
			}
		}

		function setHint($str)
		{
			$this->hint=$str;
		}

		function getPointType()
		{
			return $this->pointtype;
		}

		function getName()
		{
			return $this->name;
		}

		function getTitle()
		{
			return $this->title;
		}

		function getDescription()
		{
			return $this->description;
		}

		function getCoordinates()
		{
			return array($this->long, $this->lat);
		}

		function getPublic()
		{
			return $this->public;
		}

		function getHint()
		{
			return $this->hint;
		}
	}

?>