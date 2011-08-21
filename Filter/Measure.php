<?php
/**
 * Zend Framework Extension measure filter
 *
 * @category  ZendEx
 * @package   ZendEx_Filter
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Measure.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once 'Zend/Locale/Format.php';
require_once "Zend/Filter/Interface.php";

/**
 * @category ZendEx
 * @package  ZendEx_Filter
 */
class ZendEx_Filter_Measure implements Zend_Filter_Interface {
    /**
     * Whether comments are allowed
     *
     * @var Zend_Measure_Abstract
     */
    public $_measure = null;

    /**
     * Zend_Filter_Measure constructor
     *
     * @param  string      $measureName
     * @param  Zend_Locale $locale
     * @param  string      $nameSpace
     * @throws Zend_Filter_Exception
     */
    public function __construct($measureName, $locale = null, $nameSpace = "Zend_Measure") {
        if (empty($measureName)) {
            require_once "Zend/Filter/Exception.php";
            throw new Zend_Filter_Exception("Measure name is empty.");
        }

        $className = "${nameSpace}_${measureName}";
        require_once 'Zend/Loader.php';
        try {
            Zend_Loader::loadClass($className);
        } catch (Exception $e) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("\"$className\" not found");
        }

        $this->_measure = new $className(0, null, $locale);
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the numerical value, from measure string
     *
     * @param  string $value
     * @return integer|string
     */
    public function filter($value) {
        $locale = $this->_measure->getLocale();
        $matched = false;
        foreach ($this->_measure->getConversionList() as $key => $conversion) {
            if (preg_match("~^(.*)" . $conversion[1] . "$~", $value, $matches)) {
                try {
                    // $number = Zend_Locale_Format::getNumber(trim($matches[1]), array('locale' => $locale));
                    $this->_measure->setType($key)->setValue(trim($matches[1]));
                } catch (Exception $e) {
                    continue;
                }
                $matched = true;
            }
        }
        return $matched ? $this->_measure->getValue() : $value;
    }
}
