<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File_Rotator
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Size.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once "Zend/Measure/Binary.php";
require_once "Zend/Filter.php";
require_once "ZendEx/File/Rotator/Abstract.php";

/**
 * @category ZendEx
 * @package  ZendEx_File_Rotator
 */
class ZendEx_File_Rotator_Size extends ZendEx_File_Rotator_Abstract {
    /*
     * Binary size measure of file
     *
     * @var Zend_Measure_Binary
     */
    protected $_size = null;

    /*
     * Binary size measure of limit
     *
     * @var Zend_Measure_Binary|string|integer
     */
    protected $_limit = "10 MB";

    /**
     * Cycle length of rotations. Zero means unlimited cycle length
     *
     * @var string
     */
    protected $_cycle = 10;

    /**
     * Sets rotator options
     *
     * Accepts the following option keys:
     *   'size'   => int, Size of file
     *
     * @param  string            $url
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($url, $config) {
        parent::__construct($url, $config);
        $config = array_merge(array("limit" => $this->_limit), parent::_parseConfig($config));

        if (file_exists($this->getUrl())) {
            $this->_size  = new Zend_Measure_Binary(filesize($this->getUrl()));
        }

        $this->setLimit($config["limit"]);
    }

    /**
     * Sets size measure of limit
     *
     * @param  Zend_Measure_Binary|string|integer $limit
     * @return Zend_File_Rotator_Size
     */
    protected function setLimit($limit) {
        if ($limit instanceof Zend_Measure_Binary) {
            $this->_limit = $limit;
        } else {
            $limit = Zend_Filter::filterStatic($limit, "Measure", array("Binary"), "ZendEx_Filter");
            if (is_numeric($limit) && $limit > 0) {
                $this->_limit = new Zend_Measure_Binary($limit);
            } else {
                require_once 'Zend/Exception.php';
                throw new Zend_Exception("Invalid limit value \"$limit\".");
            }
        }
        return $this;
    }

    /**
     * Returns size measure of limit
     *
     * @return integer
     */
    protected function getLimit() {
        if ($this->_limit instanceof Zend_Measure_Binary) {
            return $this->_limit;
        } else {
            return $this->setLimit($this->_limit)->getLimit();
        }
    }

    /**
     * Cycle rotated files
     *
     * @return Zend_File_Rotator
     */
    protected function _cycle() {
        $formatter = $this->_getFormatter(array("suffix"    => "(" . $this->_getSuffixPattern() . ")"));
        $pattern = (string)$formatter;

        $files = $this->_getRotatedFiles();
        for ($i = count($files) - 1; $i >= 0; $i--) {
            preg_match("/$pattern/", basename($files[$i]), $matches);
            $formatter->suffix = $matches[1] + 1;
            rename($files[$i], $this->_getDirname() . DIRECTORY_SEPARATOR . $formatter);
        }

        return $this;
    }

    /**
     * Returns suffix
     *
     * @return string
     */
    protected function _getSuffix() {
        return "1";
    }

    /**
     * Returns suffix pattern
     *
     * @return string
     */
    protected function _getSuffixPattern() {
        return "[0-9]+";
    }

    /**
     * Checks necessity of rotation
     *
     * @return boolean
     */
    protected function _isNeedRotation() {
        if (!($this->_size instanceof Zend_Measure_Binary)) {
            return false;
        }
        return $this->_size->compare($this->_limit) >= 0;
    }

    /**
     * Create a new instance of Zend_Log_Rotator_Abstract
     *
     * @param  array|Zend_Config $config
     * @return ZendEx_File_Rotator_Period_Yearly
     */
    static public function factory($config) {
        $config = array_merge(array("url" => null), self::_parseConfig($config));

        $url = $config["url"];
        unset($config["url"]);

        return new self($url, $config);
    }
}
