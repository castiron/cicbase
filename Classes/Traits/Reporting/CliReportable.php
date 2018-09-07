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
        return static::isLegacyCli() || static::isNewCli();
    }

    /**
     * @return bool
     */
    protected static function isNewCli() {
        return defined('TYPO3_REQUESTTYPE_CLI') && defined('TYPO3_REQUESTTYPE') && (TYPO3_REQUESTTYPE === TYPO3_REQUESTTYPE_CLI || TYPO3_REQUESTTYPE === (TYPO3_REQUESTTYPE_CLI | TYPO3_REQUESTTYPE_BE)) ;
    }

    /**
     * @return bool
     */
    protected static function isLegacyCli() {
        return defined('TYPO3_cliMode');
    }

    /**
     * @param $msg
     * @param bool $inPlace
     */
    protected static function cliMsg($msg, $inPlace = false) {
        if (static::isCli()) {
            echo $msg . ($inPlace ? "\r" : "\n");
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
        return number_format(round(pow(1024, $base - floor($base)), 1), 1) . $suffix[$f_base];
    }

    /**
     * @return string
     */
    protected static function getMemoryUsage() {
        return static::convertToReadableSize(memory_get_usage());
    }

    protected static function reportMemoryUsage() {
        if (static::isCli()) {
            static::cliMsg('Memory usage: ' . static::getMemoryUsage(), true);
        }
    }

    /**
     * A simple CLI status bar
     * Credit: http://stackoverflow.com/questions/2124195/command-line-progress-bar-in-php#answer-27147177
     *
     * @param $done
     * @param $total
     */
    protected static function statusBarUpdate($done, $total) {
        /**
         * This is not relevant if we're not in a CLI context.
         */
        if (!static::isCli()) {
            return;
        }

        $perc = ceil(($done / $total) * 100);
        $filled = ceil($perc / 2);
        $left = 50 - $filled;
        $memoryUsage = static::getMemoryUsage();
        $write = sprintf("\033[0G\033[2K[%'={$filled}s>%-{$left}s] - $perc%% - $done/$total - $memoryUsage", "", "");
        fwrite(STDERR, $write);
    }
}
