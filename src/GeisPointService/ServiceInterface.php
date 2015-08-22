<?php
/**
 * GeisPointService - Library implementing PHP client for GeisPoint web service.
 *
 * @author Ondřej Doněk, <ondrej.donek@ebrana.cz>
 * @license https://www.mozilla.org/MPL/2.0/ name
 */

namespace GeisPointService;

/**
 * Interface describing the service.
 * 
 * @author Ondřej Doněk, <ondrej.donek@ebrana.cz>
 * @link http://www.geisparcel.cz/support/files/Geis_GeisPoint_WS.pdf
 */
interface ServiceInterface
{
	/**
	 * Retrieve regions.
	 *
	 * @param string $countryCode
	 * @return array
	 */
	public function getRegions($countryCode);

	/**
	 * Retrieve cities for the specified region.
	 *
	 * @param string $countryCode
	 * @param integer $regionId
	 * @return array
	 */
	public function getCities($countryCode, $regionId);

	/**
	 * Returns detail informations about single Geis Point.
	 *
	 * @param string $gpid
	 * @return Point
	 * @throws \InvalidArgumentException
	 */
	public function getPointDetail($gpid);

	/**
	 * Performs search for Geis Points. At least one parameter MUST be passed.
	 *
	 * @param string $zip
	 * @param string $city
	 * @param string $gpId
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function searchPoints($zip, $city, $gpid);
}