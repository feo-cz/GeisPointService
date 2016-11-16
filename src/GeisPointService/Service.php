<?php
/**
 * GeisPointService - Library implementing PHP client for GeisPoint web service.
 *
 * @author Ondrej Donek, <ondrejd@gmail.com>
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 */

namespace GeisPointService;

/**
 * Class that implements client of GeisPoint service.
 *
 * @author Ondřej Doněk, <ondrej.donek@ebrana.cz>
 * @link http://www.geisparcel.cz/support/files/Geis_GeisPoint_WS.pdf
 */
class Service implements ServiceInterface
{
	/**
	 * @const string URL webove sluzby.
	 */
	const URL = 'http://plugin.geispoint.cz/wsdl/wsdl.php?WSDL';

	/**
	 * @const string Defaultni zeme (Ceska Republika).
	 */
	const DEFAULT_COUNTRY = 'CZ';

	/**
	 * @const string Defaultni region (Praha)
	 */
	const DEFAULT_REGION = 19;

	/**
	 * @var string $defaultCountry
	 */
	protected $defaultCountry;

	/**
	 * @var string $defaultRegion
	 */
	protected $defaultRegion;

	/**
	 * @var string|null $lastError
	 */
	protected $lastError;

	/**
	 * @var SoapClient $client
	 */
	protected $client;

	/**
	 * @var Cache\CacheInterface $cache
	 */
	protected $cache;

	/**
	 * @var boolean $useCache
	 */
	protected $useCache;

	/**
	 * Constructor.
	 *
	 * @param array $options
	 * @return void
	 */
	public function __construct($options = array())
	{
		$opts = $this->normalizeOptions($options);

		$this->defaultCountry = $opts['defaultCountry'];
		$this->defaultRegion = $opts['defaultRegion'];
		$this->useCache = $opts['useCache'];

		if ($this->useCache === true) {
			$this->initCache($opts);
		}

		$this->client = new \SoapClient(self::URL);
	}

	/**
	 * Normalizes array with service options.
	 *
	 * @param array $options
	 * @return array
	 */
	protected function normalizeOptions(array $options)
	{
		if (!array_key_exists('defaultCountry', $options)) {
			$options['defaultCountry'] = self::DEFAULT_COUNTRY;
		}

		if (!array_key_exists('defaultRegion', $options)) {
			$options['defaultRegion'] = self::DEFAULT_REGION;
		}

		if (!array_key_exists('useCache', $options)) {
			$options['useCache'] = false;
		}

		if (!array_key_exists('usedCache', $options)) {
			$options['usedCache'] = null;
		}

		if (!array_key_exists('cacheOptions', $options)) {
			$options['cacheOptions'] = array();
		}

		if (!is_array($options['cacheOptions'])) {
			throw new Exception('Invalid value for options key `cacheOptions`!');
		}

		return $options;
	}

	/**
	 * Initializes cache.
	 *
	 * @param array $options
	 * @return void
	 */
	protected function initCache(array $options)
	{
		if (!class_exists($options['usedCache'], true)) {
			throw new Exception('Class defined in `usedCache` does not exist!');
		}

		$this->cache = new $options['usedCache']($options['cacheOptions']);
	}

	/**
	 * Returns message of the latest error.
	 *
	 * @return string|null
	 */
	public function getLastError()
	{
		return $this->lastError;
	}

	/**
	 * Retrieve regions.
	 *
	 * @param string $country (Optional.)
	 * @return array
	 */
	public function getRegions($country = null)
	{
		$country = (is_null($country) || empty($country))
			? $this->defaultCountry
			: $country;

		$cacheKey = 'region|' . $country;

		if ($this->useCache === true) {
			if ($this->cache->exists($cacheKey)) {
				return $this->cache->get($cacheKey);
			}
		}

		$arguments = array('country_code' => $country);
		$json = $this->client->__soapCall('getRegions', $arguments);
		$data = json_decode($json);
		$regions = array();

		foreach ($data as $itm) {
			if (is_object($itm)) {
				$regions[] = new Region($itm);
			}
		}

		if ($this->useCache === true) {
			$this->cache->set($cacheKey, $regions);
		}

		return $regions;
	}

	/**
	 * Retrieve cities for the specified region.
	 *
	 * @param string $country (Optional.)
	 * @param integer $region (Optional.)
	 * @return array
	 */
	public function getCities($country = null, $region = null)
	{
		$country = (is_null($country) || empty($country))
			? $this->defaultCountry
			: $country;

		$region = (is_null($region) || empty($region))
			? $this->defaultRegion
			: $region;

		$cacheKey = 'city|' . $region;

		if ($this->useCache === true) {
			if ($this->cache->exists($cacheKey)) {
				return $this->cache->get($cacheKey);
			}
		}

		$arguments = array('country_code' => $country, 'id_region' => $region);
		$json = $this->client->__soapCall('getCities', $arguments);
		$data = json_decode($json);
		$cities = array();

		foreach ($data as $itm) {
			if (is_object($itm)) {
				$cities[] = new City($itm);
			}
		}

		if ($this->useCache === true) {
			$this->cache->set($cacheKey, $cities);
		}

		return $cities;
	}

	/**
	 * Returns detail informations about single Geis Point.
	 *
	 * @param string $gpid
	 * @return \GeisPointService\Point
	 * @throws \InvalidArgumentException
	 */
	public function getPointDetail($gpid)
	{
		if (!is_string($gpid)) {
			throw new \InvalidArgumentException();
		}

		if (empty($gpid)) {
			throw new \InvalidArgumentException();
		}

		$cacheKey = 'point|' . $gpid;

		if ($this->useCache === true) {
			if ($this->cache->exists($cacheKey)) {
				return $this->cache->get($cacheKey);
			}
		}

		$arguments = array('id_gp' => (string) $gpid);
		$json = $this->client->__soapCall('getGPDetail', $arguments);
		$data = json_decode($json);

		if (count($data) === 1) {
			$point = new Point($data[0]);

			if ($this->useCache === true) {
				$this->cache->set($cacheKey, $point);
			}

			return $point;
		}

		throw new Exception('GeisPoint details was not found!');
	}

	/**
	 * Performs search for Geis Points. If all parameters are empty,
     * all Geis Points be returned.
	 *
	 * @param string $zip
	 * @param string $city
	 * @param string $gpid
	 * @return array
	 * @todo Also this method should be cached!
	 */
	public function searchPoints($zip = null, $city = null, $gpid = null)
	{
		$arguments = array(
            'zip'   => (string)$zip,
            'city'  => (string)$city,
            'id_gp' => (string)$gpid,
        );

		$json = $this->client->__soapCall('searchGP', $arguments);
		$data = json_decode($json);
		$points = array();

		foreach ($data as $itm) {
			if (is_object($itm)) {
				$points[] = new Point($itm);
			}
		}

		return $points;
	}
}
