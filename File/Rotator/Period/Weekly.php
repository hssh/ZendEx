<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File_Rotator_Period
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Weekly.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once "ZendEx/File/Rotator/Period/Abstract.php";
require_once "Zend/Date.php";

/**
 * @category ZendEx
 * @package  ZendEx_File_Rotator_Period
 */
class ZendEx_File_Rotator_Period_Weekly extends ZendEx_File_Rotator_Period_Abstract {
    /**
     * Sets rotator options
     *
     * @param  string            $url
     * @param  array|Zend_Config $options
     */
    public function __construct($url, $config) {
        parent::__construct($url, $config);

        // Seeks to center of the week (Thursday) for the cross-year week number comparison
        if ($this->_mdate instanceof Zend_Date) {
            $this->_mdate->set(4, Zend_Date::WEEKDAY_8601);
        }
    }

    /**
     * Returns suffix for rotation
     *
     * @return string
     */
    protected function _getSuffix() {
        return $this->_mdate instanceof Zend_Date ? $this->_mdate->toString("yyyyww") : null;
    }

    /**
     * Returns suffix pattern
     *
     * @return string
     */
    protected function _getSuffixPattern() {
        return "[0-9]{4}[0-5][0-9]";
    }

    /**
     * Checks necessity of rotation
     *
     * @return bool
     */
    protected function _isNeedRotation() {
        $now = Zend_Date::now()->set(4, Zend_Date::WEEKDAY_8601); // Seeks to center of this week
        return $this->_mdate instanceof Zend_Date &&
               ($now->toString("yyyyww") > $this->_mdate->toString("yyyyww"));
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
