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
use CIC\Cicbase\Utility\Arr;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * CIC\Cicbase\Persistence\PaginationRepository
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	const LOGIC_AND = 'and';
	const LOGIC_OR = 'or';

	/**
	 * @param callable $closure
	 * @param string $combineWith
	 * @return array|QueryResultInterface
	 */
	public function query(\Closure $closure, $combineWith = self::LOGIC_AND) {
		$query = $this->createQuery();
		$context = $this->objectManager->get('CIC\Cicbase\Persistence\QueryProxy', $query);
		$result = $closure($context);
		if ($result) return $result;
		$combiner = 'logical'.ucfirst($combineWith);
		$query->matching($query->$combiner($context->getContraints()));
		return $query->execute();
	}

	/**
	 * @param callable $closure
	 * @param string $combineWith
	 * @return array|QueryResultInterface
	 */
	public function count(\Closure $closure, $combineWith = self::LOGIC_AND) {
		$query = $this->createQuery();
		$context = $this->objectManager->get('CIC\Cicbase\Persistence\QueryProxy', $query);
		$result = $closure($context);
		if ($result) {
			if ($result instanceof \Countable) {
				return count($result);
			} else if (is_object($result) && method_exists($result, 'count')) {
				return $result->count();
			} else {
				return $result;
			}
		}
		$combiner = 'logical'.ucfirst($combineWith);
		$query->matching($query->$combiner($context->getContraints()));
		return $query->count();
	}

	/**
	 * Like query() but returns the raw array data and bypasses the property mapping
	 * stages provided by extbase.
	 *
	 * @param callable $queryClosure
	 * @param string $combineWith
	 * @param null $keyField See \CIC\Cicbase\Arr::column
	 * @param null $valueField See \CIC\Cicbase\Arr::column
	 * @return array
	 * @throws \Exception
	 */
	public function rows(\Closure $queryClosure, $combineWith = self::LOGIC_AND, $keyField = NULL, $valueField = NULL) {
		$result = $this->query($queryClosure, $combineWith);
		$rows = $this->persistenceManager->getObjectDataByQuery($result->getQuery());

		if ($keyField !== NULL || $valueField !== NULL) {
			return Arr::column($rows, $valueField, $keyField);
		}

		return $rows;
	}

	/**
	 * Like rows() but works with an existing QueryResult.
	 *
	 * @param QueryResultInterface $result
	 * @param null $keyField See \CIC\Cicbase\Arr::column
	 * @param null $valueField See \CIC\Cicbase\Arr::column
	 * @return array
	 * @throws \Exception
	 */
	public function rowsFromQueryResult(QueryResultInterface $result, $keyField = NULL, $valueField = NULL) {
		$rows = $this->persistenceManager->getObjectDataByQuery($result->getQuery());

		if ($keyField !== NULL || $valueField !== NULL) {
			return Arr::column($rows, $valueField, $keyField);
		}

		return $rows;
	}

	/**
	 * Finds objects by UID.
	 *
	 * @param array|string $uids
	 * @return array|QueryResultInterface
	 * @throws \Exception
	 */
	public function findByUids($uids) {
		if(is_string($uids)) {
			$uids = explode(',', $uids);
		}
		if (!is_array($uids) && !$uids instanceof \ArrayAccess && !$uids instanceof \Traversable) {
			throw new \Exception('findByUids() only takes a comma separated string of uids or an array of uids');
		}

		return $this->query(function ($q) use ($uids) { $q->in('uid', $uids); });

	}

}