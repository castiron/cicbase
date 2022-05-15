<?php
namespace CIC\Cicbase\Service;

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Filters;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\QueryFields;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Sorting;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\QueryBuilder;
use ApacheSolrForTypo3\Solr\Query;
use ApacheSolrForTypo3\Solr\Search;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class SolrService
 * @package CIC\Cicbase\Service
 */
class SolrService {
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
     * @var array
     */
	protected $userAccessGroups = [];

	/**
	 * Solr response, set when the query is executed
	 * @var mixed|object|boolean
	 */
	private $response = false;

	/**
	 * Solr search object, set when the query is executed
	 * @var mixed|Search|boolean
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
	 * @var mixed|object|boolean
	 */
	protected $boostQuery = false;

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
	 * @var \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser
	 * @inject
	 */
	protected $typoscriptParser;

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
			if($argValue !== null) {
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
	public function addFilter($key, $value, $glue = 'OR') {
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
	public function setQueryLimit($limit) {
		$this->queryLimit = (integer) $limit;
	}

	/**
	 * The max number of results to return
	 * @return integer
	 */
	public function getQueryLimit() {
		return $this->queryLimit;
	}

	/**
	 * The query offset
	 * @var integer
	 */
	public function setQueryOffset($offset) {
		$this->queryOffset = (integer) $offset;
	}

	/**
	 * The query offset
	 * @return integer
	 */
	public function getQueryOffset() {
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
     * @return QueryBuilder
     */
	protected function getQueryBuilder() {
        $typoscriptConfiguration = Util::getSolrConfiguration();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class, $typoscriptConfiguration);
        return $queryBuilder
            ->newSearchQuery($this->getKeywords())
            ->useReturnFieldsFromTypoScript()
            ->useQueryFieldsFromTypoScript()
            ->useInitialQueryFromTypoScript()
            ->useFiltersFromTypoScript()
            ->useFacetingFromTypoScript()
            ->useVariantsFromTypoScript()
            ->useGroupingFromTypoScript()
            ->useHighlightingFromTypoScript()
            ->usePhraseFieldsFromTypoScript()
            ->useBigramPhraseFieldsFromTypoScript()
            ->useTrigramPhraseFieldsFromTypoScript();
    }

    /**
     * @return \ApacheSolrForTypo3\Solr\Domain\Search\Query\Query
     */
	protected function getQuery() {
        return $this->getQueryBuilder()->getQuery();
    }

	/**
	 *
	 *
	 */
	private function executeQuery() {
		$this->queryExecuted = true;

		$solrConnection = GeneralUtility::makeInstance(ConnectionManager::class)->getAllConnections()[0];
		/** @var Search $search */
		$search = GeneralUtility::makeInstance(Search::class, $solrConnection);
		/** @var Query $query */

		$queryBuilder = $this->getQueryBuilder();
        /** @var QueryFields $queryFields */

        $queryBuilder->useUserAccessGroups($this->userAccessGroups);

		$solrConfiguration = Util::getSolrConfiguration();

		if($this->hasSorting()) {
			$sortArray = $this->sorting;

			// Handle the case where the sorting array is like array('field', 'asc') instead of array('field' => 'asc')
			if (count($sortArray) == 2 && is_numeric(key($sortArray))) {
				$sortArray = array($sortArray[0] => $sortArray[1]);
			}

			foreach ($sortArray as $field => $dir) {
				if ($dir != Sorting::SORT_DESC && $dir != Sorting::SORT_ASC) {
					$dir = Sorting::SORT_ASC; // an implicit default
				}
				$sorting = GeneralUtility::makeInstance(Sorting::class, true, $field, $dir);
				$queryBuilder->useSorting($sorting);
			}
		}
		if($this->boostQuery) {
            $queryBuilder->useBoostQueries($this->boostQuery);
		}

        $searchComponents = GeneralUtility::makeInstance(Search\SearchComponentManager::class)->getSearchComponents();
		$config = $solrConfiguration->getSearchConfiguration();
		/** @var Search\FacetingComponent $facetingComponent */
        if ($facetingComponent = $searchComponents['faceting']) {
            $facetingComponent->setSearchConfiguration($config);
            $facetingComponent->initializeSearchComponent();
        }


		// Get default filters from the SOLR search configuration
		if (isset($config['query.']['filter.']) && is_array($config['query.']['filter.'])) {
			$defaultFilters = array();
			foreach ($config['query.']['filter.'] as $searchFilter) {
				$parts = explode(':', $searchFilter);
				$defaultFilters[$parts[0]] = $parts[1];
			}
			// Calling this will trigger any subclasses that may need to make modifications
			$this->setFilters($defaultFilters);
		}

        $queryBuilder->useSpellcheckingFromTypoScript();
        $query = $queryBuilder->getQuery();

        if (is_array($this->filters) && count($this->filters) > 0) {
            foreach ($this->filters as $filter) {
				$query->addFilterQuery(['key' => md5($filter), 'query' => $filter]);
            }
        }

		$this->response = $search->search($query, $this->getQueryOffset(), $this->getQueryLimit());
		$this->body = json_decode($this->response->getRawResponse());
		$this->search = $search;
		$this->query = $query;
		$this->numFound = $this->body->response->numFound;
	}

	/**
	 *
	 */
	public function getResultDocuments() {
		$this->executeQuery();
        return $this->search->getResponseBody()->docs;
	}

    /**
     * Return the response body
     * @return array
     */
	public function getBody() {
		return $this->body;
    }

    /**
     * @param array $groups
     */
    public function setUserAccessGroups($groups = []) {
        if (!is_array($groups)) {
            return;
        }
        $this->userAccessGroups = $groups;
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
	 * @param bool|mixed|object $boostQuery
	 */
	public function setBoostQuery($boostQuery) {
		$this->boostQuery = $boostQuery;
	}

	/**
	 * @return bool|mixed|object
	 */
	public function getBoostQuery() {
		return $this->boostQuery;
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

    public function debugQuery() {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->query);
    }

    /**
     * @return mixed
     */
    public function getResultsCount() {
        return $this->search->getNumberOfResults();
    }

}
