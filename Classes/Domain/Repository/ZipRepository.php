<?php
namespace CIC\Cicbase\Domain\Repository;

/***************************************************************
*  Copyright notice
*
*  (c)  TODO - INSERT COPYRIGHT
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

/**
 * Repository for Tx_Tidesprojdir_Domain_Model_Strategy
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ZipRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
	
	
	public function findByZip($zip) {
		$query = $this->createQuery();		
		$out = $query->execute();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$out = $query->matching($query->equals('zipcode', $zip))
			->execute();
		return $out->current();
	}
}
?>