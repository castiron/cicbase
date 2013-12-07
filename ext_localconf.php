<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Cicbase_Command_ExampleCommandController';

#$signalSlotDispatcher = t3lib_div::makeInstance('Tx_Extbase_Object_Manager')->get('Tx_Extbase_SignalSlot_Dispatcher');
#$signalSlotDispatcher->connect('Controller', 'ProcessUpload', 'Tx_Cicbase_Factory_FileFactory', 'HandleProcessUploadSignal', TRUE);

// TODO: Caching config should be updated to 4.6.x methods.
// If cache is not already defined, define it
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cicbase_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cicbase_cache'] = array();
}

if (t3lib_div::compat_version('6')) {
	Tx_Extbase_Utility_Extension::registerTypeConverter('CIC\Cicbase\Property\TypeConverter\FileReferenceConverter');
}

?>