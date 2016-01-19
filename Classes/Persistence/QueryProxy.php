<?php

namespace CIC\Cicbase\Persistence;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class QueryProxy implements QueryProxyInterface{

	/** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface */
	protected $query;

	/** @var array */
	protected $constraints = array();

	/**
	 * @param QueryInterface $query
	 */
	public function __construct(QueryInterface $query) {
		$this->query = $query;
	}

	/**
	 * @return array
	 */
	public function getContraints() {
		return $this->constraints;
	}

	/**
	 * @param callable $closure
	 */
	public function logicalNot(\Closure $closure) {
		/** @var QueryProxy $newProxy */
		$newProxy = $this->newProxy();
		$closure($newProxy);
		$this->constraints[] = $this->query->logicalNot(current($newProxy->getContraints()));
	}

	/**
	 * @param callable $closure
	 */
	public function logicalAnd(\Closure $closure) {
		/** @var QueryProxy $newProxy */
		$newProxy = $this->newProxy();
		$closure($newProxy);
		$this->constraints[] = $this->query->logicalAnd($newProxy->getContraints());
	}

	/**
	 * @param callable $closure
	 */
	public function logicalOr(\Closure $closure) {
		/** @var QueryProxy $newProxy */
		$newProxy = $this->newProxy();
		$closure($newProxy);
		$this->constraints[] = $this->query->logicalOr($newProxy->getContraints());
	}

	/**
	 * @param array $orderings
	 */
	public function setOrderings(array $orderings) {
		$this->query->setOrderings($orderings);
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit) {
		$this->query->setLimit($limit);
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset) {
		$this->query->setOffset($offset);
	}

	/**
	 * @param bool $bool
	 */
	public function setRespectStoragePage($bool = TRUE) {
		$settings = $this->query->getQuerySettings();
		$settings->setRespectStoragePage($bool);
		$this->query->setQuerySettings($settings);
	}

	/**
	 * @param bool $bool
	 */
	public function setIncludeDeleted($bool = TRUE) {
		$settings = $this->query->getQuerySettings();
		$settings->setIncludeDeleted($bool);
		$this->query->setQuerySettings($settings);
	}

	/**
	 * @param bool $bool
	 */
	public function setIgnoreEnableFields($bool = TRUE) {
		$settings = $this->query->getQuerySettings();
		$settings->setIgnoreEnableFields($bool);
		$this->query->setQuerySettings($settings);
	}

	/**
	 * @return mixed
	 */
	protected function newProxy() {
		$class = get_class($this);
		return new $class($this->query);
	}

	/**
	 * Call the query object, but save the constraints
	 *
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args) {
		$this->constraints[] = call_user_func_array(array($this->query, $name), $args);
	}
}