<?php namespace CIC\Cicbase\Contracts;

/**
 * Interface SaveableInterface
 * @package CIC\Cicbase\Contracts
 */
interface SaveableInterface {
    /**
     * Persist this record to the database
     * @return mixed
     */
    public function save();
}
