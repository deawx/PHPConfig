<?php
/**
 * PHPConfig.php
 *
 * This file is part of PHPConfig.
 *
 * @package    PHPConfig.php @ 2021-11-09T12:01:42.854Z
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2021 PHPConfig
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL 3.0
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

namespace PHPConfig;

/**
 * Intermediate class to use the methods of the \PHPConfig\Config class as static.
 * 
 * @see \PHPConfig\Config
 */
class PHPConfig 
{

    private \PHPConfig\Config $lib;

    public function __construct()
    {
        $this->lib = new \PHPConfig\Config();
    }

    public function __call($name, $arguments)
    {
        return $this->lib->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new self())->$name(...$arguments);
    }

    public function __get($name)
    {
        return $this->lib->__get($name);
    }

   
}
