# Morningtrain\WP\Route

A Route Service for WordPress that uses the WordPress rewrite engine and adds Laravel syntax to it.


## Installation

```bash
composer require morningtrain/wp-route
```

## Route
A Route is an address or endpoint in the application. 
Routes are defined in the `/routes` directory of your project, unless otherwise specified.

All files in this directory are loaded by the framework while it is booting up.

Note that the Route API imitates [Laravel Route](https://laravel.com/docs/routing)

Routes MUST call a Controller as callback!

### A simple route

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

## A quick note on Controllers
Controllers should be placed in /Controllers.
A Controllers methods should follow CRUD.
This means that there should exist at most 4 methods in a Controller.

If you need multiple getters or setters in a single Controller then consider having more Controllers.
Just like a proper REST API endpoint can't have multiple different POST methods for the same endpoint.
Read more: [Laravel Resource Controllers](https://laravel.com/docs/8.x/controllers#resource-controllers)

In a controllers method you MUST validate the request, its data, the user and where applicable the referer.
