<?php

namespace CIC\Cicbase\ViewHelpers\Formhandler\Interceptor;

/**
 * <code>
 * saveInterceptors.1.class = CIC\Cicbase\ViewHelpers\Formhandler\Interceptor\EmptyIrrelevantFields
 * saveInterceptors.1.config.fields {
 *			lead_source_search_engine {
 *				unless = equals
 *				unless {
 *					field = lead_source
 * 					value = SearchEngine
 *				}
 * 			}
 * 		}
 * }
 * </code>
 */
class EmptyIrrelevantFields extends \Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		foreach($this->settings as $fieldName => $fieldConf) {
			$fieldName = substr($fieldName,0,-1);
			$this->gp[$fieldName] = $this->checkShouldEmpty($fieldConf, $this->gp) ? '' : $this->gp[$fieldName];
		}
		return $this->gp;
	}

	/**
	 * Presently only checks the "unless" => "equals" condition type
	 * @param $fieldconf
	 * @param $gp
	 */
	private function checkShouldEmpty($fieldConf,$gp) {
		if($fieldConf['unless'] == 'equals') {
			return $this->checkShouldEmptyUnless($fieldConf['unless.'],$gp);
		}
	}

	/**
	 *
	 */
	private function checkShouldEmptyUnless($unlessConf, $gp) {
		$field = $unlessConf['field'];
		$value = $unlessConf['value'];

		return $gp[$field] !== $value;
	}
}
?>