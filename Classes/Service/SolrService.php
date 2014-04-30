<?php

	/***************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2013
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
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
 *
 *
 * @package orbest
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Cicbase_Service_SolrService {
	/**
	 *        @var array
	 */
	protected $solrConnection = array(
		'host' => '',
		'port' => '',
		'path' => '',
		'scheme' => '',
	);

	/**
	 * Set to true if a query was executed
	 * @var boolean
	 */
	private $queryExecuted = false;

	/**
	 * Keywords, defaults to *
	 * @var string
	 */
	private $keywords = '*';


	/**
	 * Solr response, set when the query is executed
	 * @var mixed|object|boolean
	 */
	private $response = false;

	/**
	 * Solr search object, set when the query is executed
	 * @var mixed|object|boolean
	 */
	private $search = false;

	/**
	 * Solr query object, set when the query is executed
	 * @var mixed|object|boolean
	 */
	private $query = false;

	/**
	 * Solr The number of results found
	 * @var mixed|integer|boolean
	 */
	private $numFound = false;

	/**
	 * Solr A limit to the number of results.
	 * @var mixed|integer|boolean
	 */
	protected $queryLimit = 999999;


	/**
	 * Solr An offset, for pagination if you will.
	 * @var mixed|integer|boolean
	 */
	protected $queryOffset = 0;


	/**
	 * The regex for text cleanup
	 * @var string
	 */
	protected $textCleanupRegex = '/[^A-Za-z0-9]+/';

	/**
	 * The filters that are joined by AND to make a complete Query to Solr
	 * @var array
	 */
	protected $filters = array();

	/**
	 * @var array
	 */
	protected $sorting = array();


	/**
	 * Guesses the solr connection configuration (useful if called on page request)
	 */
	public function initialize($documentType = '') {
		$this->addFilter('type', $documentType);
	}

	/**
	 * Sets a single connection parameter or an array of parameters
	 */
	public function setSolrConnectionParameter() {
		$args = func_get_args();
		if(is_string($args[0]) && is_string($args[1])) {
			$this->solrConnection[$args[0]] = $args[1];
		} elseif(is_array($args[0])) {
			foreach($args[0] as $k => $v) {
				$this->solrConnection[(string) $k] = (string) $v;
			}
		}
	}

	/**
	 * Setting the filters can be customized by methods
	 * in a subclass.
	 */
	public function setFilters($args) {
		foreach($args as $argName => $argValue) {
			if($argValue) {
				$methodName = 'add'.ucfirst($argName).'Filter';
				if(method_exists($this,$methodName)) {
					$this->$methodName($argValue, $args);
				} else {
					$this->addFilter($argName, $argValue);
				}
			}
		}
	}

	/**
	 *  If a filter has been applied
	 *  @return boolean
	 */
	public function hasFilters() {
		return count($this->filters) ? true : false;
	}

	/**
	 * Adds a filter to the query string. You can pass
	 * a string or an array. If you pass an array, you
	 * can also specify how to join the values (as
	 * conjunction or disjunction).
	 *
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $glue
	 */
	protected function addFilter($key, $value, $glue = 'OR') {
		if(is_string($value)) {
			$value = self::quote($value);
		} else if(is_array($value) && $glue) {
			switch(strtolower($glue)) {
				case 'or' : $value = self::logicalOR($value);  break;
				case 'and': $value = self::logicalAND($value); break;
			}
		}
		$this->filters[$key] = "$key:$value";
	}

	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * @return string
	 */
	public function getKeywords() {
		return $this->keywords;
	}

	/**
	 *
	 * @return array
	 */
	protected function getFieldList() {
		return array('*','score');
	}

	/**
	 * The max number of results to return
	 * @var integer
	 */
	protected function setQueryLimit($limit) {
		$this->queryLimit = (integer) $limit;
	}

	/**
	 * The max number of results to return
	 * @return integer
	 */
	protected function getQueryLimit() {
		return $this->queryLimit;
	}

	/**
	 * The query offset
	 * @var integer
	 */
	protected function setQueryOffset($offset) {
		$this->queryOffset = (integer) $offset;
	}

	/**
	 * The query offset
	 * @return integer
	 */
	protected function getQueryOffset() {
		return $this->queryOffset;
	}

	/**
	 *
	 */
	protected function hasSorting() {
		return is_array($this->sorting) && count($this->sorting);
	}


	/**
	 * This function creates a conjunction of string
	 * values. You can pass it a list of strings or
	 * an array of strings.
	 *
	 * Empty strings are skipped.
	 *
	 * @static
	 * @return string Something like: "(a AND b AND c)"
	 */
	protected static function logicalAND(){
		$values = func_get_args();
		if(is_array($values[0])){
			$values = $values[0];
		}
		return self::junction('AND', $values);
	}

	/**
	 * This function creates a disjunction of string
	 * values. You can pass it a list of strings or
	 * an array of strings.
	 *
	 * Empty strings are skipped.
	 *
	 * @static
	 * @return string Something like: "(a OR b OR c)"
	 */
	protected static function logicalOR(){
		$values = func_get_args();
		if(is_array($values[0])){
			$values = $values[0];
		}
		return self::junction('OR', $values);
	}

	/**
	 * @static
	 * @param $timestamp
	 * @return string
	 */
	protected static function timestampToDate($timestamp) {
		return date("Y-m-d\TG:i:s\Z", $timestamp);
	}

	/**
	 * This function makes a disjunction or a conjuncion
	 * string. (i.e. "(a OR b OR c)" or "(d AND e)")
	 *
	 * @static
	 * @param string $junction "AND" or "OR"
	 * @param array $values An array of strings (atoms)
	 * @return string
	 */
	protected static function junction($junction, array $values){
		// remove empties and add quotes
		foreach($values as $val){
			$parsed = explode(':',$val);
			if($val !== '' && $parsed[1] !== ''){
				$newVals[] = self::quote($val);
			}
		}

		$values = $newVals;
		$length = count($values);
		if($length == 0){
			return '';
		}
		if($length == 1){
			return $values[0];
		}

		$str = '(';

		for($i = 0; $i < $length; ++$i){
			$str .= $values[$i];
			if($i + 1 < $length) {
				$str .= ' '.$junction.' ';
			}
		}

		$str .= ') ';
		return $str;
	}

	protected static function quote($val) {
		if(strpos($val, '[') === 0) {
			return $val;
		}
		if(strpos($val, ' ') !== FALSE) {
			$val = '"'.$val.'"';
		}
		return $val;
	}

	/**
	 *
	 *
	 */
	private function executeQuery() {
		$this->queryExecuted = true;

		$solrConnection = t3lib_div::makeInstance('tx_solr_ConnectionManager')->getConnection();
		$search = t3lib_div::makeInstance('tx_solr_Search', $solrConnection);
		$query = t3lib_div::makeInstance('tx_solr_Query', $this->getKeywords());

		$query->setFieldList($this->getFieldList());

		if($this->hasSorting()) {
			$sorts = array();
			foreach ($this->sorting as $field => $dir) {
				if ($dir != tx_solr_Query::SORT_DESC && $dir != tx_solr_Query::SORT_ASC) {
					throw new Exception('Please use tx_solr_Query constants to specify sort order.');
				}
				$sorts[] = "$field ".strtolower($dir);
			}
			$query->setSorting(implode(',', $sorts));
		}

		$solrConfiguration = tx_solr_Util::getSolrConfiguration();
		if (isset($solrConfiguration['search.']['query.']['filter.']) && is_array($solrConfiguration['search.']['query.']['filter.'])) {
			$this->setFilters($solrConfiguration['search.']['query.']['filter.']);
		}

		foreach($this->filters as $filter) {
			$query->addFilter($filter);
		}

		$this->response = $search->search($query, $this->getQueryOffset(), $this->getQueryLimit());
		$this->body = json_decode($this->response->getRawResponse());
		$this->search = $search;
		$this->query = $query;
		$this->numFound = $this->body->response->numFound;
	}

	/**
	 *
	 *
	 */
	public function getResultDocuments() {
		$this->executeQuery();
		return $this->search->getResultDocuments();
	}

	/**
	 *
	 *
	 */
	public function getNumFound() {
		return $this->numFound;
	}

	/**
	 *
	 *
	 */
	public function getRecords() {
		return $this->getResultDocuments();
	}

	/**
	 *
	 *
	 */
	public function getRecordUids() {
		$out = array();
		foreach($this->getResultDocuments() as $record) {
			$out[] = $record->uid;
		}
		return $out;
	}

}

?>