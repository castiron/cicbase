<?php

defined ('TYPO3_MODE') or die ('Access denied.');

return array(
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
		'iconfile' => 'EXT:cicbase/Resources/Public/Icons/tx_cicbase_domain_model_emailtemplate.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'template_key, is_draft, subject, body'
	),
	'types' => array(
		'1' => array('showitem' => 'template_key, is_draft, subject, body'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'is_draft' => array(
			'exclude' => 1,
			'label' => 'Draft Version',
			'config' => array(
				'type' => 'check',
			),
		),
		'template_key' => array(
			'exclude' => 0,
			'label' => 'Corresponding Template Key',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'Subject',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'body' => array(
			'exclude' => 0,
			'label' => 'Template Body',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',

			),
		),
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
	),
);
