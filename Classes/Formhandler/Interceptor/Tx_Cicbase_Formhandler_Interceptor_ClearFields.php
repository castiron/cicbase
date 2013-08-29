<?php
/**
 * <code>
 * saveInterceptors.1.class = Tx_Cicbase_Formhandler_Interceptor_ClearFields
 * saveInterceptors.1.config.fields {
 *     credit_card_number
 *     credit_card_expiration_month
 *     credit_card_expiration_year
 *     credit_card_security_code
 *   }
 * }
 * </code>
 */
class Tx_Cicbase_Formhandler_Interceptor_ClearFields extends Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		foreach($this->settings['fields.'] as $fieldName => $enabled) {
			if(!$enabled) { continue; }
			if(isset($this->gp[$fieldName])){
				unset($this->gp[$fieldName]);
			}
		}
		return $this->gp;
	}
}
?>
