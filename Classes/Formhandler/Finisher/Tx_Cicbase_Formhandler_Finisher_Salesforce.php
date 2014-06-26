<?php

namespace CIC\Cicbase\Formhandler\Finisher;

/**
 * This finisher clears the posts form data to salesforce
 *
 * Example configuration:
 *
 * <code>
 * finishers.2.class = CIC\Cicbase\Formhandler\Finisher\Salesforce
 * finishers.2.config.oid = 00392842
 * finishers.2.config.debug = 0
 * finishers.2.config.email = jerryspringer@coolesttalkshowhost.com
 * finishers.2.config.mapping.first_name = Jerry
 * finishers.2.config.mapping.last_name = Springer
 * finishers.2.config.mapping.phone = 919-393-8293
 * finishers.2.config.mapping.company = Jerry Springer, LLC.
 * finishers.2.config.mapping.lead_source = Article Review
 * </code>
 */

class Salesforce extends \Tx_Formhandler_AbstractFinisher {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		$salesforceLead = new \CIC\Cicbase\Domain\Model\SalesforceLead;
		$salesforceLead->setOid($this->settings['oid']);
		$salesforceLead->setDebug($this->settings['debug']);
		$salesforceLead->setDebugEmail($this->settings['debugEmail']);
		$salesforceLead->setUseSandbox($this->settings['useSandbox'] ? true : false);

		foreach($this->settings['mapping.'] as $localField => $sfField) {
			if(is_string($sfField)) {
				if($this->settings['mapping.'][$localField.'.']['mapOnlyIfNotEmpty'] && empty($this->gp[$localField])) continue;
				$salesforceLead->set($sfField,$this->gp[$localField]);
			}
		}

		$salesforceLead->curlPost();
		$response = $salesforceLead->getResponse();
		$this->gp['salesforce_success'] = substr_count($response, '200 OK') > 0  ? 'yes' : 'no';
		$this->gp['salesforce_response'] = $response;
		$this->gp['salesforce_request_header'] = $salesforceLead->getRequestHeader();
		$this->gp['salesforce_request_string'] = $salesforceLead->getRequestString();

		return $this->gp;
	}
}
?>
