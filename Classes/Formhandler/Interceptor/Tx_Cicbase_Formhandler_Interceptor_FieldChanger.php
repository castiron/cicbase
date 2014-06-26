<?php

namespace CIC\Cicbase\Formhandler\Interceptor;

/**
 * <code>
 * saveInterceptors.1.class = CIC\Cicbase\Formhandler\Interceptor\FieldChanger
 * saveInterceptors.1.config.fields {
 * 			1.field {
 * 				name = fieldname
 * 				mapping {
 * 					oldval = newval
 * 			}
 * 		}
 * }
 * </code>
 */
class FieldChanger extends \Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		foreach($this->settings['fields.'] as $field) {
			$fieldname = $field['field.']['name'];
			$oldval = $this->gp[$fieldname];
			if(isset($field['field.']['mapping.'][$oldval]))
				$this->gp[$fieldname] = $field['field.']['mapping.'][$oldval];
		}
		return $this->gp;
	}
}
?>
