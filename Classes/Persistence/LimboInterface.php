<?php
namespace CIC\Cicbase\Persistence;
	/***************************************************************
	 *  Copyright notice
	 *  (c) 2012 Peter Soots <peter@castironcoding.com>, Cast Iron Coding
	 *  All rights reserved
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
	 *  (at your option) any later version.
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/

interface LimboInterface extends \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function clearHeld($key = '');

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getHeld($key = '');

	/**
	 * Put an object in limbo
	 *
	 * @param \CIC\Cicbase\Persistence\LimboObjectInterface $limboObject
	 * @param string $key
	 * @return mixed
	 */
	public function hold(LimboObjectInterface $limboObject, $key = '');

}