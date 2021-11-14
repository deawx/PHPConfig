<?php
/**
 * Classes.php
 *
 * This file is part of PHPConfig.
 *
 * @package    Classes.php @ 2021-11-09T11:47:02.809Z
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2021 PHPConfig
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt  GNU GPL 3.0
 * @version    1.1
 * @link       https://www.muhammetsafak.com.tr
 */

namespace PHPConfig;

/**
 * Class created to be able to use class properties as a configuration.
 * 
 * Extend your configuration class from this class.
 * `extends \PHPConfig\Classes`
 * Then create an object with your own class and start using it.
 */
class Classes
{

    /**
     * @var array $phpconfig_configs An array that holds the properties and values of the class.
     */
    private array $phpconfig_configs = [];

    /**
     * @var string $phpconfig_delimiter The shred character to use when shredding keys.
     */
    protected string $phpconfig_delimiter = '.';

    public function __construct()
    {
        $this->phpconfig();
    }

    /**
     * Loads the properties of the class as configuration.
     * 
     * If you are using your own custom constructor (`__construct`) method, remember to call this method. Otherwise it will not work correctly.
     * 
     * @return void
     */
    protected final function phpconfig(): void
    {
        $config = get_class_vars(get_class($this));
        
        unset($config['phpconfig_configs']);
        unset($config['phpconfig_delimiter']);

        $this->phpconfig_configs = $config;
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
        if(strpos($key, $this->phpconfig_delimiter)){
            $keys = explode($this->phpconfig_delimiter, $key);
            $id = $keys[0];
            array_shift($keys);
            $key = implode($this->phpconfig_delimiter, $keys);
            if(!isset($this->phpconfig_configs[$id])){
                $this->phpconfig_configs[$id] = [];
            }
            $this->phpconfig_configs[$id] = $this->multiSubConfigSet($key, $value, $this->phpconfig_configs[$id]);
        }else{
            $this->phpconfig_configs[$key] = $value;
        }

        return $this;
    }

    /**
     * Returns the value of the specified configuration. If the configuration exists, $default_value is returned.
     * 
     * @param string $key The key to the desired configuration. If `NULL` it returns the entire configuration array.
     * @param mixed $default_value The data to return if the desired configuration is not found.
     * @return mixed The value of the configuration or `$default_value`
     */
    public function get(?string $key = null, $default_value = false)
    {
        if(is_null($key)){
            return $this->phpconfig_configs;
        }
        $keys = explode($this->phpconfig_delimiter, $key);
        if(isset($this->phpconfig_configs[$keys[0]])){
            $config = $this->phpconfig_configs[$keys[0]];
            array_shift($keys);
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
     * Replaces the value of the correct configuration with the key separated by `$delimiter`.
     * 
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @return array
     */
    private function multiSubConfigSet($key, $value, $config)
    {
        if(strpos($key, $this->phpconfig_delimiter)){
            $keys = explode($this->phpconfig_delimiter, $key);
            $id = $keys[0];
            array_shift($keys);
            
            if(!isset($config[$id])){
                $config[$id] = [];
            }
            $config[$id] = $this->multiSubConfigSet(implode($this->phpconfig_delimiter, $keys), $value, $config[$id]);
        }else{
            $config[$key] = $value;
        }
        
        return $config;
    }

}
