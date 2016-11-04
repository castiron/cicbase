<?php namespace CIC\Cicbase\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class AddFooterDataViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class AddFooterDataViewHelper extends AbstractTagBasedViewHelper {
    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     * @inject
     */
    protected $pageRenderer;

    /**
     *
     */
    public function render() {
        if ($contents = $this->renderChildren()) {
            $this->pageRenderer->addFooterData($contents);
        }
    }
}
