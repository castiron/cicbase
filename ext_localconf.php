<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

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