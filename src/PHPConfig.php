<?php
/**
 * PHPConfig.php
 *
 * This file is part of PHPConfig.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2021 PHPConfig
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL 3.0
 * @version    1.1.1
 * @link       https://www.muhammetsafak.com.tr
 */

namespace PHPConfig;

/**
 * Intermediate class to use the methods of the \PHPConfig\Config class as static.
 * 
 * @see \PHPConfig\Config
 * @method static \PHPConfig\Config delimiter(string $delimiter = '.')
 * @method static \PHPConfig\Config tolower(bool $tolower = false)
 * @method static void setArray(?string $name = null, array $configs = [])
 * @method static bool setDir(?string $name = null, string $path = __DIR__)
 * @method static \PHPConfig\Config setENV(string $path)
 * @method static mixed env(string $key, $default_value = false)
 * @method static bool setFile(?string $name = null, string $path = __DIR__)
 * @method static bool setClass(string|object $class): bool
 * @method static \PHPConfig\Config set(string $key, $value)
 * @method static mixed get(?string $key = null, $default_value = false)
 * @method static array errors()
 */
class PHPConfig 
{

    private ?\PHPConfig\Config $lib;

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
