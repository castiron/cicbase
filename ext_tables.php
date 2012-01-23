<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CIC Services Typoscript');

if (TYPO3_MODE == 'BE') {
	if (t3lib_extMgm::isLoaded('scheduler')) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_Cicbase_Scheduler_Task'] = array(
			'extension'        => $_EXTKEY,
			'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.name',
			'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.description',
			'additionalFields' => 'Tx_Cicbase_Scheduler_FieldProvider'
		);
	}
}

?>