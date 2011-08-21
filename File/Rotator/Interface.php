<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File_Rotator
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Interface.php 112 2011-08-21 12:07:20Z hssh $
 */

/**
 * @category ZendEx
 * @package  ZendEx_File_Rotator
 */
interface ZendEx_File_Rotator_Interface {
    /**
     * Sets file url
     *
     * @param  string $url
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setUrl($url);

    /**
     * Returns file url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Sets filename format for rotation
     *
     * @param  string $format
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setFormat($format);

    /**
     * Returns filename format for rotation
     *
     * @return string
     */
    public function getFormat();

    /**
     * Sets cycle of rotation
     *
     * @param  string $format
     * @return ZendEx_File_Rotator_Abstract
     */
    public function setCycle($cycle);

    /**
     * Returns cycle of rotation
     *
     * @return string
     */
    public function getCycle();

    /**
     * Returns suffix for rotation
     *
     * @return string
     */
    public function getRotatingFilename();

    /**
     * Rotates file
     *
     * @return ZendEx_File_Rotator_Abstract
     */
    public function rotate();

    /**
     * Construct a ZendEx_File_Rotator_Abstract object
     *
     * @param  array|Zend_Config $config
     * @return ZendEx_File_Rotator_Abstract
     */
    static public function factory($config);
}
