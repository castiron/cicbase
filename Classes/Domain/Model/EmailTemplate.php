<?php
namespace CIC\Cicbase\Domain\Model;

class EmailTemplate extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/** @var bool */
	protected $isDraft;

	/** @var string */
	/**
	 * @var string
	 * @validate \CIC\Cicbase\Validation\Validator\FluidStringValidator
	 */
	protected $body;

	/** @var string */
	protected $templateKey;

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

?>