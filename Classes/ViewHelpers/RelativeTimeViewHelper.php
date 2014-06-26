<?php

namespace CIC\Cicbase\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Cicbase".                    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

class RelativeTimeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
		* Returns a string representing a time relative to the current moment, like "12 days ago," "2 minutes from now," "23 hours ago," "6 days from now," etc.
	 	* Automatically adjust granularity based on the amount of time.
		*
		* @param integer $time The timestamp for which you want the relative time
		* @return String A string representing the relative time since $time
		* @author Gabe Blair <gabe@castironcoding.com>
	*/
	public function render($time) {
		$secs = time() - $time;
		if($secs < 30) {
			$out = 'just now';
		} else {
			$quantity = $secs / 60; // minutes
			$increment = 'minute';

			if($quantity > 59) {
				$quantity = $quantity / 60; // hours
				$increment = 'hour';

				if ($quantity > 23) {
					$quantity = $quantity / 24;
					$increment = 'day';
				}
			}

			$quantity = round($quantity);

			// TODO: localize
			$out = $quantity . ' ' . $increment. (abs($quantity) > 1 ? 's' : '') . ($quantity < 0 ? ' from now' : ' ago');
		}
		return $out;
	}
}

?>
