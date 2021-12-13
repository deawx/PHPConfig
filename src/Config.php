<?php
/**
 * Config.php
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
 * A class to use and easy to manage configurations.
 */
class Config
{

    private string $version = '1.1.1';

    /**
     * @var array $config Array where configurations are kept
     */
    protected static array $config = [];

    /**
     * @var array $objectConfig Array that stores configurations in object type.
     */
    protected static array $objectConfig = [];

    /**
     * @var string[] $errors Array of errors
     */
    protected static array $errors = [];

    /**
     * @var string $delimiter The character to use to break up key values.
     */
    protected static string $delimiter = '.';

    /**
     * @var bool $strtolower Specifies whether to reduce the characters of the keys.
     *
     * If `false` nothing is changed. If `true` the keys are minified with `strtolower()`.
     */
    protected static bool $strtolower = false;

    /**
     * Magic method for accessing configs as objects.,
     *
     * @param $key
     */
    public function __get($key)
    {
        $return = false;
        if(isset(self::$objectConfig[$key])){
            $return = self::$objectConfig[$key];
        }
        return $return;
    }

    public function version(): string
    {
        return $this->version;
    }

    /**
     * Sets the shredder to use.
     *
     * @param string $delimiter
     * @return self
     */
    public function delimiter(string $delimiter = '.'): self
    {
        self::$delimiter = $delimiter;
        return $this;
    }

    /**
     * Sets whether to convert keys to lowercase.
     *
     * @param bool $tolower
     * @return self
     */
    public function tolower(bool $tolower = false): self
    {
        self::$strtolower = $tolower;
        return $this;
    }

    /**
     * Imports an array.
     *
     * @param string|NULL $name The parent array key of the configurations to load. If `null` is loaded directly. It is possible to install in a subdirectory by specifying a name.
     * @param array $configs The array to import.
     * @return void
     */
    public function setArray(?string $name = null, array $configs = []): void
    {
        if($name === null){
            self::$config = $configs;
            self::$objectConfig = [$this->convertObject($configs)];
        }else{
            if(self::$strtolower){
                $name = \strtolower($name);
            }
            if(\strpos($name, self::$delimiter)){
                $this->set($name, $configs);
            }else{
                self::$config[$name] = $configs;
                self::$objectConfig[$name] = $this->convertObject($configs);
            }
        }
    }

    /**
     * Imports arrays returned by php files in the specified directory.
     *
     * The files should be returned as an array. Filenames become the parent array key holding the configuration returned by the file.
     *
     * @param string|NULL $name The parent array key of the configurations to load.
     * @param string $path The full file path of the directory where the files are located.
     * @return bool Returns `true` if one or more files are imported, `false` otherwise.
     */
    public function setDir(?string $name = null, string $path = __DIR__): bool
    {
        if(\is_dir($path)){
            $files = \glob(\rtrim($path, '/') . '/*.php');
            if(\is_array($files)){
                $return = false;
                $config_name_prefix = '';
                if($name !== null){
                    $config_name_prefix = $name . self::$delimiter;
                }
                foreach($files as $file){
                    $basename = \basename($file, '.php');
                    $config_name = $config_name_prefix . $basename;
                    if(self::$strtolower){
                        $config_name = \strtolower($config_name);
                    }
                    if($this->setFile($config_name, $file)){
                        $return = true;
                    }
                }
                if(!$return){
                    $this->setError("No php file found in ".$path." or none of the files return an array.");
                }
                return $return;
            }
        }
        $this->setError("The ".$path." directory could not be found.");
        return false;
    }

    /**
     * Introduces configurations in an .env file to the system.
     *
     * @param string $path The path to the .env file or the directory hosting the .env file.
     * @return self
     */
    public function setENV(string $path): self
    {
        if(\is_dir($path)){
            $path = \rtrim($path, '/') . '/.env' ;
        }
        if(\is_file($path)){
            $this->envFileLoad($path);
        }else{
            $this->setError(".env file not found!");
        }

        return $this;
    }

    /**
     * Gets an env value.
     *
     * @param string $key The key to the desired env value.
     * @param $default_value The value to return if the value for the key is not found.
     * @return mixed If the env with the desired key is found, it returns its value. If not found, `$default_value` is returned.
     */
    public function env(string $key, $default_value = false)
    {
        return $_ENV[$key] ?? $default_value;
    }

