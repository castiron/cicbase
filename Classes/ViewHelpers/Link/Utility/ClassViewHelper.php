<?php
namespace CIC\Cicbase\ViewHelpers\Link\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This allows you to extract just the class attribute from a typolink 'parameter' of the form
 * "$pageOrUrl $target $class $whatever", which is allowed in typolink parameter values, normally
 *
 * E.g. in your Fluid view:
 *   ... class="some-class-whatevs {c:link.utility.class(parameter: myFileReference.link)}" ...
 *
 * Class ClassViewHelper
 * @package CIC\Cicbase\ViewHelpers\Link
 */
class ClassViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $parameter The value of the parameter for which you want to extract the link
     * @return mixed
     */
    public function render($parameter) {
        $out = '';
        $parts = GeneralUtility::trimExplode(' ', $parameter);
        if($parts[2]) {
            $out = $parts[2];
        }

        return $out;
    }
}

