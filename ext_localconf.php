<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'CIC\Cicbase\Command\ExampleCommandController';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'CIC\Cicbase\Command\MigrationCommandController';

#$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Extbase_Object_Manager')->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
#$signalSlotDispatcher->connect('Controller', 'ProcessUpload', 'CIC\Cicbase\Factory\FileFactory', 'HandleProcessUploadSignal', TRUE);

// TODO: Caching config should be updated to 4.6.x methods.
// If cache is not already defined, define it
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cicbase_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['cicbase_cache'] = array();
}

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cicbase']['enableSQLLogging']) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_db.php']['queryProcessors'][] = 'CIC\Cicbase\Persistence\SQLLogger';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\CIC\Cicbase\Property\TypeConverter\File::class);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory'] = ['className' => 'CIC\Cicbase\Persistence\QueryFactory'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\QueryFactoryInterface'] = ['className' => 'CIC\Cicbase\Persistence\QueryFactory'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator'] = ['className' => 'CIC\Cicbase\Validation\Validator\EmailAddressValidator'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3Fluid\Fluid\Core\Parser\TemplateParser'] = ['className' => 'CIC\Cicbase\View\TemplateParser'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Validation\ValidatorResolver'] = ['className' => 'CIC\Cicbase\Validation\ValidatorResolver'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyDeliverer'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyDenierInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyDenier'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyGatewayInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyGateway'];


# TODO: does this cause problems? Appears to have been commented out after v8 upgrade
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] =
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Hooks/DatabaseRecordList.php:CIC\Cicbase\Hooks\DatabaseRecordList';
