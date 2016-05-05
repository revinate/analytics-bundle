<?php

namespace Revinate\AnalyticsBundle\Lib;

class DateHelper {
    /** Timestamp Granularities */
    const TS_GRANULARITY_MINUTE = 60;
    const TS_GRANULARITY_HOUR = 3600;
    const TS_GRANULARITY_DAY = 86400;

    const SECONDS_IN_MINUTE = 60;
    const SECONDS_IN_HOUR = 3600;
    const SECONDS_IN_DAY = 86400;
    /** Revinate HQ Timezone */
    const TIMEZONE_REVINATE = 'America/Los_Angeles';
    const DEFAULT_TIMEZONE = 'America/Los_Angeles';

    /** Scale types */
    const SCALE_DAY     = 'day';
    const SCALE_WEEK    = 'week';
    const SCALE_MONTH   = 'month';
    const SCALE_QUARTER = 'quarter';
    const SCALE_YEAR    = 'year';

    /**
     * Rounds the timestamp to closest period based on the specified granularity
     *
     * @param int    $date              date
     * @param int    $granularity       granularity in seconds; default self::TS_GRANULARITY_HOUR
     * @param string $returnDateFormat  Date format of returned date (compatible with strtotime)
     *
     * @return int
     */
    public static function roundDate($date, $granularity = self::TS_GRANULARITY_HOUR, $returnDateFormat = 'c') {
        if (is_null($date) || $date == 0) { return null; }
        $tz = DateHelper::changeTimezone('UTC');
        $timestamp = strtotime($date);
        $newTimestamp = floor($timestamp / $granularity) * $granularity;
        $newDate = date($returnDateFormat, $newTimestamp);
        DateHelper::changeTimezone($tz);
        return $newDate;
    }

    /**
     * Change to given timezone
     *
     * @param string $timezone    new timezone to set
     * @return string Returns old timezone
     */
    public static function changeTimezone($timezone = 'UTC') {
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        return $oldTz;
    }

    /**
     * Returns timestamp in UTC (or whatever other timezone is provided)
     *
     * @static
     * @param   string  $date   date in string format
     * @param   string $timeZone the timezone, defaults to utc
     * @param   string $now the reference timestamp to base the calculation off, defaults to null (i.e. now)
     * @return int
     */
    public static function getTimestamp($date, $timeZone = 'UTC', $now = null) {
        $origTz = date_default_timezone_get();
        date_default_timezone_set($timeZone);
        $ts = is_null($now) ? strtotime($date) : strtotime($date, $now);
        date_default_timezone_set($origTz);
        return $ts;
    }

    /**
     * Returns mid-night timestamp in UTC (or whatever other timezone is provided)
     *
     * @static
     * @param   string $date   date in string format
     * @param   string $timeZone tz
     * @return int
     */
    public static function getMidNightTimestamp($date, $timeZone = 'UTC') {
        $origTz = date_default_timezone_get();
        date_default_timezone_set($timeZone);
        $ts = strtotime(date('Y-m-d', strtotime($date)));
        date_default_timezone_set($origTz);
        return $ts;
    }

    /**
     * Returns UTC Date string given local date string and time
     * @param string $dateFormat
     * @param string $localDate
     * @param string $localTimezone
     * @return string
     */
    public static function getUTCDateFromLocalDate($dateFormat, $localDate, $localTimezone) {
        $origTz = self::changeTimezone($localTimezone);
        $localDateWithTz = date('c', strtotime($localDate));
        date_default_timezone_set('UTC');
        $utcDate = date($dateFormat, strtotime($localDateWithTz));
        self::changeTimezone($origTz);
        return $utcDate;
    }

