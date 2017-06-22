<?php
namespace CIC\Cicbase\Domain\Model;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Zach Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
*  			Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding, Inc
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use CIC\Cicbase\Traits\ExtbaseInstantiable;

/**
 * Address Model
 *
 * @version $Id$
 * @copyright Copyright belngs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class LatLng extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
    use ExtbaseInstantiable;

	/**
	 * latitude
	 * @var float
	 */
	protected $lat = 0;

	/**
	 * longitude
	 * @var float
	 */
	protected $lng = 0;

    /**
     * LatLng constructor.
     * @param $args
     */
    public function __construct($args = array()) {
        if ($args['lat']) {
            $this->setLat($args['lat']);
        }

        if ($args['lng']) {
            $this->setLng($args['lng']);
        }
    }

    /**
	 * Setter for lat
	 *
	 * @param string $lat Latitude
	 * @return void
	 */
	public function setLat($lat) {
		$this->lat = floatval($lat);
	}

	/**
	 * Setter for lng
	 *
	 * @param string $lng longitude
	 * @return void
	 */
	public function setlng($lng) {
		$this->lng = floatval($lng);
	}

	/**
	 * Getter for lat
	 *
	 * @return string Lat
	 */
	public function getLat() {
		return $this->lat;
	}

	/**
	 * Getter for lng
	 *
	 * @return string lng
	 */
	public function getlng() {
		return $this->lng;
	}

    /**
     * @return string
     */
	public function getStringTuple() {
        return implode(',', $this->getTuple());
    }

    /**
     * @return array
     */
    public function getTuple() {
        return array('lat' => $this->getLat(), 'lng' => $this->getlng());
    }

}

