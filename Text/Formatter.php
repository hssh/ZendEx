<?php
/**
 * Zend Framework Extension text formatter
 *
 * @category  ZendEx
 * @package   ZendEx_Text
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Formatter.php 111 2011-08-21 12:02:33Z hssh $
 */

require_once "Zend/Filter.php";

/**
 * @category ZendEx
 * @package  ZendEx_Text
 */
class ZendEx_Text_Formatter {
    /*
     * Format
     *
     * @var string
     */
    protected $_format = null;

    /*
     * Replacements
     *
     * @var array
     */
    protected $_replacements = array();

    /*
     * Default filter
     *
     * @var array
     */
    protected $_defaultFilter = array();

    /*
     * Filters
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Returns formatted string
     *
     * @param  string $format
     * @param  array  $replacements
     * @return string
     */
    public static function format($format, array $replacements = array()) {
        $formatter = new ZendEx_Text_Formatter($format, $replacements);
        return (string)$formatter;
    }

    /**
     * Initializes formatter
     *
     * @param  string $format
     * @param  array  $replacements
     * @return void
     */
    public function __construct($format, array $replacements = array()) {
        if (empty($format)) {
            require_once "Zend/Text/Exception.php";
            throw new Zend_Exception("Format is empty.");
        }
        $this->_defaultFilter = new Zend_Filter();
        $this->setFormat($format)
             ->setReplacements($replacements);
    }

    /**
     * Sets $value to $_replacements[$name]
     *
     * @param  string $name
     * @param  array  $value
     * @return void
     */
    public function __set($name, $value) {
        $this->_replacements[$name] = $value;
    }

    /**
     * Returns $_replacements[$name]
     *
     * @param  string $name
     * @return boolean
     */
    public function __get($name) {
        return $this->_replacements[$name];
    }

    /**
     * Checks existence of $_replacements[$name]
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name) {
        isset($this->_replacements[$name]);
    }

    /**
     * Unsets $_replacements[$name]
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name) {
        unset($this->_replacements[$name]);
    }

    /**
     * Sets format
     *
     * @param  string $format
     * @return ZendEx_Text_Formatter
     */
    public function setFormat($format) {
        $this->_format = $format;
        return $this;
    }

    /**
     * Returns format
     *
     * @return string
     */
    public function getFormat() {
        return $this->_format;
    }

    /**
     * Sets format
     *
     * @param  string $format
     * @return ZendEx_Text_Formatter
     */
    public function setReplacements($replacements) {
        $this->_replacements = $replacements;
        return $this;
    }

    /**
     * Returns replacements
     *
     * @return array
     */
    public function getReplacements() {
        return $this->_replacements;
    }

    /**
     * Adds a filter to the chain
     *
     * @param  Zend_Filter_Interface $filter
     * @param  string $tagName
     * @param  string $placement
     * @return ZendEx_Text_Formatter
     */
    public function addFilter(Zend_Filter_Interface $filter, $tagName = null,
                              $placement = Zend_Filter::CHAIN_APPEND) {
        if (empty($tagName)) {
            $this->_defaultFilter->addFilter($filter, $placement);
        } else {
            if (!array_key_exists($tagName, $this->_filters)) {
                $this->_filters[$tagName] = new Zend_Filter();
            }
            $this->_filters[$tagName]->addFilter($filter, $placement);
        }
        return $this;
    }

    /**
     * Applies filters to replacements
     */
    protected function _filterReplacements() {
        foreach ($this->_replacements as $name => $replacement) {
            if (is_string($replacement)) {
                $replacement = $this->_defaultFilter->filter($replacement);
                if (array_key_exists($name, $this->_filters)) {
                    $replacement = $this->_filters[$name]->filter($replacement);
                }
                $this->_replacements[$name] = $replacement;
            }
        }
    }

    /**
     * Returns formatted string
     *
     * Replaces tags in format string by replacements. There are 2 types of tags.
     *
     *   Type 1: %tagname%
     *   Type 2: %foo{tagname}bar%
     *
     * See examples below:
     *
     *   Format:
     *     "%protocol%://%host%%:{port}%/%{directory}/%%basename%"
     *
     *   Replacements:
     *     array("protocol" => "http", "host" => "localhost", "directory" => "~user")
     *
     *   Result:
     *     "http://localhost/~user/"
     *
     * @return string
     */
    public function toString() {
        $string = $this->getFormat();
        $this->_filterReplacements();
        foreach ($this->getReplacements() as $name => $replacement) {
            if ($replacement === null || $replacement === "" || $replacement === false) {
                continue;
            } elseif ((is_object($replacement) && !method_exists($replacement, '__toString')) ||
                      is_array($replacement)) {
                $replacement = gettype($replacement);
            }
            $string = preg_replace("/%([^%{]*){" . $name . "}([^}%]*)%|%${name}%/",
                                   "\${1}${replacement}\${2}", $string);
        }
        return preg_replace("/%[^%]+%/", "", $string); // Clear unreplaced tags
    }

    /**
     * Returns formatted string
     *
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }
}
