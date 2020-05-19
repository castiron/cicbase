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