    /**
     * Returns Date in UTC (or whatever other timezone is provided)
     * todo - rename :)
     *
     * @static
     * @param   string  $dateFormat     Format of the date as supported by date()
     * @param   int     $timestamp      Timestamp
     * @param   string  $timeZone       Timezone
     *
     * @return string                   Formatted date/time string
     */
    public static function getDate($dateFormat, $timestamp = null, $timeZone = 'UTC') {
        $origTz = date_default_timezone_get();
        date_default_timezone_set($timeZone);
        $timestamp = is_null($timestamp) ? time() : $timestamp;
        $date = date($dateFormat, $timestamp);
        date_default_timezone_set($origTz);
        return $date;
    }

    //todo - rename
    public static function getDateObject($time, $dateFormat = 'Y-m-d H:i:s', $timeZone = 'UTC') {
        if (is_null($time)) {
            return null;
        }
        $origTz = date_default_timezone_get();
        date_default_timezone_set($timeZone);
        $date = \DateTime::createFromFormat($dateFormat, $time);
        date_default_timezone_set($origTz);
        return $date;
    }

    /**
     * Returns the appropriate start and end dates in yyyy-mm-dd format with the
     * given day and scale.
     *
     * @throws \Exception  if an unsupported scale string is provided
     *
     * @param $day string  day in yyyy-mm-dd format
     * @param $scale string  scale label, e.g. 'day', 'week', 'month', etc
     *
     * @return array  period start and end dates in yyyy-mm-dd format
     */
    public static function getScaleRange($day, $scale) {
        if ($scale == 'day') {
            return array($day, $day);
        } elseif ($scale == 'week') {
            // week beginning sunday
            $timestamp = strtotime($day);
            $time = localtime($timestamp, true);
            $dayOfWeek = intval($time['tm_wday']);
            $timestamp = $dayOfWeek > 0 ? strtotime('-' . $dayOfWeek . ' day' . ($dayOfWeek > 1 ? 's' : ''), $timestamp) : $timestamp;
            return array(date('Y-m-d', $timestamp), date('Y-m-d', strtotime('+7 days', $timestamp) - 1));
        } elseif ($scale == 'month') {
            $timestamp = strtotime($day);
            return array(date('Y-m-01', $timestamp), date('Y-m-d', strtotime(date('Y-m-01', DateHelper::addMonthOffset(1, $timestamp))) - (24 * 60 * 60)));
        } elseif ($scale == 'quarter') {
            // calendar quarter
            $timestamp = strtotime($day);
            $time = localtime($timestamp, true);
            $start_day = date('Y-m-01', DateHelper::addMonthOffset(-1 * (intval($time['tm_mon']) % 3), $timestamp));
            $end_day = date('Y-m-d', DateHelper::addMonthOffset(3, strtotime($start_day)) - (24 * 60 * 60));
            return array($start_day, $end_day);
        } elseif ($scale == 'year') {
            $timestamp = strtotime($day);
            return array(date('Y-01-01', $timestamp), date('Y-12-31', $timestamp));
        } elseif (preg_match('/^last_(\d+)_(\w+)$/', $scale, $matches)) {
            // rolling last n period group
            $n = intval($matches[1]);
            $period_scale = $matches[2];
            list ($period_start, $period_end) = self::getScaleRange($day, $period_scale);
            if ($period_scale == 'quarter') {
                $n *= 3;
                $period_scale = 'month';
            }
            $timestamp = strtotime($period_start);
            $start_day = date('Y-m-d', strtotime('-' . $n . ' ' . $period_scale, $timestamp));
            $end_day = date('Y-m-d', $timestamp - (24 * 60 * 60));
            return array($start_day, $end_day);
        } else {
            throw new \Exception("Unsupported scale [$scale]");
        }
    }

    /**
     * Offset the provided reference time by the provided offset.  All dates will become relative to the first of the reference month
     *
     * @static
     * @param integer $offset  number of months to offset (e.g., -1 or 3)
     * @param integer|null $refTime  the reference time to offset from; null is current time
     *
     * @return integer  adjusted timestamp
     */
    public static function addMonthOffset($offset, $refTime = null) {
        if (is_null($refTime)) { $refTime = time(); }
        $refTime = strtotime(date('Y-m-01', $refTime));
        return strtotime($offset . ' month' . (abs($offset) == 1 ? '' : 's'), $refTime);
    }

