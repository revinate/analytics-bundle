<?php

namespace Revinate\AnalyticsBundle\Service;

use AMNL\Google\Chart\Table;
use AMNL\Google\Chart\Row;
use AMNL\Google\Chart\Column;
use AMNL\Google\Chart\Type\String;
use AMNL\Google\Chart\Type\Number;
use AMNL\Google\Chart\Type\Date;
use AMNL\Google\Chart\Type\DateTime;
use AMNL\Google\Chart\Type\Boolean;
use AMNL\Google\Chart\Type\TimeOfDay;

/**
 * Class GoogleDataTableService
 * @package Revinate\SharedBundle\Service
 *
 * This is a service that is wrapped around the AMNL/gc-datatable package.
 * The aim of this service is to make it easy to generate google datatable without taking a deep dive into its
 * workings.
 *
 * Usage:
 * - Create an instance of the service
 *   $googleDataTableService = $this->container->get('revinate.googleDataTable');
 *
 * - Add columns
 *   $googleDataTableService->addColumn('Date', GoogleDataTableService::TYPE_STRING, 'date_id');
 *   $googleDataTableService->addColumn('Clicks', GoogleDataTableService::TYPE_NUMBER, 'click_id');
 *   $googleDataTableService->addColumn('Opens', GoogleDataTableService::TYPE_NUMBER, 'open_id');
 *   $googleDataTableService->addColumn('Sent', GoogleDataTableService::TYPE_NUMBER, 'send_id');
 *   $type has to be from the list $types below or it won't get added
 *
 * - Add rows
 *   $googleDataTableService->addRow(array('2014-11-05', 5, 8, 10));
 *   $googleDataTableService->addRow(array('2014-11-04', 3, 6, 10));
 *   $googleDataTableService->addRow(array('2014-11-03', 7, 9, 10));
 *   $googleDataTableService->addRow(array('2014-11-02', 1, 4, 10));
 *   $googleDataTableService->addRow(array('2014-11-01', 2, 6, 10));
 *
 *   rows can also be added in bulk using the $googleDataTableService->addRows($rows) function where $rows is an
 *   array of arrays listed above
 *
 * - Generate Data Table
 *   $googleDataTableService->getDataTableArray()
 *   OR
 *   $googleDataTableService->getDataTableJson()
 *   OR
 *   $googleDataTableService->getDataTableObject()
 *
 *
 * IMPORTANT NOTES:
 *   MAKE SURE NUMBER OF DATA POINTS IN ROW MATCH THE NUMBER OF COLUMN.
 *   MAKE SURE THE COLUMNS AND DATA POINTS IN ROWS ARE ORDERED THE SAME WAY.
 */

class GoogleDataTableService {

    /**
     * types of columns supported
     */
    const TYPE_BOOLEAN = 'Boolean';
    const TYPE_DATE = 'Date';
    const TYPE_DATETIME = 'DateTime';
    const TYPE_NUMBER = 'Number';
    const TYPE_STRING = 'String';
    const TYPE_TIMEOFDAY = 'TimeOfDay';

    /**
     * @var Table $dataTable
     */
    protected $dataTable;

    /**
     * @var $rows
     */
    protected $rows;

    /**
     * @var $columns
     */
    protected $columns;

    /**
     * supported columns types
     *
     * @var array
     */
    protected $types = array(
        self::TYPE_BOOLEAN,
        self::TYPE_DATE,
        self::TYPE_DATETIME,
        self::TYPE_NUMBER,
        self::TYPE_STRING,
        self::TYPE_TIMEOFDAY
    );

    /**
     */
    public function __construct() {
        $this->rows = array();
        $this->columns = array();
    }

    /**
     * row should contain an array of items equal to the number of columns
     *
     * @param Array|mixed $row
     */
    public function addRow($row) {
        if (!empty($row)) {
            $this->rows[] = new Row($row);
        }
    }

    /**
     * Add a column to the data table.
     * Make sure when you add a column you do add subsequent data in the rows
     *
     * @param String $label
     * @param String $type
     * @param null $id
     */
    public function addColumn($label, $type, $id = null) {
        if (in_array($type, $this->types)) {
            $this->columns[] = new \AMNL\Google\Chart\Column($this->_getTypeObj($type), $label, $id);
        }
    }

    /**
     * instantiate the type and return object
     *
     * @param $type
     * @return String|Number|Date|DateTime|TimeOfDay|Boolean
     */
    private function _getTypeObj($type) {
        $className = "AMNL\\Google\\Chart\\Type\\".$type;
        return new $className();
    }

    /**
     * Add many rows which should be in the form of array of arrays
     *
     * @param Array $rows
     */
    public function addRows(Array $rows) {
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $this->rows[] = new Row($row);
            }
        }
    }

    /**
     * Get the generated datatable in json
     *
     * @return string
     */
    public function getDataTableJson($flushData = true) {
        $this->_setTable();
        $table =  $this->dataTable->toJson();
        if ($flushData) {
            $this->_flushInternalData();
        }
        return $table;
    }

    /**
     * Get the generated datatable as an object
     *
     * @return object
     */
    public function getDataTableObject($flushData = true) {
        $this->_setTable();
        $table = $this->dataTable->toObject();
        if ($flushData) {
            $this->_flushInternalData();
        }
        return $table;
    }

    /**
     * get the generated datatable as an array
     *
     * @return Array
     */
    public function getDataTableArray($flushData = true) {
        $this->_setTable();
        $table = json_decode(json_encode($this->dataTable->toObject()), true);
        if ($flushData) {
            $this->_flushInternalData();
        }
        return $table;
    }


    /**
     * Internal function to create the datatable
     */
    private function _setTable() {
        $this->dataTable = new Table($this->columns);
        if (!empty($this->rows)) {
            foreach ($this->rows as $row) {
                $this->dataTable->addRow($row);
            }
        }
    }

    /**
     * Clears all previously set rows and columns incase we want to render a new datatable
     */
    private function _flushInternalData() {
        $this->rows = array();
        $this->columns = array();
        $this->dataTable = null;
    }
}