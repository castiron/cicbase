<?php
namespace CIC\Cicbase\Domain\Model;

class SalesforceLead {
	/**
	 *
	 */
	var $postDomain = 'www.salesforce.com';

	/**
	 * @var string
	 */
	var $sandboxPostDomain = 'test.salesforce.com';

	/**
	 * @var bool
	 */
	var $postSSL = false;

	/**
	 * @var string
	 */
	var $postPath = '/servlet/servlet.WebToLead';

	/**
	 * @var string
	 */
	var $encoding = 'UTF-8';

	/**
	 * @var string
	 */
	var $oid = '';

	/**
	 * @var bool
	 */
	var $debug = false;

	/**
	 * @var bool
	 */
	var $useSandbox = false;

	/**
	 * @var string
	 */
	var $debugEmail = '';

	/**
	 * @var integer
	 */
	var $submittedTstamp;

	/**
	 * @var string
	 */
	var $requestString;

	/**
	 * @var string
	 */
	var $requestHeader;

	/**
	 * @var string
	 */
	var $response;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @param $oid
	 */
	public function setOid($oid) {
		$this->oid = $oid;
	}

	/**
	 * @param $boolean
	 */
	public function setDebug($boolean) {
		$this->debug = $boolean ? true : false;
	}

	/**
	 * @param $email
	 */
	public function setDebugEmail($email) {
		$this->debugEmail = $email;
	}

	/**
	 * @param $useSandbox
	 */
	public function setUseSandbox($useSandbox) {
		$this->useSandbox = (boolean) $useSandbox;
	}

	/**
	 * @param $fieldName
	 * @param $fieldValue
	 */
	public function set($fieldName,$fieldValue) {
		$this->data[$fieldName] = $fieldValue;
	}

	/**
	 * @return bool
	 */
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

		$this->requestString = $req;

		$postDomain = $this->useSandbox ? $this->sandboxPostDomain : $this->postDomain;
		$header  = "POST {$this->postPath}?encoding={$this->encoding} HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Host: $postDomain\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		$this->requestHeader = $header;

		// Only uses port 80 right now
		$fp = fsockopen ($postDomain, 80, $errno, $errstr, 30);
		if (!$fp) {
			$this->submittedTstamp = 0;
			return FALSE;
		} else {
			fputs($fp,$header.$req);

			$this->submittedTstamp = time();

			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				$response .= $res;
				print $res;
			}

			fclose($fp);
		}

		$this->response = $response;

		return TRUE;
	}

	/**
	 * Mainly for debugging
	 * @return string
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Mainly for debugging
	 * @return string
	 */
	public function getRequestString() {
		return $this->requestString;
	}

	/**
	 * Mainly for debugging
	 * @return string
	 */
	public function getRequestHeader() {
		return $this->requestHeader;
	}

}

?>