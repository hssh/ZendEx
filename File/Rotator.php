<?php
/**
 * Zend Framework Extension file rotator
 *
 * @category  ZendEx
 * @package   ZendEx_File
 * @copyright Copyright (c) 2011 hssh.
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Rotator.php 112 2011-08-21 12:07:20Z hssh $
 */

/**
 * @category ZendEx
 * @package  ZendEx_File
 */
abstract class ZendEx_File_Rotator {
    /**
     * Create a new ZendEx_File_Rotator object
     *
     * @param  string $url
     * @param  string $className
     * @return Zend_File_Rotator
     */
    public static function factory($config) {
        if (empty($config["rotatorName"])) {
            require_once "Zend/Exception.php";
            throw new Zend_Exception("Rotator name is empty.");
        }

        if (empty($config["rotatorNamespace"])) {
            $config["rotatorNamespace"] = "ZendEx_File_Rotator";
        }

        $className = $config["rotatorNamespace"] . "_" . $config["rotatorName"];

        require_once 'Zend/Loader.php';
        try {
            Zend_Loader::loadClass($className);
        } catch (Exception $e) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("\"$className\" not found");
        }

        return call_user_func(array($className, "factory"), $config["rotatorParams"]);
    }
}
