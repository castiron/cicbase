<?php

class Tx_Cicbase_Property_TypeConverter_File extends Tx_Extbase_Property_TypeConverter_PersistentObjectConverter {

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
	protected $targetType = 'Tx_Cicbase_Domain_Model_File';

	/**
	 * The priority for this converter.
	 *
	 * @var integer
	 * @api
	 */
	protected $priority = 2;

	/**
	 * @var Tx_Cicbase_Factory_FileFactory
	 */
	protected $fileFactory;

	/**
	 * @var Tx_Cicbase_Domain_Repository_FileRepository
	 */
	protected $fileRepository;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * inject the fileRepository
	 *
	 * @param Tx_Cicbase_Domain_Repository_FileRepository fileRepository
	 * @return void
	 */
	public function injectFileRepository(Tx_Cicbase_Domain_Repository_FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
	}

	/**
	 * inject the documentFactory
	 *
	 * @param Tx_Cicbase_Factory_FileFactory documentFactory
	 * @return void
	 */
	public function injectFileFactory(Tx_Cicbase_Factory_FileFactory $documentFactory) {
		$this->fileFactory = $documentFactory;
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
	 * Actually convert from $source to $targetType, taking into account the fully
	 * built $convertedChildProperties and $configuration.
	 * The return value can be one of three types:
	 * - an arbitrary object, or a simple type (which has been created while mapping).
	 *   This is the normal case.
	 * - NULL, indicating that this object should *not* be mapped (i.e. a "File Upload" Converter could return NULL if no file has been uploaded, and a silent failure should occur.
	 * - An instance of Tx_Extbase_Error_Error -- This will be a user-visible error message lateron.
	 * Furthermore, it should throw an Exception if an unexpected failure occured or a configuration issue happened.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param Tx_Extbase_Property_PropertyMappingConfigurationInterface $configuration
	 * @return mixed the target type
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), Tx_Extbase_Property_PropertyMappingConfigurationInterface $configuration = NULL) {
		$propertyPath = $configuration->getConfigurationValue('Tx_Cicbase_Property_TypeConverter_File', 'propertyPath');
		if(!$propertyPath) $propertyPath = 'file';
		if(!$this->fileFactory->wasUploadAttempted($propertyPath)) {
			$fileObject = $this->fileRepository->getHeld();
			if($fileObject instanceof Tx_Cicbase_Domain_Model_File) {
				return $fileObject;
			} else {
				// This is another edge case, but one that could happen if, for example, we cleaned out the temporary
				// upload folder in typo3temp while a user was in the middle of submitting a form, since the file
				// repository confirms that the file exists before it will return it as a held file.
				return new Tx_Extbase_Error_Error('No file was uploaded.', 1336597083);
			}
		} else {
			// Otherwise, we create a new file object. Note that we use the fileFactory to turn $_FILE data into
			// a proper file object. Elsewhere, we use the fileRepository to retrieve file objects, even those that
			// haven't yet been persisted to the database;
			$allowedTypes = $configuration->getConfigurationValue('Tx_Cicbase_Property_TypeConverter_File', 'allowedTypes');
			$maxSize = $configuration->getConfigurationValue('Tx_Cicbase_Property_TypeConverter_File', 'maxSize');
			if(!$allowedTypes) {
				$allowedTypes = $this->settings['fileAllowedMime'];
			}
			if(!$maxSize) {
				$maxSize = $this->settings['fileMaxSize'];
			}

			// Too risky to use this type converter without some settings in place.
			if(!$maxSize) {
				throw new Tx_Extbase_Configuration_Exception('Before you can use the file type converter, you must set a
				 fileMaxSize value in the settings section of your extension typoscript, or in the file type converter
				 configuration.', 1337043345);
			}
			if (!is_array($allowedTypes) && count($allowedTypes) == 0) {
				throw new Tx_Extbase_Configuration_Exception('Before you can use the file type converter, you must configure
				 fileAllowedMime settings section of your extension typoscript, or in the file type converter
				 configuration.', 1337043346);
			}

			$result = $this->fileFactory->createFile($source, $propertyPath, $allowedTypes, $maxSize);
			return $result;
		}
	}

}

?>