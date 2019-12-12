<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CICBase Static Typoscript');

if (TYPO3_MODE == 'BE') {
	// Older versions of ExtBase don't have CLI CommandManager
	if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler') && class_exists('TYPO3\CMS\Extbase\Mvc\Cli\CommandManager')) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['CIC\Cicbase\Scheduler\Task'] = array(
			'extension'        => $_EXTKEY,
			'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.name',
			'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.description',
			'additionalFields' => 'CIC\Cicbase\Scheduler\FieldProvider'
		);
	}

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'CIC.'.$_EXTKEY,
		'tools',	// Make module a submodule of 'tools'
		'cicbase',	// Submodule key
		'',			// Position
		array(
			'Migration' => 'index,run'
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cicbase.xml',
		)
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'CIC.'.$_EXTKEY,
		'web',	// Make module a submodule of 'tools'
		'emailtemplate',	// Submodule key
		'',			// Position
		array(
			'EmailTemplate' => 'list,selectTemplate,new,create,edit,update,delete'
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_emailtemplate.xml',
		)
	);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_cicbase_domain_model_file', 'EXT:cicbase/Resources/Private/Language/locallang_csh_tx_cicbase_domain_model_file.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_cicbase_domain_model_file');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_cicbase_domain_model_emailtemplate', 'EXT:cicbase/Resources/Private/Language/locallang_csh_tx_cicbase_domain_model_emailtemplate.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_cicbase_domain_model_emailtemplate');

# TODO: UPGRADE these overrides do not appear to working
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory'] = ['className' => 'CIC\Cicbase\Persistence\QueryFactory'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\QueryFactoryInterface'] = ['className' => 'CIC\Cicbase\Persistence\QueryFactory'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator'] = ['className' => 'CIC\Cicbase\Validation\Validator\EmailAddressValidator'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3Fluid\Fluid\Core\Parser\TemplateParser'] = ['className' => 'CIC\Cicbase\View\TemplateParser'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Validation\ValidatorResolver'] = ['className' => 'CIC\Cicbase\Validation\ValidatorResolver'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyDelivererInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyDeliverer'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyDenierInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyDenier'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['CIC\Cicbase\Proxy\File\Contracts\FileProxyGatewayInterface'] = ['className' => 'CIC\Cicbase\Proxy\File\FileProxyGateway'];


# TODO: UPGRADE: disabling this for now, causing error, seemingly because of the [[filename]]:[[classname]] formatting
#$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] =
#	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Hooks/DatabaseRecordList.php:CIC\Cicbase\Hooks\DatabaseRecordList';
