<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'CIC\Cicbase\Command\ExampleCommandController';

#$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Extbase_Object_Manager')->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
#$signalSlotDispatcher->connect('Controller', 'ProcessUpload', 'CIC\Cicbase\Factory\FileFactory', 'HandleProcessUploadSignal', TRUE);

// TODO: Caching config should be updated to 4.6.x methods.
// If cache is not already defined, define it
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cicbase_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cicbase_cache'] = array();
}

if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('6')) {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter');
}

?>