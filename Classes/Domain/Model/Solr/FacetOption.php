<?php namespace CIC\Cicbase\Domain\Model\Solr;

use CIC\Cicbase\Traits\ExtbaseInstantiable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FacetOption
 * @package CIC\Cicbase\Domain\Model\Solr
 */
class FacetOption {
    use ExtbaseInstantiable;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var FacetProxy
     */
    protected $facet;

    /**
     * FacetOption constructor.
     * @param $value
     * @param $count
     * @param $facet
     */
    public function __construct($value, $count, $facet = null) {
        $this->value = $value;
        $this->count = $count;
        $this->facet = $facet;
    }

    /**
     * @param array $rawOptions
     * @return array
     */
    public static function fromRawOptions($rawOptions = [], $facet) {
        $out = [];
        foreach ($rawOptions as $name => $count) {
            $out[] = static::get($name, $count, $facet);
        }
        return $out;
    }

    /**
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * @return bool
     */
    public function getActive() {
        $solrParams = GeneralUtility::_GP('tx_solr');
        $activeOptionParams = $solrParams['filter'];
        if (!is_array($activeOptionParams)) {
            return false;
        }
        return in_array($this->uriParameterValue(), $activeOptionParams);
    }

    /**
     * @return string
     */
    protected function uriParameterValue() {
        return $this->facet->getUriParameter($this->getValue());
    }

    /**
     * @return array
     */
    public function getUriParameter() {
        return array(
            'tx_solr' => array(
                'filter' => array(
                    $this->uriParameterValue()
                )
            )
        );
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }
}
