<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File_Rotator_Period
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Abstract.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once "ZendEx/File/Rotator/Abstract.php";
require_once "Zend/Date.php";

/**
 * @category ZendEx
 * @package  ZendEx_File_Rotator_Period
 */
abstract class ZendEx_File_Rotator_Period_Abstract extends ZendEx_File_Rotator_Abstract {
    /*
     * Modification date of file
     *
     * @var Zend_Date
     */
    protected $_mdate = null;

    /**
     * Sets rotator options
     *
     * @param  string            $url
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($url, $config) {
        parent::__construct($url, $config);

        if (file_exists($this->getUrl())) {
            $this->_mdate = new Zend_Date(filemtime($this->getUrl()));
        }
    }

    /**
     * Get rotated files
     *
     * @return array
     */
    protected function _getRotatedFiles() {
        return array_reverse(parent::_getRotatedFiles()); // Sort descending by date
    }
}
