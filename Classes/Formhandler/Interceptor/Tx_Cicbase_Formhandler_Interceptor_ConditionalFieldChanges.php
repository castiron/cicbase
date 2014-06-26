<?php

namespace CIC\Cicbase\Formhandler\Interceptor;

/**
 * <code>
 * saveInterceptors.1.class = CIC\Cicbase\Formhandler\Interceptor\ConditionalFieldChanges
 * saveInterceptors.1.config.fields {
 *			1 {
 *				field = LeadSource
 *				value = admin-alt@domain.com
 *
 *				# In reality, you'd only use one of the two of these, if or unless
 *				unless = equals
 *				unless {
 *					field = lead_source
 * 					value = SearchEngine
 *				}
 *				if = equals
 * 				if {
 *					field = lead_source
 *					value = Another value
 * 				}
 * 			}
 * 		}
 * }
 * </code>
 */
class ConditionalFieldChanges extends \Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		foreach($this->settings as $fieldConf) {
			$fieldName = trim($fieldConf['field']);
			$this->gp[$fieldName] = $this->checkShouldModify($fieldConf, $this->gp) ? $fieldConf['value'] : $this->gp[$fieldName];
		}
		return $this->gp;
	}

	/**
	 * Presently only checks the "unless" => "equals" condition type
	 * @param $fieldconf
	 * @param $gp
	 */
	private function checkShouldModify($fieldConf,$gp) {
		if($fieldConf['unless'] == 'equals') {
			return $this->checkUnless($fieldConf['unless.'],$gp);
		} elseif($fieldConf['if'] == 'equals') {
			return !$this->checkUnless($fieldConf['unless.'],$gp);
		}
	}

	/**
	 *
	 */
	private function checkUnless($unlessConf, $gp) {
		$field = $unlessConf['field'];
		$value = $unlessConf['value'];

		return $gp[$field] !== $value;
	}


}
?>
