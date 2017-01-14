<?php namespace CIC\Cicbase\Traits\Reporting;

/**
 * Class CliReportable
 * @package CIC\Cicbase\Traits\Reporting
 */
trait CliReportable {
    /**
     * @return bool
     */
    protected static function isCli() {
        return defined('TYPO3_cliMode');
    }

    /**
     * @param $msg
     */
    protected static function cliMsg($msg) {
        if (static::isCli()) {
            echo $msg . "\n";
        }
    }

    /**
     * @param $size
     * @return string
     */
    protected static function convertToReadableSize($size){
        $base = log($size) / log(1024);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    }

    /**
     * @return string
     */
    protected static function getMemoryUsage() {
        return static::convertToReadableSize(memory_get_usage());
    }

    protected static function reportMemoryUsage() {
        if (static::isCli()) {
            static::cliMsg('Memory usage: ' . static::getMemoryUsage());
        }
    }
}
