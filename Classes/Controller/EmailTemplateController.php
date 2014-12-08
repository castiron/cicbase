<?php
namespace CIC\Cicbase\Controller;


use CIC\Cicbase\Domain\Model\EmailTemplate;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class EmailTemplateController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \CIC\Cicbase\Domain\Repository\EmailTemplateRepository
	 * @inject
	 */
	protected $emailTemplateRepository;

	/**
	 * @var \CIC\Cicbase\Service\EmailService
	 * @inject
	 */
	protected $emailService;

	public function listAction() {
		$this->view->assignMultiple(array(
			'templates' => $this->emailTemplateRepository->findAll(),
		));
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function newAction(EmailTemplate $emailTemplate = NULL) {
		$keys = $this->getAvailableTemplateKeys();
		$this->view->assignMultiple(array(
			'templateKeys' => $keys,
			'noKeys' => ! count($keys)
		));
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function editAction(EmailTemplate $emailTemplate) {
		$this->view->assignMultiple(array(
			'emailTemplate' => $emailTemplate
		));
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function createAction(EmailTemplate $emailTemplate) {
		$this->emailTemplateRepository->add($emailTemplate);
		$this->redirect('list');
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function updateAction(EmailTemplate $emailTemplate) {
		$this->emailTemplateRepository->update($emailTemplate);
		$this->redirect('list');
	}

	protected function getAvailableTemplateKeys() {
		$exts = array();
		$keys = array();
		if (isset($this->settings['emailTemplateOverrides']['extensions'])) {
			$exts = $this->settings['emailTemplateOverrides']['extensions'];
			if (!is_array($exts) || !count($exts)) {
				return array();
			}
		}

		foreach ($exts as $extKey => $extPlugin) {
			$extSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extKey, $extPlugin);
			if (!is_array($extSettings) || !isset($extSettings['email']['templates'])) continue;
			$templates = $extSettings['email']['templates'];
			$keys[$extKey] = array_keys($templates);
		}

		$this->emailTemplateRepository->filterOutExistingTemplateKeys($keys);
		return $keys;

	}

}

?>