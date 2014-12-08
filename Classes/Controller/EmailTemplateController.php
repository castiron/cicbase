<?php
namespace CIC\Cicbase\Controller;


use CIC\Cicbase\Domain\Model\EmailTemplate;

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


	public function selectTemplateAction() {
		$keys = $this->emailService->getAvailableTemplateKeys();
		$this->view->assignMultiple(array(
			'templateKeys' => $keys,
			'noKeys' => ! count($keys)
		));
	}

	/**
	 * @param string $selectedTemplateKey
	 * @param EmailTemplate $emailTemplate
	 */
	public function newAction($selectedTemplateKey, EmailTemplate $emailTemplate = NULL) {
		$defaultBody = $this->emailService->getTemplateBodyFromKey($selectedTemplateKey);
		$this->view->assignMultiple(array(
			'selectedTemplateKey' => $selectedTemplateKey,
			'emailTemplate' => $emailTemplate,
			'defaultBody' => $defaultBody,
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



}

?>