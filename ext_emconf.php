<?php

########################################################################
# Extension Manager/Repository config file for ext "cicservices".
#
# Auto generated 16-02-2011 17:47
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CIC Extbase Services',
	'description' => 'CIC Extbase Services',
	'category' => 'plugin',
	'author' => 'Zachary Davis, Cast Iron Coding Inc',
	'author_email' => 'zach@castironcoding.com',
	'shy' => '',
	'dependencies' => 'extbase',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'extbase' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"0c67";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"b04a";s:14:"ext_tables.php";s:4:"5698";s:14:"ext_tables.sql";s:4:"3af2";s:25:"ext_tables_static+adt.sql";s:4:"7ec6";s:32:"Classes/Domain/Model/Address.php";s:4:"5c54";s:37:"Classes/Domain/Model/DigitalAsset.php";s:4:"79df";s:31:"Classes/Domain/Model/LatLng.php";s:4:"d4ef";s:30:"Classes/Domain/Model/State.php";s:4:"d207";s:28:"Classes/Domain/Model/Zip.php";s:4:"257f";s:52:"Classes/Domain/Repository/DigitalAssetRepository.php";s:4:"274f";s:45:"Classes/Domain/Repository/StateRepository.php";s:4:"03eb";s:43:"Classes/Domain/Repository/ZipRepository.php";s:4:"a41f";s:38:"Classes/Service/GeolocationService.php";s:4:"7ba7";s:34:"Configuration/TypoScript/setup.txt";s:4:"8348";s:19:"doc/wizard_form.dat";s:4:"0ecf";s:20:"doc/wizard_form.html";s:4:"f515";}',
	'suggests' => array(
	),
);

?>