<?php

class Tx_Cicbase_Domain_Model_SalesforceLead {
	var $postDomain = 'www.salesforce.com';
	var $postSSL = false;
	var $postPath = '/servlet/servlet.WebToLead';
	var $encoding = 'UTF-8';
	var $oid = '';
	var $debug = false;
	var $debugEmail = '';
	var $submittedTstamp;
	var $response;

	private $data = array();

	public function setOid($oid) {
		$this->oid = $oid;
	}

	public function setDebug($boolean) {
		$this->debug = $boolean ? true : false;
	}

	public function setDebugEmail($email) {
		$this->debugEmail = $email;
	}

	/* Some Salesforce fields have names that can be class attribute names in PHP, so let's set them this way */
	public function set($fieldName,$fieldValue) {
		$this->data[$fieldName] = $fieldValue;
	}

	public function curlPost() {
		$req = '';
		$response = '';
		foreach($this->data as $fK => $fV) {
			$req .= '&'.urlencode($fK).'='.urlencode($fV);
		}

		if($this->debug) {
			$req .= '&debug=1';
		}

		if($this->debugEmail) {
			$req .= '&debugEmail='.$this->debugEmail;
		}

		if($this->oid) {
			$req .= '&oid='.$this->oid;
		}

		$header  = "POST {$this->postPath}?encoding={$this->encoding} HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Host: {$this->postDomain}\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		$fp = fsockopen ($this->postDomain, 80, $errno, $errstr, 30);
		if (!$fp) {
			$this->submittedTstamp = 0;
			return FALSE;
		} else {
			fputs($fp,$header.$req);

			$this->submittedTstamp = time();

			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				$response .= $res;
			}

			fclose($fp);
		}

		$this->response = $response;

		return TRUE;
	}

	public function getResponse() {
		return $this->response;
	}
}

?>