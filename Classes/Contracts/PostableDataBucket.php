<?php namespace CIC\Cicbase\Contracts;

/**
 * Interface PostableDataBucket
 * @package CIC\Cicbase\Contracts
 */
interface PostableDataBucket {
    /**
     * Return the mapped data
     * @return array
     */
    public function mappedData();

    /**
     * @param array $config
     * @return void
     */
    public function setMappingConfig($config = []);
}
