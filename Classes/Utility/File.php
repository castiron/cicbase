<?php
namespace CIC\Cicbase\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class File
 * @package CIC\Cicbase\Utility
 */
class File {
    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return bool
     */
    public static function isProcessableFile($file) {
        $exts = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
        return array_search($file->getExtension(), $exts) !== false;
    }
}
