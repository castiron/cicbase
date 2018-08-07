<?php namespace CIC\Cicbase\Domain\Model\Solr;

use ApacheSolrForTypo3\Solr\Facet\Facet;
use ApacheSolrForTypo3\Solr\Search;
use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Facet
 * @package CIC\Cicbase\Domain\Model
 */
class FacetProxy {
    use ExtbaseInstantiable;

    /**
     * @var Facet
     */
    protected $txSolrFacet;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * FacetProxy constructor.
     * @param Facet $txSolrFacet
     */
    public function __construct(Facet $txSolrFacet) {
        $this->txSolrFacet = $txSolrFacet;
    }

    /**
     * @return array
     */
    public function getOptions() {
        /**
         * Already fetched these
         */
        if (count($this->options)) {
            return $this->options;
        }

        $optionClass = $this->optionClass();
        return $this->options = $optionClass::fromRawOptions(
            static::_getSearch()->getFacetFieldOptions($this->getField()),
            $this
        );
    }

    /**
     * @return array
     */
    public function getActiveOptions() {
        return array_values(array_filter($this->getOptions(), function (FacetOption $option) {
            return $option->getActive();
        }));
    }

    /**
     * @return FacetOption|null
     */
    public function getFirstActiveOption() {
        $options = $this->getActiveOptions();
        return $options[0];
    }

    /**
     * @return mixed
     */
    protected function optionClass() {
        $conf = $this->getConfiguration();
        return $conf['optionClass'] ?: 'CIC\Cicbase\Domain\Model\Solr\FacetOption';
    }

    /**
     * @return string
     */
    public function getField() {
        return $this->txSolrFacet->getField();
    }

    /**
     * @param string $val
     * @return string
     */
    public function getUriParameter($val = '') {
        return "{$this->getName()}:$val";
    }

    /**
     * @return string
     */
    public function getLabel() {
        $conf = $this->getConfiguration();
        return $conf['label'];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->txSolrFacet->getName();
    }

    /**
     * @return array
     */
    public function getConfiguration() {
        return $this->txSolrFacet->getConfiguration();
    }

    /**
     * @return object
     */
    protected static function _getSearch() {
        return GeneralUtility::makeInstance(Search::class);
    }

}