    /**
     * Returns period info for the provided period name
     * @throws \Exception  if unsupported period name is provided
     *
     * @param string $periodName  name of period, such as 'mtd', 'lm', 'ytd'
     * @param int|null $time  reference timestamp; null to use current time
     * @param string $timezone
     *
     * @return array  key-value pairs, 'period' => array(start_day, scale), 'description' => string
     */
    public static function getPeriodInfo($periodName, $time = null, $timezone = 'UTC') {
        $tz = self::changeTimezone($timezone);
        if (is_null($time)) { $time = time(); }
        $periodName = strtolower($periodName);
        if ($periodName == 'lw') {
            $range = self::getScaleRange(date('Y-m-d', strtotime('-7 days', $time)), 'week');
            $period = array($range[0], 'week', $range[1]);
            $return = array('period' => $period, 'description' => 'Last Week: ' . date('n/j/y', strtotime($range[0])) . '-' . date('n/j/y', strtotime($range[1])), 'short_description' => 'Last Week');
        } elseif ($periodName == 'mtd') {
            $period = array(date('Y-m-01', $time), 'month', date('Y-m-d'));
            $return = array('period' => $period, 'description' => 'Month To Date: ' . date('n/j/y', strtotime($period[0])) . '-' . date('n/j/y'), 'short_description' => 'Month to Date');
        } elseif ($periodName == 'lm') {
            $startDay = date('Y-m-01', strtotime('-1 month', strtotime(date('Y-m-01', $time))));
            $range = self::getScaleRange($startDay, 'month');
            $period = array($range[0], 'month', $range[1]);
            $return = array('period' => $period, 'description' => 'Last Month: ' . date('F y', strtotime($period[0])), 'short_description' => 'Last Month');
        } elseif (preg_match("/^l(\d+)m$/", $periodName, $matches)) {
            $range = self::getScaleRange(date('Y-m-d', $time), 'last_' . $matches[1] . '_month');
            $period = array($range[0], 'last_' . $matches[1] . '_month', $range[1]);
            $startTime = strtotime($range[0]);
            $endTime = strtotime($range[1]);
            $return = array('period' => $period, 'description' => 'Last ' . $matches[1] . ' Months: ' . date('M', $startTime) . ' - ' . date('M', $endTime), 'short_description' => 'Last ' . $matches[1] . ' Months');
        } elseif (preg_match("/^l(\d+)mtd$/", $periodName, $matches)) {
            $range = self::getScaleRange(date('Y-m-d', $time), 'last_' . $matches[1] . '_month');
            $period = array($range[0], 'last_' . $matches[1] . '_month_to_date', date('Y-m-d', $time));
            $startTime = strtotime($range[0]);
            $return = array('period' => $period, 'description' => 'Last ' . $matches[1] . ' Months to date: ' . date('M', $startTime) . ' - ' . date('Y-m-d', $time), 'short_description' => 'Last ' . $matches[1] . ' Months to Date');
        } elseif ($periodName == 'ytd') {
            $period = array(date('Y-01-01', $time), 'year', date('Y-m-d'));
            $return = array('period' => $period, 'description' => 'Year To Date: ' . date('Y'), 'short_description' => 'Year to Date');
        } elseif ($periodName == 'ly') {
            $yearAgoTime = strtotime('-1 year', $time);
            $period = array(date('Y-01-01', $yearAgoTime), 'year', date('Y-12-31', $yearAgoTime));
            $return = array('period' => $period, 'description' => 'Last Year: ' . date('Y', $yearAgoTime), 'short_description' => 'Last Year');
        } elseif ($periodName == 'wtd') {
            $period = array(date('Y-m-d', strtotime('last sunday', $time)), 'week', date('Y-m-d'));
            $return = array('period' => $period, 'description' => 'Week To Date: ' . date('n/j/y', strtotime($period[0])) . '-' . date('n/j/y'), 'short_description' => 'Week to Date');
        } elseif (strpos($periodName, 'da') !== false) {
            $howLong = (int) str_replace('da', '', $periodName);
            $daysAgo = $howLong;
            $range = self::getScaleRange(date('Y-m-d', strtotime('-'.$daysAgo.' days', $time)), 'day');
            $period = array($range[0], 'day', $range[1]);
            $shortDesc = $howLong == 0 ? 'Today' : ($howLong == 1 ? 'Yesterday' : $howLong .' Days Ago');
            $return = array('period' => $period, 'description' => $howLong.' Days Ago: ' . date('n/j/y', strtotime($range[0])) . '-' . date('n/j/y', strtotime($range[1])), 'short_description' => $shortDesc);
        } elseif (strpos($periodName, 'wa') !== FALSE) {
            $howLong = (int) str_replace('wa', '', $periodName);
            $daysAgo = $howLong*7;
            $range = self::getScaleRange(date('Y-m-d', strtotime('-'.$daysAgo.' days', $time)), 'week');
            $period = array($range[0], 'week', $range[1]);
            $return = array('period' => $period, 'description' => $howLong.' Weeks Ago: ' . date('n/j/y', strtotime($range[0])) . '-' . date('n/j/y', strtotime($range[1])), 'short_description' => $howLong.' Weeks Ago');
        } elseif (strpos($periodName, 'ma') !== FALSE) {
            $howLong = (int) str_replace('ma', '', $periodName);
            $startDay = date('Y-m-01', strtotime('-'.$howLong.' month', strtotime(date('Y-m-01', $time))));
            $range = self::getScaleRange($startDay, 'month');
            $period = array($range[0], 'month', $range[1]);
            $return = array('period' => $period, 'description' => $howLong.' Months Ago: ' . date('F y', strtotime($period[0])), 'short_description' => $howLong.' Months Ago');
        } elseif (strpos($periodName, 'lq') !== FALSE) {
            $startTime = strtotime('-3 month', $time);
            $startDay = date('Y-m-d', $startTime);
            $range = self::getScaleRange($startDay, 'quarter');
            $period = array($range[0], 'quarter', $range[1]);
            $return = array('period' => $period, 'description' => 'Last Quarter: ' . self::getQuarterDate($startTime), 'short_description' => 'Last Quarter');
        } elseif (strpos($periodName, 'qa') !== FALSE) {
            $howLong = (int)str_replace('qa', '', $periodName);
            $monthsAgo = $howLong*3; //months ago
            $startTime = strtotime('-' . $monthsAgo . ' month', $time);
            $startDay = date('Y-m-d', $startTime);
            $range = self::getScaleRange($startDay, 'quarter');
            $period = array($range[0], 'quarter', $range[1]);
            $return = array('period' => $period, 'description' => $howLong.' Quarter' . ($howLong == 1 ? '' : 's' ).' Ago: '. self::getQuarterDate($startTime), 'short_description' => $howLong . ' Quarters Ago');
        } elseif (strpos($periodName, 'ya') !== FALSE) {
            $howLong = (int)str_replace('ya', '', $periodName);
            $yearAgoTime = strtotime('-' . $howLong . ' year', $time);
            $period = array(date('Y-01-01', $yearAgoTime), 'year', date('Y-12-31', $yearAgoTime));
            $return = array('period' => $period, 'description' => $howLong . ' Years Ago: ' . date('Y', $yearAgoTime), 'short_description' => $howLong . ' Years Ago');
        } else if (strpos($periodName, 'lmp') !== FALSE) {
            $dayOfMonth = date('d', $time);
            $startDate = date('Y-m-d', self::addMonthOffset(-1, $time));
            $daysInMonth = date('t', strtotime($startDate));
            $dayOfMonth = intval($dayOfMonth) > intval($daysInMonth) ? $daysInMonth : $dayOfMonth;
            $period = array($startDate, 'month', date('Y-m-', strtotime($startDate)) . $dayOfMonth);
            return array('period' => $period, 'description' => 'Last Month Pace: ' . date('m/d/y', strtotime($period[0])) . ' - ' . date('m/d/y', strtotime($period[2])), 'short_description' => "Last Month Pace");
        } elseif (strpos($periodName, 'qtd') !== FALSE) {
            $startDay = date('Y-m-d', $time);
            $range = self::getScaleRange($startDay, 'quarter');
            $period = array($range[0], 'quarter',  date('Y-m-d'));
            $return = array('period' => $period, 'description' => 'Quarter To Date: ' . date('n/j/y', strtotime($period[0])) . '-' . date('n/j/y'), 'short_description' => 'Quarter To Date');
        } elseif (strpos($periodName, '-') !== false) {
            $pieces = explode("-", $periodName);
            $period[0] = ($pieces[0]);
            $period[1] = ($pieces[1]);
            $return = array('period' => $period, "description" => "Custom Date Range " . date('m/d/y', strtotime($period[0]))
                . " - " . date('m/d/y', strtotime($period[1])), 'short_description' => 'Custom Date Range');
        } elseif (preg_match('/l([\d]+)d/', $periodName, $matches)) {
            $howLong = $matches[1] - 1;
            $days = $matches[1];
            $period = array(date('Y-m-d', strtotime('-'.$howLong.' days', $time)), 'day', date('Y-m-d', $time));
            $return = array('period' => $period, 'description' => "Last {$days} days: " . date('n/j/y', strtotime($period[0])) . '-' . date('n/j/y', strtotime($period[2])), 'short_description' => "Last {$days} days: ");
        } else {
            throw new \Exception('Unsupported period name: [' . $periodName . ']');
        }
        self::changeTimezone($tz);
        return $return;
    }

