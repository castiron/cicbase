<?php
namespace CIC\Cicbase\Property\TypeConverter;

use CIC\Cicbase\Domain\Model\FileReference;

/**
 * Class FileReferenceConverter
 *
 * Converts $_FILE arrays into FileReference objects.
 *
 *
 * Typoscript settings (required)
 * You can also set maxSize and allowedMimes in the controller
 * as type convert options
 * ========================
 *
 * plugin.tx_ext.settings.files {
 *   file { # default propertyPath
 *     # ...
 *   }
 *   partner_image {
 *     maxSize = 20971520
 *     allowedMimes {
 *       bmp = image/bmp
 *       gif = image/gif
 *       jpeg = image/jpeg,image/jpg
 *       jpg = image/jpeg,image/jpg
 *       png = image/png
 *     }
 *   }
 *   partner_documents {
 *     dontValidateMime = 1
 *     dontValidateSize = 1
 *   }
 * }
 *
 *
 *
 * Controller setup (required)
 * ========================
 *
 * $this->arguments->getArgument('partner')->getPropertyMappingConfiguration()
 *   ->forProperty('image')
 *   ->setTypeConverter($fileConverter)
 *   ->setTypeConverterOption($fileConverterName, 'additionalReferenceProperties', array('tablenames' => 'tx_orbest_domain_model_partner'))
 *   ->setTypeConverterOption($fileConverterName, 'propertyPath', 'partner.image');
 *
 *
 *
 * Repository setup (required)
 * ========================
 *
 * public function add($partner) {
 *   $this->saveFiles($partner);
 *   parent::add($partner);
 * }
 *
 * public function update($partner) {
 *   $this->saveFiles($partner);
 *   parent::update($partner);
 * }
 *
 * protected function saveFiles(Partner $partner) {
 *   $image = $partner->getImage();
 *   if ($image) {
 *     $this->fileReferenceFactory->saveOneToOne($partner, 'image', $image, 'partner.image');
 *   }
 *   $documents = $partner->getDocuments();
 *   $this->fileReferenceFactory->saveAll($partner, 'documents', $documents, 'document');
 * }
 *
 * @package CIC\Cicbase\Property\TypeConverter
 */
class FileReferenceConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter {

	/** @var array  */
	protected $settings = array();

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
	protected $targetType = '\TYPO3\CMS\Extbase\Domain\Model\FileReference';

	/**
	 * The priority for this converter.
	 *
	 * @var integer
	 * @api
	 */
	protected $priority = 2;

	/**
	 * @var \CIC\Cicbase\Factory\FileReferenceFactory
	 * @inject
	 */
	protected $fileFactory;

	/**
	 * @var \CIC\Cicbase\Persistence\LimboInterface
	 * @inject
	 */
	protected $limbo;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
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
		return !is_numeric($source);
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
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return mixed|null|\CIC\Cicbase\Domain\Model\FileReference|\TYPO3\CMS\Extbase\Error\Error
	 * @throws \TYPO3\CMS\Extbase\Configuration\Exception
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if (is_numeric($source)) {
			return $this->fetchObjectFromPersistence($source, $targetType);
		}

		$thisClass = get_class($this);
		$propertyPath = $configuration->getConfigurationValue($thisClass, 'propertyPath');


		if(!$propertyPath) {
			$propertyPath = 'file';
			$key = '';
		} else {
			$key = $propertyPath;
		}

		if(!$this->fileFactory->wasUploadAttempted($propertyPath)) {
			$fileReference = $this->limbo->getHeld($key);
			if($fileReference instanceof FileReference) {
				return $fileReference;
			} else {
				return NULL;
			}
		}

		$propertyPathUnderscores = str_replace('.', '_', $propertyPath);
		$conf = $this->settings['files'][$propertyPathUnderscores];

		$allowedTypes = $configuration->getConfigurationValue($thisClass, 'allowedMimes');
		$maxSize = $configuration->getConfigurationValue($thisClass, 'maxSize');
		if(!$allowedTypes && isset($conf['allowedMimes'])) {
			$allowedTypes = $conf['allowedMimes'];
		}
		if(!$maxSize && isset($conf['maxSize'])) {
			$maxSize = $conf['maxSize'];
		}

		// Too risky to use this type converter without some settings in place.
		if(!$maxSize && (!isset($conf['dontValidateSize']) || !$conf['dontValidateSize'])) {
			throw new \TYPO3\CMS\Extbase\Configuration\Exception('Before you can use the file type converter, you must set a
			 maxSize value in the settings section of your extension typoscript, or in the file type converter
			 configuration. You can also get this error if your upload input is not named properly.', 1337043345);
		}
		if ((!is_array($allowedTypes) || count($allowedTypes) == 0) && (!isset($conf['dontValidateMime']) || !$conf['dontValidateMime'])) {
			throw new \TYPO3\CMS\Extbase\Configuration\Exception('Before you can use the file type converter, you must configure
			 allowedMimes settings section of your extension typoscript, or in the file type converter
			 configuration. You can also get this error if your upload input is not named properly.', 1337043346);
		}

		$additionalReferenceProperties = $configuration->getConfigurationValue($thisClass, 'additionalReferenceProperties');

		$reference = $this->fileFactory->createFileReference($propertyPath, $additionalReferenceProperties, $allowedTypes, $maxSize);

		return $reference;
	}

}

?>