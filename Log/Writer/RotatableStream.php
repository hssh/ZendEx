<?php
/**
 * Zend Framework Extension rotatable stream log writer
 *
 * @category  ZendEx
 * @package   ZendEx_Log_Writer
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: RotatableStream.php 112 2011-08-21 12:07:20Z hssh $
 */

require_once "Zend/Log/Writer/Stream.php";
require_once "ZendEx/File/Rotator.php";

/**
 * @category ZendEx
 * @package  ZendEx_Log_Writer
 */
class ZendEx_Log_Writer_RotatableStream extends Zend_Log_Writer_Stream {
    /**
     * Class Constructor
     *
     * @param array|string|resource $streamOrUrl Stream or URL to open as a stream
     * @param string|null $mode Mode, only applicable if a URL is given
     * @param string|null $rotation Rotation type
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($streamOrUrl, $mode = null, $rotatorConfig = null) {
        // Setting the default
        if (null === $mode) {
            $mode = 'a';
        }

        if (is_resource($streamOrUrl)) {
            if (get_resource_type($streamOrUrl) != 'stream') {
                require_once 'Zend/Log/Exception.php';
                throw new Zend_Log_Exception('Resource is not a stream');
            }

            if ($mode != 'a') {
                require_once 'Zend/Log/Exception.php';
                throw new Zend_Log_Exception('Mode cannot be changed on existing streams');
            }

            $this->_stream = $streamOrUrl;
        } else {
            if (is_array($streamOrUrl) && isset($streamOrUrl['stream'])) {
                $streamOrUrl = $streamOrUrl['stream'];
            }

            // Rotation
            if (is_string($streamOrUrl) && $rotatorConfig != null) {
                $rotatorConfig["rotatorParams"]["url"] = $streamOrUrl;
                ZendEx_File_Rotator::factory($rotatorConfig)->rotate();
            }

            if (! $this->_stream = @fopen($streamOrUrl, $mode, false)) {
                require_once 'Zend/Log/Exception.php';
                $msg = "\"$streamOrUrl\" cannot be opened with mode \"$mode\"";
                throw new Zend_Log_Exception($msg);
            }
        }

        $this->_formatter = new Zend_Log_Formatter_Simple();
    }

    /**
     * Create a new instance of ZendEx_Log_Writer_RotatableStream
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_RotatableStream
     */
    static public function factory($config) {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'stream'        => null,
            'mode'          => null,
            'rotatorName'   => null,
            'rotatorParams' => null
        ), $config);

        $streamOrUrl = isset($config['url']) ? $config['url'] : $config['stream'];
        $rotatorConfig = array("rotatorName"   => $config["rotatorName"],
                               "rotatorParams" => $config["rotatorParams"]);

        return new self(
            $streamOrUrl,
            $config['mode'],
            $rotatorConfig
        );
    }
}
