<?php namespace CIC\Cicbase\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class AddHeaderDataViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class AddHeaderDataViewHelper extends AbstractTagBasedViewHelper {
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
            $this->pageRenderer->addHeaderData($contents);
        }
    }
}
