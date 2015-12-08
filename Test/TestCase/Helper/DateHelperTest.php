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
    }
}