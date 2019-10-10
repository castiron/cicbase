<?php
namespace CIC\Cicbase\Domain\Model;

class EmailTemplate extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/** @var bool */
	protected $isDraft;

	/**
	 * @var string
	 */
	protected $body;

	/** @var string */
	protected $templateKey;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $subject;

	/**
	 * @return boolean
	 */
	public function isIsDraft() {
		return $this->isDraft;
	}

	/**
	 * @param boolean $isDraft
	 */
	public function setIsDraft($isDraft) {
		$this->isDraft = $isDraft;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @param string $body
	 */
	public function setBody($body) {
		$this->body = $body;
	}


	/**
	 * @return string
	 */
	public function getTemplateKey() {
		return $this->templateKey;
	}

	/**
	 * @param string $templateKey
	 */
	public function setTemplateKey($templateKey) {
		$this->templateKey = $templateKey;
	}
}