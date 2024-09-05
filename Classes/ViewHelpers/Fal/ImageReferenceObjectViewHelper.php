<?php namespace CIC\Cicbase\ViewHelpers\Fal;


use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class CIC\Cicbase\ViewHelpers\Fal\ImageReferenceObjectViewHelper
 */
class ImageReferenceObjectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var \TYPO3\CMS\Core\Resource\FileRepository
     * @inject
     */
    protected $fileRepository;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'string', 'uid of the referenced element', true);
        $this->registerArgument('tableName', 'string', 'The table name on the image reference', false, 'tt_content');
        $this->registerArgument('fieldName', 'string', 'The field name on the image reference', false, 'media');
        $this->registerArgument('fieldIndex', 'string', 'The ordinal index of the item in the relationship', false, '0');
    }

    /**
     * Returns an image reference
     *
     * @return FileReference
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public function render()
    {
        $files = $this->fileRepository->findByRelation($this->arguments['tableName'], $this->arguments['fieldName'], $this->arguments['uid']);
        return $files[$this->arguments['fieldIndex']] ?: null;
    }
}

