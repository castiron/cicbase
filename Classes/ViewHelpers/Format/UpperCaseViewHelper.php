<?php

namespace CIC\Cicbase\ViewHelpers\Format;

class UpperCaseViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @return string
     */
    public function render() {
        $text = $this->renderChildren();
        return strtoupper($text);
    }
}
?>