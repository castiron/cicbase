<?php
namespace CIC\Cicbase\Controller;


use CIC\Cicbase\Domain\Model\EmailTemplate;
use TYPO3\CMS\Core\Messaging\FlashMessage;

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
	 * @ignorevalidation $emailTemplate
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
	 * @ignorevalidation $emailTemplate
	 */
	public function editAction(EmailTemplate $emailTemplate) {
		$this->view->assignMultiple(array(
			'emailTemplate' => $emailTemplate
		));
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function deleteAction(EmailTemplate $emailTemplate) {
		$templateName = $emailTemplate->getTemplateKey();
		$this->emailTemplateRepository->remove($emailTemplate);
		$this->flash("Deleted Email Template: $templateName");
		$this->redirect('list');
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function createAction(EmailTemplate $emailTemplate) {
		$templateName = $emailTemplate->getTemplateKey();
		$this->emailTemplateRepository->add($emailTemplate);
		$this->flash("Created Email Template: $templateName");
		$this->redirect('list');
	}

	/**
	 * @param EmailTemplate $emailTemplate
	 */
	public function updateAction(EmailTemplate $emailTemplate) {
		$templateName = $emailTemplate->getTemplateKey();
		$this->emailTemplateRepository->update($emailTemplate);
		$this->flash("Updated Email Template: $templateName");
		$this->redirect('list');
	}


	/**
	 * @param string $message
	 * @param string $title
	 * @param int $severity
	 * @throws \TYPO3\CMS\Core\Exception
	 */
	protected function flash($message, $title = '', $severity = FlashMessage::OK) {
		$this->controllerContext->getFlashMessageQueue()->enqueue(new FlashMessage($message, $title, $severity, TRUE));
	}

	/**
	 * @return string
	 */
	protected function getErrorFlashMessage() {
		$vr = $this->arguments->getValidationResults();
		$allErrors = array();
		foreach ($vr->getFlattenedErrors() as $prop => $errors) {
			foreach ($errors as $error) {
				$allErrors[] = $error->getMessage();
			}
		}
		return implode(' ', $allErrors);
	}


}

?>