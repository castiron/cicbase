<?php namespace CIC\Cicbase\ViewHelpers\Fal;

use TYPO3\CMS\Core\Resource\File;

/**
 * Class CIC\Cicbase\ViewHelpers\Fal\ImageViewHelper
 */
class ImageReferenceViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
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
        $this->contentObject = $this->configurationManager->getContentObject();
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('uid', 'string', 'uid of the referenced element', true);
        $this->registerTagAttribute('tableName', 'string', 'The table name on the image reference', false, 'tt_content');
        $this->registerTagAttribute('fieldName', 'string', 'The field name on the image reference', false, 'media');
        $this->registerTagAttribute('fieldIndex', 'string', 'The ordinal index of the item in the relationship', false, '0');
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @return string
     */
    public function render()
    {
        $out = '';
        if ($file = $this->findFile()) {
            $this->arguments['src'] = $file->getCombinedIdentifier();
            $out = parent::render();
        }
        if ($this->arguments['urlOnly'] && preg_match('/src="([^"]*)"/', $out, $matches)) {
            $out = $matches[1];
        }
        return $out;
    }

    /**
     * @return File|null
     */
    protected function findFile()
    {
        $files = $this->fileRepository->findByRelation($this->arguments['tableName'], $this->arguments['fieldName'], $this->arguments['uid']);
        return $files[$this->arguments['fieldIndex']] ?: null;
    }
}