    /**
     * Get the date as a quarter string (i.e. Q2 2011) in UTC
     *
     * @param int   $time timestamp
     * @param int   $relativeQuarter    the +/- delta of quarters from this quarter, defaults to 0
     * @return string   the date as a quarter
     */
    public static function getQuarterDate($time, $relativeQuarter = 0) {
        if ($relativeQuarter != 0) {
            $time = self::getTimestamp(self::getDateAtStartOfQuarterRelativeToNow($relativeQuarter,$time));
        }
        $origTz = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $ret = 'Q'.self::getQuarter($time).' '.date('\'y', $time);
        date_default_timezone_set($origTz);
        return $ret;
    }

    /**
     * Returns the time at the start of the quarter relative to now in UTC
     *
     * @param int   $relativeQuarter    the +/- delta of quarters from this quarter
     * @param int   $now    the timestamp to get the offset from, defaults to null (i.e. current time)
     * @return  string  the date of the beginning of the quarter
     */
    public static function getDateAtStartOfQuarterRelativeToNow($relativeQuarter, $now = null) {
        $origTz = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $monthsFromNow = $relativeQuarter*3;
        $time = strtotime("$monthsFromNow months", $now);
        $quarter = self::getQuarter($time);
        $ret = date('M 1, Y', mktime(0, 0, 0, ($quarter-1)*3 + 1, 1, date('Y', $time)));
        date_default_timezone_set($origTz);
        return $ret;
    }

