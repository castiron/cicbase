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
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }
}
