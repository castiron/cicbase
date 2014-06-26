<?php

namespace CIC\Cicbase\Property\TypeConverter;

class File extends \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter {

	/**
	 * The source types this converter can convert.
	 *
	 * @var array<string>
	 * @api
	 */
	protected $sourceTypes = array('array');

	/**
	 * The target type this converter can convert to.
	 *
	 * @var string
	 * @api
	 */
	protected $targetType = 'CIC\Cicbase\Domain\Model\File';

	/**
	 * The priority for this converter.
	 *
	 * @var integer
	 * @api
	 */
	protected $priority = 2;

	/**
	 * @var \CIC\Cicbase\Factory\FileFactory
	 */
	protected $fileFactory;

	/**
	 * @var \CIC\Cicbase\Domain\Repository\FileRepository
	 */
	protected $fileRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * inject the fileRepository
	 *
	 * @param \CIC\Cicbase\Domain\Repository\FileRepository fileRepository
	 * @return void
	 */
	public function injectFileRepository(\CIC\Cicbase\Domain\Repository\FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
	}

	/**
	 * inject the documentFactory
	 *
	 * @param \CIC\Cicbase\Factory\FileFactory documentFactory
	 * @return void
	 */
	public function injectFileFactory(\CIC\Cicbase\Factory\FileFactory $documentFactory) {
		$this->fileFactory = $documentFactory;
	}

	/**
	 * @param mixed $source
	 * @return array
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		return array();
	}

	/**
	 * This implementation always returns TRUE for this method.
	 *
	 * @param mixed $source the source data
	 * @param string $targetType the type to convert to.
	 * @return boolean TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
	 * @api
	 */
	public function canConvertFrom($source, $targetType) {
		return TRUE;
	}

	/**
	 * Return the target type this TypeConverter converts to.
	 * Can be a simple type or a class name.
	 *
	 * @return string
	 * @api
	 */
	public function getSupportedTargetType() {
		return $this->targetType;
	}

	/**
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param null|\TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return null|object|\CIC\Cicbase\Domain\Model\File|\TYPO3\CMS\Extbase\Error\Error|\TYPO3\CMS\Extbase\Error\Error
	 * @throws \TYPO3\CMS\Extbase\Configuration\Exception
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$propertyPath = $configuration->getConfigurationValue('CIC\Cicbase\Property\TypeConverter\File', 'propertyPath');
		if(!$propertyPath) {
			$propertyPath = 'file';
			$key = '';
		} else {
			$key = $propertyPath;
		}
		if(!$this->fileFactory->wasUploadAttempted($propertyPath)) {
			$fileObject = $this->fileRepository->getHeld($key);
			if($fileObject instanceof \CIC\Cicbase\Domain\Model\File) {
				return $fileObject;
			} else {
				// this is where we end up if no file upload was attempted (eg, form was submitted without a value
				// in the upload field, and we were unable to find a held file. In this case, return false, as though
				// nothing was ever posted.
				return NULL;
				// I thought this should return an error, at first, but instead we're going to treat this as though
				// nothing was posted at all... this allows for option file upload fields, I think.
				return new \TYPO3\CMS\Extbase\Error\Error('No file was uploaded.', 1336597083);
			}
		} else {
			// Otherwise, we create a new file object. Note that we use the fileFactory to turn $_FILE data into
			// a proper file object. Elsewhere, we use the fileRepository to retrieve file objects, even those that
			// haven't yet been persisted to the database;
			$allowedTypes = $configuration->getConfigurationValue('CIC\Cicbase\Property\TypeConverter\File', 'allowedTypes');
			$maxSize = $configuration->getConfigurationValue('CIC\Cicbase\Property\TypeConverter\File', 'maxSize');
			if(!$allowedTypes) {
				$allowedTypes = $this->settings['fileAllowedMime'];
			}
			if(!$maxSize) {
				$maxSize = $this->settings['fileMaxSize'];
			}

			// Too risky to use this type converter without some settings in place.
			if(!$maxSize) {
				throw new \TYPO3\CMS\Extbase\Configuration\Exception('Before you can use the file type converter, you must set a
				 fileMaxSize value in the settings section of your extension typoscript, or in the file type converter
				 configuration.', 1337043345);
			}
			if (!is_array($allowedTypes) && count($allowedTypes) == 0) {
				throw new \TYPO3\CMS\Extbase\Configuration\Exception('Before you can use the file type converter, you must configure
				 fileAllowedMime settings section of your extension typoscript, or in the file type converter
				 configuration.', 1337043346);
			}

			$result = $this->fileFactory->createFile($source, $propertyPath, $allowedTypes, $maxSize);
			return $result;
		}
	}

}

?>