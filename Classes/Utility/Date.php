<?php

namespace CIC\Cicbase\Utility;

/**
 *
 * This holds some useful date utility functions. Please add more!
 *
 * @package CIC\Utility
 */
class Date {

	public static $months = ['january','february','march','april','may','june','july','august','september','october','november','december'];

	/**
	 * @param string $monthString
	 * @param string $format
	 * @return int
	 */
	public static function monthStringToInteger($monthString, $format = 'n') {
		if (is_numeric($monthString)) return (int) $monthString;
		return (int) date_format(date_create("1 $monthString 2000"), $format);
	}

	/**
	 * @param integer $monthInteger
	 * @param string $format
	 * @return bool|string
	 */
	public static function monthIntegerToString($monthInteger, $format = 'F') {
		if (is_string($monthInteger) && in_array(strtolower($monthInteger), self::$months)) return $monthInteger;
		return date_format(date_create("1/$monthInteger/2000"), $format);
	}

	/**
	 * @param integer $year
	 * @param string|integer $month
	 * @return int
	 */
	public static function monthStartTimestamp($year, $month) {
		$month = self::monthStringToInteger($month);
		return strtotime("$month/1/$year 00:00");
	}

	/**
	 * @param integer $year
	 * @param string|integer $month
	 * @param null|\DateTimeZone $tz
	 * @return integer
	 */
	public static function monthEndTimestamp($year, $month, $tz = NULL) {
		$nextMonth = self::monthStringToInteger($month) + 1;
		if ($nextMonth > 12) {
			$nextMonth = 1;
			++$year;
		}
		$dt = new \DateTime("$nextMonth/1/$year 00:00", $tz);
		return (int) $dt->sub(new \DateInterval('PT1S'))->format('U');
	}

	/**
	 * @param \DateTime $start
	 * @param \DateTime $end
	 * @return bool
	 */
	public static function spansMultipleDays(\DateTime $start, \DateTime $end) {
		return $end->diff($start)->format('%a') > 0;
	}
}