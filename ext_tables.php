<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CICBase Static Typoscript');

if (TYPO3_MODE == 'BE') {
	// Older versions of ExtBase don't have CLI CommandManager
	if (t3lib_extMgm::isLoaded('scheduler') && class_exists('Tx_Extbase_MVC_CLI_CommandManager')) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_Cicbase_Scheduler_Task'] = array(
			'extension'        => $_EXTKEY,
			'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.name',
			'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.description',
			'additionalFields' => 'Tx_Cicbase_Scheduler_FieldProvider'
		);
	}
}

t3lib_extMgm::addLLrefForTCAdescr('tx_cicbase_domain_model_file', 'EXT:cicbase/Resources/Private/Language/locallang_csh_tx_cicbase_domain_model_file.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_cicbase_domain_model_file');
$TCA['tx_cicbase_domain_model_file'] = array(
	'ctrl' => array(
		'title'	=> 'User File Uploads',
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
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/File.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_cicbase_domain_model_file.gif'
	),
);


?>