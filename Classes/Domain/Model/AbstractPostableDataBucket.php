<?php namespace CIC\Cicbase\Domain\Model;
use CIC\Cicbase\Contracts\PostableDataBucket;

/**
 * Class AbstractPostableDataBucket
 * @package CIC\Cicbase\Domain\Model
 */
class AbstractPostableDataBucket implements PostableDataBucket {
    /**
     * @var array
     */
    protected $data = [];
    protected $mappingConfig = [];

    /**
     * AbstractLeadDataBucket constructor.
     * @param $data
     */
    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function mappedData() {
        return $this->data;
    }

    /**
     * @param array $mappingConfig
     */
    public function setMappingConfig($mappingConfig = []) {
        $this->mappingConfig = $mappingConfig;
    }

}
