<?php

namespace CIC\Cicbase\ViewHelpers\Format;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 */

/**
 * Renders a string by passing it to a TYPO3 parseFunc.
 * You can either specify a path to the TypoScript setting or set the parseFunc options directly.
 * By default lib.parseFunc_RTE is used to parse the string.
 *
 * Example:
 *
 * (1) default parameters:
 * <f:format.html>foo <b>bar</b>. Some <LINK 1>link</LINK>.</f:format.html>
 *
 * Result:
 * <p class="bodytext">foo <b>bar</b>. Some <a href="index.php?id=1" >link</a>.</p>
 * (depending on your TYPO3 setup)
 *
 * (2) custom parseFunc
 * <f:format.html parseFuncTSPath="lib.parseFunc">foo <b>bar</b>. Some <LINK 1>link</LINK>.</f:format.html>
 *
 * Output:
 * foo <b>bar</b>. Some <a href="index.php?id=1" >link</a>.
 *
 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4198758
 *
 */
class CropHtmlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var	\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var	t3lib_fe contains a backup of the current $GLOBALS['TSFE'] if used in BE mode
	 */
	protected $tsfeBackup;

	/**
	 * If the escaping interceptor should be disabled inside this ViewHelper, then set this value to FALSE.
	 * This is internal and NO part of the API. It is very likely to change.
	 *
	 * @var boolean
	 * @internal
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	/**
	 * This function extracts the non-tags string and returns a correctly formatted string
	 * It can handle all html entities e.g. &amp;, &quot;, etc..
	 *
	 * @param string $s
	 * @param integer $srt
	 * @param integer $len
	 * @param bool/integer	Strict if this is defined, then the last word will be complete. If this is set to 2 then the last sentence will be completed.
	 * @param string A string to suffix the value, only if it has been chopped.
	 */
	protected function htmlSubstr( $s, $srt, $len = NULL, $strict=false, $suffix = NULL ) {
		if ( is_null($len) ){ $len = strlen( $s ); }
		$f = 'static $strlen=0;
				if ( $strlen >= ' . $len . ' ) { return "><"; }
				$html_str = html_entity_decode( $a[1] );
				$subsrt   = max(0, ('.$srt.'-$strlen));
				$sublen = ' . ( empty($strict)? '(' . $len . '-$strlen)' : 'max(@strpos( $html_str, "' . ($strict===2?'.':' ') . '", (' . $len . ' - $strlen + $subsrt - 1 )), ' . $len . ' - $strlen)' ) . ';
				$new_str = substr( $html_str, $subsrt,$sublen);
				$strlen += $new_str_len = strlen( $new_str );
				$suffix = ' . (!empty( $suffix ) ? '($new_str_len===$sublen?"'.$suffix.'":"")' : '""' ) . ';
				return ">" . htmlentities($new_str, ENT_QUOTES, "UTF-8") . "$suffix<";';
		$out = preg_replace( array( "#<[^/][^>]+>(?R)*</[^>]+>#", "#(<(b|h)r\s?/?>){2,}$#is"), "", trim( rtrim( ltrim( preg_replace_callback( "#>([^<]+)<#", create_function(
				'$a',
				$f
	        ), ">$s<"  ), ">"), "<" ) ) );
		return $out;
	}

	/**
	 * @param string $parseFuncTSPath path to TypoScript parseFunc setup.
	 * @return the parsed string.
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Niels Pardon <mail@niels-pardon.de>
	 */
	public function render($parseFuncTSPath = 'lib.parseFunc_RTE') {

		if (TYPO3_MODE === 'BE') {
			$this->simulateFrontendEnvironment();
		}

		$value = $this->renderChildren();
		$value = $this->htmlSubstr($value,0,200,true,'...');
		\TYPO3\CMS\Core\Utility\DebugUtility::debug($value,'value');

		$content = $this->contentObject->parseFunc($value, array(), '< ' . $parseFuncTSPath);

		if (TYPO3_MODE === 'BE') {
			$this->resetFrontendEnvironment();
		}
		return $content;
	}

	/**
	 * Copies the specified parseFunc configuration to $GLOBALS['TSFE']->tmpl->setup in Backend mode
	 * This somewhat hacky work around is currently needed because the parseFunc() function of \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer relies on those variables to be set
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function simulateFrontendEnvironment() {
		$this->tsfeBackup = isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->tmpl->setup = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
	}

	/**
	 * Resets $GLOBALS['TSFE'] if it was previously changed by simulateFrontendEnvironment()
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see simulateFrontendEnvironment()
	 */
	protected function resetFrontendEnvironment() {
		$GLOBALS['TSFE'] = $this->tsfeBackup;
	}
}

?>