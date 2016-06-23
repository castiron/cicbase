<?php namespace CIC\Cicbase\Utility;

/**
 * Class Typoscript
 * @package CIC\Cicbase\Utility
 */
class Typoscript {

    /**
     * @credit Adapted from the 'tscobj' extension by Jean-David Gadina (macmade@gadlab.net)
     *
     * @param string $path
     * @param array $setup a parsed typoscript template with
     * @return array [$objectType, $objectConfig]; e.g. what you would pass to cObjGetSingle()
     *
     */
    public static function getConfigForPath($path, $setup = array()) {
        // Get complete TS template
        $tmpl = $setup ?: $GLOBALS['TSFE']->tmpl->setup;

        // Get TS object hierarchy in template
        $tmplPath = explode('.', $path);

        // Final TS object storage
        $tsObj = $tmpl;

        $cType = '';

        // Process TS object hierarchy
        for ($i = 0; $i < count($tmplPath); $i++) {

            // Try to get content type
            $cType = $tsObj[$tmplPath[$i]];

            // Try to get TS object configuration array
            $tsObj = $tsObj[$tmplPath[$i] . '.'];

            // Check object
            if (!$cType && !$tsObj) {
                return array();
            }
        }

        return array($cType, $tsObj);
    }
}
