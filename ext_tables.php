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
$GLOBALS['TCA']['tx_cicbase_domain_model_file'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/File.php',
		'iconfile' => 'EXT:cicbase/Resources/Public/Icons/tx_cicbase_domain_model_file.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_cicbase_domain_model_emailtemplate', 'EXT:cicbase/Resources/Private/Language/locallang_csh_tx_cicbase_domain_model_emailtemplate.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_cicbase_domain_model_emailtemplate');
$GLOBALS['TCA']['tx_cicbase_domain_model_emailtemplate'] = array(
	'ctrl' => array(
		'title'	=> 'Email Template',
		'label' => 'template_key',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'hideTable' => TRUE,
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/EmailTemplate.php',
		'iconfile' => 'EXT:cicbase/Resources/Public/Icons/tx_cicbase_domain_model_emailtemplate.gif'
	),
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] =
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Hooks/DatabaseRecordList.php:CIC\Cicbase\Hooks\DatabaseRecordList';


?>