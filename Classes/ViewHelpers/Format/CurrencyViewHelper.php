<?php

namespace CIC\Cicbase\ViewHelpers\Format;

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Fluid".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Formats a given float to a currency representation.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <cic:format.currency>123.456</cic:format.currency>
 * </code>
 * <output>
 * 123,46
 * </output>
 *
 * <code title="All parameters">
 * <cic:format.currency currencySign="$" decimalSeparator="." thousandsSeparator=",">54321</cic:format.currency>
 * </code>
 * <output>
 * 54,321.00 $
 * </output>
 *
 * <code title="Inline notation">
 * {someNumber -> cic:format.currency(thousandsSeparator: ',', currencySign: '€')}
 * </code>
 * <output>
 * 54,321,00 €
 * (depending on the value of {someNumber})
 * </output>
 *
 * @api
 */
class CurrencyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $currencySign (optional) The currency sign, eg $ or €.
	 * @param string $decimalSeparator (optional) The separator for the decimal point.
	 * @param string $thousandsSeparator (optional) The thousands separator.
	 * @param bool $prefix (optional) If true, the sign will be a in front of the digits.
	 * @param int $decimals (optional) The number of digits after the decimal.
	 * @return string
	 */
	public function render($currencySign = '', $decimalSeparator = ',', $thousandsSeparator = '.', $prefix = false, $decimals = 2) {
		$stringToFormat = $this->renderChildren();
		$output = number_format($stringToFormat, $decimals, $decimalSeparator, $thousandsSeparator);
		if($currencySign !== '') {
			if($prefix) {
				$output = $currencySign.$output;
			} else {
				$output.= ' ' . $currencySign;
			}
		}
		return $output;
	}
}
?>