    /**
     * Returns the quarter in the year for the timestamp in UTC
     *
     * @param int   $time the timestamp
     * @return int  the quarter from 1-4
     */
    public static function getQuarter($time){
        $origTz = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $n = date('n', $time);
        date_default_timezone_set($origTz);
        if($n < 4){
            return 1;
        } elseif($n > 3 && $n <7){
            return 2;
        } elseif($n >6 && $n < 10){
            return 3;
        } elseif($n >9){
            return 4;
        }
    }

    /**
     * Return the number of days between two timestamp
     *
     * @static
     * @param integer $ts1
     * @param integer $ts2
     * @return integer
     */
    public static function getNumberOfDaysBetweenTwoTimestamp($start, $end) {
        return intval(($end - $start) / self::SECONDS_IN_DAY);
    }

    public static function getNumberOfDaysBetweenTwoDates(\DateTime $start, \DateTime $end) {
        $dDiff = $start->diff($end);
        return (int)($dDiff->format('%R').$dDiff->days);
    }

    /**
     * Conver a date time to utc timestamp
     *
     * @static
     * @param mixed $dateTime
     * @param string $originTimezone
     * @return null
     */
    public static function convertDateTimeToUTCTimestamp($dateTime, $originTimezone = null) {
        $defaultTz = date_default_timezone_get();
        date_default_timezone_set($originTimezone);
        $dateTimeObj = new \DateTime($dateTime);
        $ts = $dateTimeObj->getTimestamp();
        date_default_timezone_set($defaultTz);
        return $ts;
    }
    /**
     * @param int $timestamp
     * @return \DateTime
     */
    public static function convertTimestampToDateTime($timestamp)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);
        return $dateTime;
    }

    /**
     * @param $dateString
     * @return \DateTime|null
     */
    public static function convertDateToDateTime($dateString) {
        return $dateString ? new \DateTime($dateString) : null;
    }

    /**
     * Convert given date period to property tz date strings
     * @param string $timezone   Property Timezone
     * @param array  $period     From and To Period
     * @return array of from and to period
     */
    public static function getPeriodWithTimezone($timezone, $period) {
        if (!$timezone || empty($period)) {
            return $period;
        }
        // Convert to Property's tz
        $oldTz = DateHelper::changeTimezone($timezone);
        // From Date
        if ($period[0]) {
            $period[0] = date('c', strtotime(date('Y-m-d 00:00:00', strtotime($period[0]))));
        }
        // To Date
        if ($period[1]) {
            $period[1] = date('c', strtotime(date('Y-m-d 24:59:59', strtotime($period[1]))));
        }
        DateHelper::changeTimezone($oldTz);
        return $period;
    }

    /**
     * Get increment string for strtotime for a given scale string
     *
     * @param   string  $scale
     * @return  string
     */
    public static function getIncrementStringFromScale($scale) {
        switch ($scale) {
            case self::SCALE_DAY:
            case self::SCALE_WEEK:
            case self::SCALE_MONTH:
            case self::SCALE_YEAR:
                return '+1 '.$scale;
            case self::SCALE_QUARTER:
                return '+3 months';
            default:
                return '';
        }
    }


    /**
     * Generates an array of timestamps for a given period array of format $period = array($startDate, $endDate).
     * For monthly scale, sets period start to beginning of month. For quarterly scale, to the beginning of the quarter.
     *
     * @param   array   $period
     * @param   string  $scale
     * @return  array
     */
    public static function getIntervalTimestampsForES($period, $scale) {
        // Adjust start time to match up with proper ES date buckets
        if ($scale === self::SCALE_QUARTER) {
            $scaleAdjustedStartTime = DateHelper::getDateAtStartOfQuarterRelativeToNow(0, strtotime($period[0]));
        } else if ($scale === self::SCALE_MONTH){
            $scaleAdjustedStartTime = date('1-m-Y', strtotime($period[0]));
        } else if ($scale === self::SCALE_WEEK) {
            $scaleAdjustedStartTime = date('d-m-Y', strtotime('last monday', strtotime($period[0])));
        }else {
            $scaleAdjustedStartTime = $period[0];
        }

        $time = strtotime($scaleAdjustedStartTime);
        $endTime = strtotime($period[1]);
        $incrementString = self::getIncrementStringFromScale($scale);

        $timestamps = array();
        while ($time <= $endTime) {
            $timestamps[] = $time;
            $time = strtotime($incrementString, $time);
        }
        return $timestamps;
    }
}