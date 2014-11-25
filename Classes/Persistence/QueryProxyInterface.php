<?php

namespace CIC\Cicbase\Persistence;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface QueryProxyInterface {


	/**
	 * @param QueryInterface $query
	 */
	public function __construct(QueryInterface $query);

	/**
	 * @return array
	 */
	public function getContraints();

	/**
	 * @param callable $closure
	 */
	public function logicalNot(\Closure $closure);

	/**
	 * @param callable $closure
	 */
	public function logicalAnd(\Closure $closure);

	/**
	 * @param callable $closure
	 */
	public function logicalOr(\Closure $closure);

	/**
	 * @param array $orderings
	 */
	public function setOrderings(array $orderings);

	/**
	 * @param int $limit
	 */
	public function setLimit($limit);

	/**
	 * @param int $offset
	 */
	public function setOffset($offset);

	/**
	 * @param bool $bool
	 */
	public function setRespectStoragePage($bool);

	/**
	 * Call the query object, but save the constraints
	 *
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args);
}