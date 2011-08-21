<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File_Rotator
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Abstract.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once "Zend/Config.php";
require_once "Zend/Filter.php";
require_once "ZendEx/File/Rotator/Interface.php";
require_once "ZendEx/Text/Formatter.php";

/**
 * @category ZendEx
 * @package  ZendEx_File_Rotator
 */
abstract class ZendEx_File_Rotator_Abstract implements ZendEx_File_Rotator_Interface {
    /**
     * Url of file
     *
     * @var string
     */
    protected $_url = null;

    /**
     * Filename format for rotation
     *
     * @see ZendEx_Text_Fromatter
     * @var string
     */
    protected $_format = "%filename%%.{extension}%%.{suffix}%";

    /**
     * Cycle length of rotations. Zero means unlimited cycle length
     *
     * @var string
     */
    protected $_cycle = 0;

    /**
     * Sets rotator options
     *
     * Accepts the following option keys:
     *   'format' => string, filename format for rotation
     *
     * @param  string            $url
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($url, $config) {
        if (empty($url)) {
            require_once "Zend/Exception.php";
            throw new Zend_Exception("Url is empty.");
        }
        $config = array_merge(array("format" => $this->_format, "cycle" => $this->_cycle),
                              self::_parseConfig($config));

        $this->setUrl($url)
             ->setFormat($config["format"])
             ->setCycle($config["cycle"]);
    }

    /**
     * Sets file url
     *
     * @param  string $url
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setUrl($url) {
        $this->_url = $url;
        return $this;
    }

    /**
     * Returns file url
     *
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Sets filename format for rotation
     *
     * @param  string $format
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setFormat($format) {
        $this->_format = $format;
        return $this;
    }

    /**
     * Returns filename format for rotation
     *
     * @return string
     */
    public function getFormat() {
        return $this->_format;
    }

    /**
     * Sets cycle of rotation
     *
     * @param  string $format
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setCycle($cycle) {
        $this->_cycle = $cycle;
        return $this;
    }

    /**
     * Returns cycle of rotation
     *
     * @return string
     */
    public function getCycle() {
        return $this->_cycle;
    }

    /**
     * Returns filename for rotation
     *
     * @return string
     */
    public function getRotatingFilename() {
        $formatter = $this->_getFormatter(array("suffix" => $this->_getSuffix()));
        return $this->_getDirname() . DIRECTORY_SEPARATOR . $formatter;
    }

    /**
     * Rotates file
     *
     * @return ZendEx_File_Rotator_Abstract
     */
    public function rotate() {
        if ($this->_isNeedRotation()) {
            $this->_cycle();
            rename($this->getUrl(), $this->getRotatingFilename());
            $this->_unlinkCycleExceededFiles();
        }
        return $this;
    }

    /**
     * Returns dirname of url
     *
     * @return string
     */
    protected function _getDirname() {
        return Zend_Filter::filterStatic($this->getUrl(), "Dir");
    }

    /**
     * Returns basename of url
     *
     * @return string
     */
    protected function _getBasename() {
        return Zend_Filter::filterStatic($this->getUrl(), "BaseName");
    }

    /**
     * Returns filename of url
     *
     * @return string
     */
    protected function _getFilename() {
        return pathinfo($this->_getBasename(), PATHINFO_FILENAME);
    }

    /**
     * Returns extension of url
     *
     * @return string
     */
    protected function _getExtension() {
        return pathinfo($this->_getBasename(), PATHINFO_EXTENSION);
    }

    /**
     * Returns initialized formatter
     *
     * @param  array $replacements Replacements for formatter tags
     * @return string
     */
    protected function _getFormatter($replacements = array()) {
        $replacements =
            array_merge(array("basename"  => $this->_getBasename(),
                              "filename"  => $this->_getFilename(),
                              "extension" => $this->_getExtension()),
                        $replacements);
        return new ZendEx_Text_Formatter($this->getFormat(), $replacements);
    }

    /**
     * Returns suffix
     *
     * @return string
     */
    abstract protected function _getSuffix();

    /**
     * Returns suffix pattern
     *
     * @return string
     */
    abstract protected function _getSuffixPattern();

    /**
     * Checks necessity of rotation
     *
     * @return bool
     */
    abstract protected function _isNeedRotation();

    /**
     * Cycle rotated files
     *
     * @return Zend_File_Rotator
     */
    protected function _cycle() {
        return $this;
    }

    /**
     * Unlink files which exceed rotation cycle
     *
     * @return Zend_File_Rotator
     */
    protected function _unlinkCycleExceededFiles() {
        // Unlimited rotation if $_cycle is zero
        if ($this->getCycle() == 0) {
            return $this;
        }

        // Unlink files which exceed rotation cycle
        $files = $this->_getRotatedFiles();
        for ($i = $this->getCycle() - 1; $i < count($files); $i++) {
            unlink($files[$i]);
        }

        return $this;
    }

    /**
     * Get rotated files
     *
     * @return array
     */
    protected function _getRotatedFiles() {
        // Get initialized formatter
        $pattern = $this->_getFormatter();

        // Glob files which match with any suffix pattern.
        $pattern->suffix = "*";
        $files = glob($this->_getDirname() . DIRECTORY_SEPARATOR . $pattern);

        // Exclude files which don't match with the rotator's suffix pattern
        $pattern->suffix = $this->_getSuffixPattern();
        foreach ($files as $key => $filename) {
            if (!preg_match("/$pattern/", basename($filename), $matches)) {
                unset($files[$key]);
            }
        }

        // Natural sort & Reindexing
        natsort($files);
        return array_values($files);
    }

    /**
     * Validate and optionally convert the config to array
     *
     * @param  array|Zend_Config $config Zend_Config or Array
     * @return array
     * @throws Zend_Log_Exception
     */
    static protected function _parseConfig($config) {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception(
                'Configuration must be an array or instance of Zend_Config'
            );
        }

        return $config;
    }
}
