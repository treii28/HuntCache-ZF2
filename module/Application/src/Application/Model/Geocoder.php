<?php
/**
 * GeocodingAdapter
 *
 * @author Robert McVey
 */
class Geocoder {
	protected $key = "";

	public function __construct($apiKey)
	{
		$this->key = $apiKey;
	}

	public function getGeocodedLatitudeAndLongitude($address)
	{
		$client = new Zend_Http_Client();
		$client->setUri($this->getGeocodingUri());
		$client->setParameterGet('q', urlencode($address))
			   ->setParameterGet('output', 'json')
			   ->setParameterGet('sensor', 'false')
			   ->setParameterGet('key', $this->key);

		$result = $client->request('GET');

		$response = Zend_Json_Decoder::decode($result->getBody(),
					Zend_Json::TYPE_OBJECT);

		return $response;
	}

	public function getCoordinates($address)
	{
		$response = $this->_getGeocodedLatitudeAndLongitude($address);
		if(isset($response->Placemark[0]->Point->coordinates[1])){
			return array(
				'lat' => $response->Placemark[0]->Point->coordinates[1],
				'lon' => $response->Placemark[0]->Point->coordinates[0]
			);
		}
		return null;
	}

	private function getGeocodingUri()
	{
		return 'http://maps.google.com/maps/geo';
	}
}
?>