    /**
     * It reads the .env file and adds the configurations.
     *
     * @see \PHPConfig\Config::setENV()
     * @param string $path
     * @return void
     */
    protected function envFileLoad(string $path): void
    {
        $lines = \file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (\strpos(\trim($line), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = \trim($key);
            $value = \trim(\trim($value), '"');

            if (!\array_key_exists($key, $_SERVER) && !\array_key_exists($key, $_ENV)) {
                \putenv(\sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Imports the array returned by the specified php file.
     *
     * @param string|NULL $name The key name of the parent directory to which the configuration array will be transferred. `NULL` is treated as a direct configuration array.
     * @param string $path The full file path of the file to be imported.
     * @return bool Returns `true` if the file is imported, `false` otherwise.
     */
    public function setFile(?string $name = null, string $path = __DIR__): bool
    {
        if(\is_file($path)){
            $config = $this->loadFile($path);
            if(\is_array($config)){
                $this->setArray($name, $config);
                return true;
            }
        }
        $this->setError("File ".$path." not found or this file does not return an array.");
        return false;
    }

    /**
     * Imports the public properties of the specified class or object.
     *
     * @param object|string $class The full name of the class or the object created with the class.
     * @return bool It returns `true`. Returns `false` if the class is not found.
     */
    public function setClass($class): bool
    {
        $class_name = $class;
        if(\is_object($class)){
            $class_name = \get_class($class);
        }else{
            if(!\class_exists($class_name)){
                $this->setError("Class ".$class." not found.");
                return false;
            }
        }

        $properties = \get_class_vars($class_name);

        $namespace_split = \explode("\\", $class_name);
        $config_name = \end($namespace_split);

        $this->setArray($config_name, $properties);

        return true;
    }

    /**
     * Sets the value of the specified configuration.
     *
     * @param string $key Configuration key.
     * @param mixed $value The new value of the configuration.
     * @return self
     */
    public function set(string $key, $value): self
    {
        if(self::$strtolower){
            $key = \strtolower($key);
        }
        if(\strpos($key, self::$delimiter)){
            $keys = \explode(self::$delimiter, $key);
            $id = $keys[0];
            \array_shift($keys);
            $key = \implode(self::$delimiter, $keys);
            if(!isset(self::$config[$id])){
                self::$config[$id] = [];
            }
            $set = $this->multiSubConfigSet($key, $value, self::$config[$id]);
            self::$config[$id] = $set;
            self::$objectConfig[$id] = $this->convertObject($set);
        }else{
            self::$config[$key] = $value;
            if(\is_array($value)){
                self::$objectConfig[$key] = $this->convertObject($value);
            }else{
                self::$objectConfig[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns the value of the specified configuration. If the configuration exists, $default_value is returned.
     *
     * @param string|null $key The key to the desired configuration. If `NULL` it returns the entire configuration array.
     * @param mixed $default_value The data to return if the desired configuration is not found.
     * @return mixed The value of the configuration or `$default_value`
     */
    public function get(?string $key = null, $default_value = false)
    {
        if($key === null){
            return self::$config;
        }
        if(self::$strtolower){
            $key = \strtolower($key);
        }
        $keys = \explode(self::$delimiter, $key);
        if(isset(self::$config[$keys[0]])){
            $config = self::$config[$keys[0]];
            \array_shift($keys);
            foreach($keys as $key){
                if(isset($config[$key])){
                    $config = $config[$key];
                }else{
                    $config = $default_value;
                    break;
                }
            }
            return $config;
        }

        return $default_value;
    }

    /**
     * Returns error messages.
     *
     * @return string[] Returns the array `$errors`.
     */
    public function errors(): array
    {
        return self::$errors;
    }

    /**
     * Appends the error message to the `$errors` array.
     *
     * @param string $err
     * @return void
     */
    protected function setError(string $err): void
    {
        self::$errors[] = $err;
    }

    /**
     * Converts an array to object and returns
     *
     * @param array $configs
     * @return object
     */
    protected function convertObject(array $configs)
    {
        $object = new \stdClass();
        foreach ($configs as $key => $value) {
            if (\is_array($value)) {
                $value = $this->convertObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    /**
     * Replaces the value of the correct configuration with the key separated by `$delimiter`.
     *
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @return array
     */
    protected function multiSubConfigSet($key, $value, $config): array
    {
        if(\strpos($key, self::$delimiter)){
            $keys = \explode(self::$delimiter, $key);
            $id = $keys[0];
            \array_shift($keys);

            if(!isset($config[$id])){
                $config[$id] = [];
            }
            $config[$id] = $this->multiSubConfigSet(\implode(self::$delimiter, $keys), $value, $config[$id]);
        }else{
            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * Include the specified file.
     *
     * @param string $path The full path of the file to include.
     * @return mixed Returns the file if the file exists, returns an empty array if the file is not found.
     */
    protected function loadFile(string $path)
    {
        if(\is_file($path)){
            return require $path;
        }
        $this->setError("The '.$path.' file could not be found.");
        return [];
    }

}
