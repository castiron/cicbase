<?php

class Tx_Cicbase_Service_LoggerService implements \t3lib_Singleton {


	/**
	 * @param string $extension
	 * @param int $line
	 * @param string $msg
	 * @param bool $returnVal
	 * @return bool
	 */
	public static function log($extension, $line, $msg, $returnVal = TRUE) {
		try {
			$fieldValues = array(
				'userid' => 0,
				'type' => 4,
				'action' => 0,
				'error' => t3lib_div::SYSLOG_SEVERITY_INFO,
				'details_nr' => 0,
				'details' => $msg,
				'IP' => '',
				'tstamp' => time(),
				'workspace' => 0,
				'log_data' => serialize(array('extension' => $extension, 'line' => $line)),
			);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_log', $fieldValues);
		} catch (\Exception $e) {
			// nix
		}
		return $returnVal;
	}
}