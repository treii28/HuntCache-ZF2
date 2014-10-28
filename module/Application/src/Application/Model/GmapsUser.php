<?php

class GmapsUser extends Zend_Db_Table_Abstract{
	protected $_name = 'users';
	protected $key;
	protected $geocoder;

	public function init()
	{
		$this->key = Zend_Registry::get('google')->google_map_key;
		$this->geocoder = new Geocoder($this->key);

	}
	public function getUsersAndGeocode()
	{
		$result = $this->fetchAll();

		$users	= $result->_toArray();
		foreach($users as $user)
		{
			$address = "{$user['address']} {$user['city']} {$user['state']} {$user['zip']}";

			$latlon = $this->geocoder->getCoordinates($address);
			if($latlon)
			{
				$user['lat'] = $latlon['lat'];
				$user['lon'] = $latlon['lon'];
			}
		}
		return $users;
	}
}

?>