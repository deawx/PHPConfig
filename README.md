# PHPConfig
Library for managing configuration files.

```
________ ______  __________        _________               _____________          
___  __ \___  / / /___  __ \       __  ____/______ _______ ___  __/___(_)_______ _
__  /_/ /__  /_/ / __  /_/ /       _  /     _  __ \__  __ \__  /_  __  / __  __ `/
_  ____/ _  __  /  _  ____/        / /___   / /_/ /_  / / /_  __/  _  /  _  /_/ / 
/_/      /_/ /_/   /_/             \____/   \____/ /_/ /_/ /_/     /_/   _\__, /  
                                                                         /____/   
```

## Installation

For installation with Composer:

```
composer require muhametsafak/phpconfig
```

Download files for manual installation. Include `/src/Classes.php`, `/src/Config.php` and `/src/PHPConfig.php` in the queue.

## Usage

You will find the library document in the `/docs/` directory. If you wish, you can access the online document [here](https://www.muhammetsafak.com.tr/docs/PHPConfig/).

### PHPConfig (Config)

Intermediate class to use the methods of the `\PHPConfig\Config` class as static. You can find the `\PHPConfig\Config` documents [here](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html)

```php
$config = new \PHPConfig\PHPConfig();
```

#### `setArray()`

Imports an array as a configuration. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_setArray)

**Example :**

```php
$data = [
    'base' => [
        'dir' => '/home/www/site',
        'url' => 'http://localhost',
    ],
    'site' => [
        'name' => 'Site Name',
        'title' => 'Site Title'
    ],
    'test' => [
        'theme' => [
            'style' => 'style.css',
            'script' => [
                'jquery.js', 'main.js'
            ]
        ]
    ]
];

$config->setArray('configname', $data);
```

***

#### `get()`

Returns the value of the specified configuration. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_get)

**Example :** _Based on the configuration above._

```php
echo $config->get('configName.base.dir');
// Output : "/home/www/site"

print_r($config->get('configName.base'));
// Output : Array('dir' => '/home/www/site', 'url' => 'http://localhost')

var_dump($config->get('not_found'))
// Output : bool(false)

var_dump($config->get('not_found', NULL));
// Output : NULL
```

***

#### `set()`

Sets the value of the specified configuration. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_set)


**Example :** _Based on the configuration above._

```php
echo $config->get('configName.site.name');
// Output : "Site Name"

$config->set('configName.site.name', 'New Site Name');

echo $config->get('configName.site.name');
// Output : "New Site Name"
```

***

#### `setClass()`

Imports the public properties of the specified class or object. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_setClass)

```php
class FirstConfig{
    
    public $url = 'http://google.com';

    protected $name = 'Name';
}

class SecondConfig{
    public $url = 'http://github.com';
}

$config->setClass('FirstConfig');

$config->setClass(new SecondConfig());

$config->get('FirstConfig.url');
// Return : "http://google.com"

$config->get('FirstConfig.name');
// Return : false

$config->get('SecondConfig.url');
// Return : "http://github.com"
```

***

#### `setFile()`

Imports the array returned by the specified php file. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_setFile)

`/public_html/Database.php` :

```php
return [
    'name' => 'test_db',
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
]
```

```php
$config->setFile("db_config", "/public_html/Database.php");

$config->get('db_config.name');
// Return : "test_db"
```

***

#### `setDir()`

Imports arrays returned by php files in the specified directory. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_setDir)

_Loads multiple configuration files in the same structure as shown in the `setFile()` method._

```
/public_html/
    /config/
        Database.php
        Base.php
```

```php
$config->setDir("myConfig", "/public_html/config/");

$config->get('myConfig.Database.host');

$config->get('myConfig.Base.url');
```

***

#### `errors()`

Returns error messages. [see](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Config.html#method_errors)

```php
$config->setFile("Not_Found_File.php");

$errors = $config->errors();
if(!empty($errors)){
    foreach($errors as $err){
        echo $err . PHP_EOL;
    }
}
```

### .env

#### `setENV()`

Introduces configurations in an .env file to the system.

```php
$config->setENV("/home/www/safak/"); # /home/www/safak/.env

// or

$config->setENV("/home/www/safak/.env");

```

#### `env()`

It is used to get ENV values. 

**Note :** You can also use the `$_ENV` global or the `getenv()` function instead.

Example `.env` file :

```
BASE_URL = "http://www.google.com.tr"
```

```php
$config = new \PHPConfig\PHPConfig();

$config->setENV(__DIR__);

echo $config->env("BASE_URL");
// Output : http://www.google.com.tr

echo $config->env("SITE_URL");
// false

echo $config->env("DB_HOST", "localhost");
// Output : localhost
```

***

## Classes

See [here](https://www.muhammetsafak.com.tr/docs/PHPConfig/classes/PHPConfig-Classes.html) for the documentation of the class.

```php
class CustomClass extends \PHPConfig\Classes
{
    public $url = 'http://localhost';

    protected $name = 'Project Name';

    private $site = 'My Site';
}

$config = new CustomClass();

$config->get('url');
// Return : "http://localhost"

$config->get('name');
// Return : "Project Name"

$config->get('site');
// Return : false
```

**Note :** If you have a constructor method, call the `phpconfig()` method.

In the example below you can see the use of the `set()` and `phpconfig()` methods.

```php
class CustomClass extends \PHPConfig\Classes
{
    public $url = 'http://localhost';

    protected $name = 'Project Name';

    public $arr = [
        'site' => [
            'name' => 'Site Name'
        ]
    ];

    function __constructor()
    {
        // code ...
        $this->phpconfig();
    }
}

$config = new CustomClass();

$config->get('arr.site.name');
// Return : "Site Name"

$config->set('arr.site.name', 'New Site Name');

$config->get('arr.site.name');
// Return : "New Site Name"
```

## Licence

Copyright &copy; 2021 [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) - This library is distributed under the [GNU GPL 3.0](http://www.gnu.org/licenses/gpl-3.0.txt) license.
