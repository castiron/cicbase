<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Cicbase_Command_ExampleCommandController';

// TODO: Caching config should be updated to 4.6.x methods.

// If cache is not already defined, define it
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']
   ['cacheConfigurations']['cicservices'])) {
   $TYPO3_CONF_VARS['SYS']['caching']
      ['cacheConfigurations']['cicservices'] = array(
      'backend' => 't3lib_cache_backend_DbBackend',
      'options' => array(
         'cacheTable' => 'tx_cicservices_cache',
         'tagsTable' => 'tx_cicservices_cache_tags',
      )
   );
}

?>