<?php

namespace Revinate\AnalyticsBundle\Test\TestCase\Helper;
use Revinate\AnalyticsBundle\Lib\DateHelper;

class DateHelperTest extends \PHPUnit_Framework_TestCase {


    public function testGetPeriodInfo() {
        // Last Month Pace
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

        // Last 3 Months
        $l3mExpectedResults = array(
            'period' => array('2015-09-01', 'last_3_month', '2015-11-30'),
            'description' => 'Last 3 Months: Sep - Nov',
            'short_description' => 'Last 3 Months'
        );

        // Last 6 Months
        $l6mExpectedResults = array(
            'period' => array('2015-06-01', 'last_6_month', '2015-11-30'),
            'description' => 'Last 6 Months: Jun - Nov',
            'short_description' => 'Last 6 Months'
        );

        $this->assertEquals($l3mExpectedResults, DateHelper::getPeriodInfo('l3m', strtotime('December 15 2015')));
        $this->assertEquals($l6mExpectedResults, DateHelper::getPeriodInfo('l6m', strtotime('December 15 2015')));

        // Last 3 Months to Date
        $l3mtdExpectedResults = array(
            'period' => array('2015-09-01', 'last_3_month_to_date', '2015-12-15'),
            'description' => 'Last 3 Months to date: Sep - 2015-12-15',
            'short_description' => 'Last 3 Months to Date'
        );

        // Last 6 Months to Date
        $l6mtdExpectedResults = array(
            'period' => array('2015-06-01', 'last_6_month_to_date', '2015-12-15'),
            'description' => 'Last 6 Months to date: Jun - 2015-12-15',
            'short_description' => 'Last 6 Months to Date'
        );

        $this->assertEquals($l3mtdExpectedResults, DateHelper::getPeriodInfo('l3mtd', strtotime('December 15 2015')));
        $this->assertEquals($l6mtdExpectedResults, DateHelper::getPeriodInfo('l6mtd', strtotime('December 15 2015')));

        // Week To Date Previous Period
        $wtdppExpectedResults = array(
            'period' => array('2015-12-06', 'week_previous_period', '2015-12-08'),
            'description' => 'Week To Date Previous Period: 12/6/15 - 12/8/15',
            'short_description' => 'Week to Date Previous Period'
        );

        $this->assertEquals($wtdppExpectedResults, DateHelper::getPeriodInfo('wtdpp', strtotime('December 15 2015')));

        // Last Week Previous Period
        $lwppExpectedResults = array(
            'period' => array('2015-11-29', 'last_week_previous_period', '2015-12-05'),
            'description' => 'Last Week Previous Period: 11/29/15 - 12/5/15',
            'short_description' => 'Last Week Previous Period'
        );

        $this->assertEquals($lwppExpectedResults, DateHelper::getPeriodInfo('lwpp', strtotime('December 15 2015')));

        // Month To Date Previous Period
        $mtdppExpectedResults = array(
            'period' => array('2015-11-01', 'month_previous_period', '2015-11-15'),
            'description' => 'Month To Date Previous Period: 11/1/15 - 11/15/15',
            'short_description' => 'Month To Date Previous Period'
        );

        $this->assertEquals($mtdppExpectedResults, DateHelper::getPeriodInfo('mtdpp', strtotime('December 15 2015')));

        // Month To Date Previous Year
        $mtdpyExpectedResults = array(
            'period' => array('2014-12-01', 'month_previous_year', '2014-12-15'),
            'description' => 'Month To Date Previous Year: 12/1/14 - 12/15/14',
            'short_description' => 'Month to Date Previous Year'
        );

        $this->assertEquals($mtdpyExpectedResults, DateHelper::getPeriodInfo('mtdpy', strtotime('December 15 2015')));

        //  Quarter To Date Previous Period
        $qtdppExpectedResults = array(
            'period' => array('2015-07-01', 'quarter_previous_period', '2015-09-15'),
            'description' => 'Quarter To Date Previous Period: 7/1/15 - 9/15/15',
            'short_description' => 'Quarter To Date Previous Period'
        );

        $this->assertEquals($qtdppExpectedResults, DateHelper::getPeriodInfo('qtdpp', strtotime('December 15 2015')));

        // Quarter To Date Previous Year
        $qtdpyExpectedResults = array(
            'period' => array('2014-10-01', 'quarter_previous_year', '2014-12-15'),
            'description' => 'Quarter To Date Previous Year: 10/1/14 - 12/15/14',
            'short_description' => 'Quarter To Date Previous Year'
        );

        $this->assertEquals($qtdpyExpectedResults, DateHelper::getPeriodInfo('qtdpy', strtotime('December 15 2015')));

        // Year to Date Previous Year
        $ytdpyExpectedResults = array(
            'period' => array('2014-01-01', 'year_previous_year', '2014-12-15'),
            'description' => 'Year To Date Previous Year: 2014',
            'short_description' => 'Year to Date Previous Year'
        );

        $this->assertEquals($ytdpyExpectedResults, DateHelper::getPeriodInfo('ytdpy', strtotime('December 15 2015')));

        // Custom Date Range
        $customExpectedResults = array(
            'period' => array('2014-01-01', 'custom', '2014-12-15'),
            'description' => 'Custom Date Range 01/01/14 - 12/15/14',
            'short_description' => 'Custom Date Range'
        );

        $this->assertEquals($customExpectedResults, DateHelper::getPeriodInfo('01/01/2014-12/15/2014'));

        $period = array('01/01/2014', '12/15/2014');
        $customExpectedResults = array('2014-01-01T00:00:00+00:00', '2014-12-31T23:59:59+00:00');
        $this->assertEquals($customExpectedResults, DateHelper::extractStartAndEndDates($period));
    }
}
