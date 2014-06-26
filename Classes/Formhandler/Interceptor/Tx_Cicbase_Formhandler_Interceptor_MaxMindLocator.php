<?php

namespace CIC\Cicbase\Formhandler\Interceptor;

/**
 * Example configuration:
 *
 * <code>
 * 2.class = CIC\Cicbase\Formhandler\Interceptor\MaxMindLocator
 * 2.config {
 * 		maxMindId = oDgEqB1ewIVa
 *		type = city
 *
 * 		# If the result is expected to be a comma list, then take the array item
 * 		# from the value on the left and put it on the field on the right
 * 		resultArrayFieldMapping {
 * 		1 = state
 *		2 = city
 * 		}
 *
 * 		# If the result is expected to be a string, just plop the value into this field
 * 		# resultStringField = country
 * }
 * </code>
 */
class MaxMindLocator extends \Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {
		if($this->settings['type'] == 'city') {
			$queryStringCode = 'f';
		} else {
			$queryStringCode = 'a';
		}
		$maxmindAccountId = $this->settings['maxMindId'];
		$ip = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR');

		if($this->settings['debugForceIp']) {
			$ip = $this->settings['debugForceIp'];
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://geoip3.maxmind.com/'.$queryStringCode.'?l='.$maxmindAccountId.'&i='.$ip);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);

		switch($queryStringCode) {
			case 'f':
				$array = explode(',', $result);
				foreach($this->settings['resultArrayFieldMapping.'] as $index => $name) {
					$this->gp[$name] = $array[$index];
				}
				break;
			case 'a':
				$this->gp[$this->settings['resultStringField']] = $result;
				break;
		}


		return $this->gp;
	}
}
?>
