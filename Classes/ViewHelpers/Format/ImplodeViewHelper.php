<?php namespace CIC\Cicbase\ViewHelpers\Format;

class ImplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
    /**
     *
     */
    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'An array', true);
        $this->registerArgument('separator', 'string', 'A separator', false, ', ');
        $this->registerArgument('prependToLast', 'string', 'Use if you need to add something like \'and\' before the last element in the list. Spaces added for you. Assumes oxford comma.', false, '');
    }

    /**
	 * @return string
	 */
	public function render() {
	    $array = $this->arguments['array'];
	    $separator = $this->arguments['separator'];
	    $prependToLast = $this->arguments['prependToLast'];

		if ($prependToLast) {
			$len = count($array);
			$vals = array_values($array);

			switch ($len) {
				case 0: return '';
				case 1: return $vals[0];
				case 2: return $vals[0].' '.trim($prependToLast, ' ').' '.$vals[1];
				default:
					$i = $len - 1;
					$vals[$i] = trim($prependToLast, ' ') . ' ' . $vals[$i];
					return implode($separator, $vals);
			}
		}
		return implode($separator, $array);
	}
}
