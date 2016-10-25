<?php namespace CIC\Cicbase\Domain\Model;
use CIC\Cicbase\Traits\ExtbaseInstantiable;

/**
 * Class AbstractPageBasedModel
 * @package CIC\Cicbase\Domain\Model
 */
class AbstractPageBasedModel extends AbstractArrayBasedModel {
    use ExtbaseInstantiable;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @inject
     */
    var $contentObjectRenderer;

    /**
     * @return string
     */
    public function getUrl() {
        return $this->contentObjectRenderer->getTypoLink_URL($this->rec['uid']);
    }
}
