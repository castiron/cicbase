<?php
namespace CIC\Cicbase\Property\TypeConverter;

/**
 *
 * NOTICE:
 *
 * The only difference between this and the parent is that this one DOES NOT add empty objects
 * to the storage, thus avoiding php warnings when using spl_object_hash on an empty value.
 *
 *
 * Class ObjectStorageConverter
 * @package CIC\Orbest\Property\TypeConverter
 */
class ObjectStorageConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\ObjectStorageConverter {


	/**
	 * @var integer
	 */
	protected $priority = 2;


	/**
	 * Actually convert from $source to $targetType, taking into account the fully
	 * built $convertedChildProperties and $configuration.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$objectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		foreach ($convertedChildProperties as $subProperty) {
			if ($subProperty) {
				$objectStorage->attach($subProperty);
			}
		}
		return $objectStorage;
	}

}
?>