<?php namespace CIC\Cicbase\Domain\Model;

use CIC\Cicbase\Contracts\DataPoster;
use CIC\Cicbase\Contracts\PostableDataBucket;
use TYPO3\CMS\Core\Error\Exception;

/**
 * Class AbstractLead
 * @package CIC\Cicbase\Domain\Model
 */
class AbstractDataPoster implements DataPoster {
    /**
     * @var PostableDataBucket
     */
    protected $dataBucket;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * AbstractDataPoster constructor.
     * @param PostableDataBucket $dataBucket
     * @param string $endpoint
     * @throws \TYPO3\CMS\Core\Error\Exception
     */
    public function __construct(PostableDataBucket $dataBucket, $endpoint = '') {
        if (!$endpoint) {
            throw new Exception('Cannot instanciate a data poster without an endpoint');
        }

        $this->dataBucket = $dataBucket;
        $this->endpoint = $endpoint;
    }

    /**
     *
     */
    public function post() {
        throw new Exception('You must extend the post() method in ' . __CLASS__);
    }

    /**
     * @return string
     */
    protected function endpoint() {
        return $this->endpoint;
    }

    /**
     * @return array
     */
    protected function dataToPost() {
        return $this->dataBucket->mappedData();
    }
}
