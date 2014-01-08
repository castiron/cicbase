<?php
namespace CIC\Cicbase\Property\TypeConverter;

class FileReferenceConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter {

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
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 * @inject
	 */
	protected $fileFactory;

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 * @inject
	 */
	protected $fileRepository;

	/**
	 * @var \TYPO3\CMS\Core\Resource\StorageRepository
	 * @inject
	 */
	protected $folderRepository;

	/**
	 * @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility
	 * @inject
	 */
	protected $fileUtility;


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
	 * @return null|object|\TYPO3\CMS\Extbase\Domain\Model\FileReference|\TYPO3\CMS\Extbase\Error\Error
	 * @throws Tx_Extbase_Configuration_Exception
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {

		# Determine where we're going to save this file. Sorted by date.
		$relFolderPath = 'cicbase/uploads/'.date('Y').'/'.date('n').'/'.date('j');
		$absFolderPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(PATH_site."fileadmin/$relFolderPath");

		# Get a FolderObject whether we need to create it or not
		if(!is_dir($absFolderPath)) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep(PATH_site."fileadmin/$relFolderPath");
			$storage = $this->folderRepository->findByUid(1);
			$folder = $this->fileFactory->createFolderObject($storage, $relFolderPath, 'upload_folder');
		} else {
			$folder = $this->fileFactory->getFolderObjectFromCombinedIdentifier("1:$relFolderPath");
		}

		# Use the file hash as the name of the file
		$source['name'] = md5_file($source['tmp_name']) . '.' . pathinfo($source['name'], PATHINFO_EXTENSION);

		# Create the FileObject by adding the uploaded file to the FolderObject.
		$file = $folder->addUploadedFile($source, 'replace');

		# Build a FileReference object using the FileObject
		$ref = $this->fileFactory->createFileReferenceObject(array(
			'uid_local' => $file->getUid(),
			'table_local' => 'sys_file',
		));

		$this->fileRepository->add($file);

		# Convert the Core FileReference we made to an ExtBase FileReference
		$extbaseRef = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Domain\Model\FileReference');
		$extbaseRef->setOriginalResource($ref);
		return $extbaseRef;

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// OLD CODE:

		if(!$propertyPath) {
			$propertyPath = 'file';
			$key = '';
		} else {
			$key = $propertyPath;
		}
		if(!$this->fileFactory->wasUploadAttempted($propertyPath)) {
			$fileObject = $this->fileRepository->getHeld($key);
			if($fileObject instanceof Tx_Cicbase_Domain_Model_File) {
				return $fileObject;
			} else {
				// this is where we end up if no file upload was attempted (eg, form was submitted without a value
				// in the upload field, and we were unable to find a held file. In this case, return false, as though
				// nothing was ever posted.
				return NULL;
				// I thought this should return an error, at first, but instead we're going to treat this as though
				// nothing was posted at all... this allows for option file upload fields, I think.
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