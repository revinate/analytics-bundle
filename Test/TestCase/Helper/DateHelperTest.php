<?php

namespace Revinate\AnalyticsBundle\Test\TestCase\Helper;
use Revinate\AnalyticsBundle\Lib\DateHelper;

class DateHelperTest extends \PHPUnit_Framework_TestCase {


    public function testGetPeriodInfo() {
        // Right now only testing last month pace
        $lmpTypicalExpectedResults = array(
            'period' => array('2015-10-01', 'month', '2015-10-16'),
            'description' => 'Last Month Pace: 10/01/15 - 10/16/15',
            'short_description' => 'Last Month Pace'
        );
        $lmpEdgeExpectedResults = array(
            'period' => array('2015-02-01', 'month', '2015-02-28'),
            'description' => 'Last Month Pace: 02/01/15 - 02/28/15',
            'short_description' => 'Last Month Pace'
        );

        $this->assertEquals($lmpTypicalExpectedResults, DateHelper::getPeriodInfo('lmp', strtotime('November 16 2015')));
        $this->assertEquals($lmpEdgeExpectedResults, DateHelper::getPeriodInfo('lmp', strtotime('March 31 2015')));

        $l3mExpectedResults = array(
            'period' => array('2015-09-01', 'last_3_month', '2015-11-30'),
            'description' => 'Last 3 Months: Sep - Nov',
            'short_description' => 'Last 3 Months'
        );
        $l6mExpectedResults = array(
            'period' => array('2015-06-01', 'last_6_month', '2015-11-30'),
            'description' => 'Last 6 Months: Jun - Nov',
            'short_description' => 'Last 6 Months'
        );

        $this->assertEquals($l3mExpectedResults, DateHelper::getPeriodInfo('l3m', strtotime('December 15 2015')));
        $this->assertEquals($l6mExpectedResults, DateHelper::getPeriodInfo('l6m', strtotime('December 15 2015')));

        $l3mtdExpectedResults = array(
            'period' => array('2015-09-01', 'last_3_month_to_date', '2015-12-15'),
            'description' => 'Last 3 Months to date: Sep - 2015-12-15',
            'short_description' => 'Last 3 Months to Date'
        );
        $l6mtdExpectedResults = array(
            'period' => array('2015-06-01', 'last_6_month_to_date', '2015-12-15'),
            'description' => 'Last 6 Months to date: Jun - 2015-12-15',
            'short_description' => 'Last 6 Months to Date'
        );

        $this->assertEquals($l3mtdExpectedResults, DateHelper::getPeriodInfo('l3mtd', strtotime('December 15 2015')));
        $this->assertEquals($l6mtdExpectedResults, DateHelper::getPeriodInfo('l6mtd', strtotime('December 15 2015')));
    }
}