# WP Route

A Route Service for WordPress that uses the WordPress rewrite engine and adds Laravel syntax to it.

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
    - [Installation](#installation)
- [Dependencies](#dependencies)
    - [morningtrain/php-loader](#morningtrainphp-loader)
- [Usage](#usage)
    - [Adding a route](#adding-a-route)
    - [A route with arguments](#a-route-with-arguments)
    - [Using named routes](#using-named-routes)
- [Credits](#credits)
- [Testing](#testing)
- [License](#license)
-

## Introduction

A Route is an address or endpoint in the application. Routes are defined in the `/routes` directory of your project,
unless otherwise specified.

All files in this directory are loaded by the framework while it is booting up.

Note that the Route API imitates [Laravel Route](https://laravel.com/docs/routing)

Routes MUST call a Controller as callback!

## Getting Started

To get started install the package as described below in [Installation](#installation).

To use the tool have a look at [Usage](#usage)

### Installation

Install with composer

```bash
composer require morningtrain/wp-route
```

## Dependencies

### morningtrain/php-loader

[PHP Loader](https://github.com/Morning-Train/php-loader) is used to load and initialize all Hooks

## Usage

### Adding a route

```php
// /routes/myroute.php
use \Morningtrain\WP\Route\Route;

// Set up a route on the /myroute URL and call MyrouteController::myRoute as callback
Route::get('/myroute','MyrouteController::myRoute');

// /Controllers/MyrouteController.php
class MyrouteController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public static function myRoute(){
        // Validate request & send http status code
        // Fetch data
        // Render view
    }
}
```

### A route with arguments

```php
// /routes/kittens.php
use \Morningtrain\WP\Route\Route;

// Set up a route on the /kitten/1 URL and call KittenController::kittenById as callback
Route::get('/kitten/{kitten_id}','KittenController::kittenById');

// /Controllers/KittenController.php
class KittenController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public static function kittenById($kitten_id){
        // Validate request & send http status code
        // Fetch data
        // Render view
    }
}
```

### Using named routes

```php
// /routes/kittens.php
use \Morningtrain\WP\Route\Route;

// Set up a route on the /kitten/1 URL and call KittenController::kitten as callback
Route::get('/kitten/{kitten_id}','KittenController::kitten')->name('kitten');

// /Controllers/KittenController.php
class KittenController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public static function kitten($kitten_id){
        // Validate request & send http status code
        // Fetch data
        // Render view
    }
}

// In some template or hook you can now get and check for the named route
// Get Url for Kittens Route
$url = \Morningtrain\WP\Route\RouteService::getUrl('kittens',['kitten_id' => 1]); // Would return /kitten/1
// Check if currently on kitten route
$bool = \Morningtrain\WP\Route\RouteService::isCurrentRoute('kitten');
```


## Credits

- [Mathias Munk](https://github.com/mrmoeg)
- [All Contributors](../../contributors)

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
