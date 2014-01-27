<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_cicbase_domain_model_file'] = array(
	'ctrl' => $TCA['tx_cicbase_domain_model_file']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, description, filename, original_filename, path, awsbucket, mime_type, size, root_directory, awslink'
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title, owner, path, mime_type, filename, original_filename, description, awsbucket, awslink, size, root_directory --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_cicbase_domain_model_file',
				'foreign_table_where' => 'AND tx_cicbase_domain_model_file.pid=###CURRENT_PID### AND tx_cicbase_domain_model_file.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'filename' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file.filename',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'original_filename' => array(
			'exclude' => 0,
			'label' => 'Original Filename',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'path' => array(
			'exclude' => 0,
			'label' => 'Path',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'awsbucket' => array(
			'exclude' => 0,
			'label' => 'AWS Bucket Name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'wizards' => array(
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=800,width=500,status=0,menubar=0,scrollbars=1'
					),
				),
			),
		),
		# NOTE awslink is not a database column!
		'awslink' => array(
			'exclude' => 0,
			'label' => 'AWS Link',
			'config' => array(
				'readOnly' => 1,
				'type' => 'user',
				'size' => '30',
				'userFunc' => 'EXT:cicbase/Classes/Factory/FileFactory.php:Tx_Cicbase_Factory_FileFactory->generateLink',
			),
		),
		'mime_type' => array(
			'exclude' => 0,
			'label' => 'MIME Type',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'size' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file.size',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'trim,required'
			),
		),
		'root_directory' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file.rootDirectory',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:cicbase/Resources/Private/Language/locallang_db.xml:tx_cicbase_domain_model_file.description',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',

			),
		),
		'owner' => array(
			'exclude' => 0,
			'label' => 'Creator',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
			),
		),
	),
);



?>
