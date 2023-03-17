# WP Route

A Route Service for WordPress that uses the WordPress rewrite engine and adds Laravel syntax to it.

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
    - [Installation](#installation)
- [Dependencies](#dependencies)
    - [morningtrain/php-loader](#morningtrainphp-loader)
    - [illuminate/pipeline](#illuminatepipeline)
    - [Symfony HTTP Foundation](#symfony-http-foundation)
- [Usage](#usage)
    - [Setup](#setup)
    - [Adding a route](#adding-a-route)
    - [A route with arguments](#a-route-with-arguments)
    - [Using named routes](#using-named-routes)
    - [Grouping Routes](#grouping-routes)
    - [Accessing WP Query Vars](#accessing-wp-query-vars)
- [Middleware](#middleware)
    - [A quick example](#a-quick-example)
- [REST](#rest)
    - [Options](#options)
    - [Public](#public)
    - [Expose](#expose)
    - [Changing the namespace](#changing-the-namespace)
- [Credits](#credits)
- [Testing](#testing)
- [License](#license)

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

### illuminate/pipeline

[Illuminate Pipeline](https://packagist.org/packages/illuminate/pipeline)

### Symfony HTTP Foundation

[Symfony Http Foundation](https://symfony.com/doc/current/components/http_foundation.html)

## Usage

### Setup

Initialize the package with `Route::setup($path)` or, if the package is already initialized, load a directory of routes
with `Route::loadDir($path)`

```php
use Morningtrain\WP\Route\Route;

Route::setup(__DIR__ . '/routes');
```

### Adding a route

```php
// /routes/myroute.php
use \Morningtrain\WP\Facades\Route;

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
use \Morningtrain\WP\Facades\Route;

// Set up a route on the /kitten/1 URL and call KittenController::kittenById as callback
Route::get('/kitten/{kitten_id}','KittenController::kittenById');

// /Controllers/KittenController.php
class KittenController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public static function kittenById(\Symfony\Component\HttpFoundation\Request $request){
        // Validate request & send http status code
        // Fetch data
        $kitten_id = $request->query->get('kitten_id');
        // Render view
    }
}
```

### Using named routes

```php
// /routes/kittens.php
use \Morningtrain\WP\Facades\Route;

// Set up a route on the /kitten/1 URL and call KittenController::kitten as callback
Route::get('/kitten/{kitten_id}','KittenController::kitten')->name('kitten');

// /Controllers/KittenController.php
class KittenController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public static function kitten(\Symfony\Component\HttpFoundation\Request $request){
        // Validate request & send http status code
        // Fetch data
        $kitten_id = $request->query->get('kitten_id');
        // Render view
    }
}

// In some template or hook you can now get and check for the named route
// Get Url for Kittens Route
$url = Route::route('kittens',['kitten_id' => 1]); // Would return /kitten/1
// Check if currently on kitten route
$bool = Route::current()->getName() === 'kitten';
```

### Grouping Routes

You may group a set of routes to apply a shared prefix to all of them or to apply shared middleware.

#### With prefix

```php
use \Morningtrain\WP\Facades\Route;
Route::prefix('my-prefix')->group(function(){
    Route::get('foo',FooController::class); // url will be /my-prefix/foo
    Route::get('bar',BarController::class); // url will be /my-prefix/bar
})
```

#### With middleware

```php
use \Morningtrain\WP\Facades\Route;
// Users must now be logged in to view these two routes
Route::middleware('auth')->group(function(){
    Route::get('foo',FooController::class);
    Route::get('bar',BarController::class);
})
```

#### With both prefix and middleware

```php
use \Morningtrain\WP\Facades\Route;
Route::prefix('my-prefix')
->middleware('auth')
->group(function(){
    Route::get('foo',FooController::class);
    Route::get('bar',BarController::class);
})
```

### Accessing WP Query Vars

WordPress query vars are added to the Request class as query data. So you can access them like so:

```php
use Symfony\Component\HttpFoundation\Request;

// Controller
class FooController extends \Morningtrain\WP\Route\Abstracts\AbstractController{
    public function __invoke(Request $request){
        // Do something here
        $post = $request->query->get('post');
    }
}
// Middleware
function(Request $request, $next){
    if($request->query->has('post')){
        // Do something here
        $post = $request->query->get('post');
    }
    return $next($request);
}
```

### Middleware

Middleware are functions called for a route after it has been matched against a url, but before its callback is called.

Middleware are useful for validating a group of routes, validating a users permissions or hijacking a request.

Read more about them here: [Laravel Docs - Middleware](https://laravel.com/docs/middleware)

#### A quick example

Middleware are function that receive a request object and a closure that represents the next middleware in the pipeline.
It is important to always `return $next($request);` at the end of a valid middleware.

In the example below we create a middleware that stops the pipeline if the current user is not logged in and returns a
response with status 404. If the user is logged in the middleware pipeline continues and eventually lets the route call
its controller.

A middleware is allowed to either continue the pipeline, return a Response or throw an exception. Responses must
be `\Symfony\Component\HttpFoundation\Response` and will be sent automatically. Exceptions are caught and converted into
custom `\Morningtrain\WP\Route\Responses\WPErrorResponse` that are then displayed using `wp_die()`

```php
use Morningtrain\WP\Facades\Route;
use \Symfony\Component\HttpFoundation\Request;
Route::middleware([function(Request $request, $next){
    if(!\is_user_logged_in()){
        // Returns a Response object
        return \Morningtrain\WP\Route\Classes\Response::with404();    
    }
    // Continues the middleware pipeline
    return $next($request);
}])
->group(function(){
    Route::get('my-account',MyAccountController::class);
});
```

### Rest

You can also register rest endpoints using the same syntax! Behind the scenes all Rest routes are registered
using [register_rest_route](https://developer.wordpress.org/reference/functions/register_rest_route/)

```php
use Morningtrain\WP\Facades\Rest;

Rest::get('products', [ProductRestController::class, 'getAllProducts'])->public(); // Will register an endpoint like /wp-json/mtwp/v1/products that accepts GET requests

```

#### Options

You can use middleware, prefix, groups and names on Rest routes the same way as you can on rewrite routes!!

**Note:** Middleware is applied AFTER WordPress has identified the route and passed through all other verifications such
as `permission_callback`, but before the controller or supplied callback is called.

##### Public

To make an endpoint public chain `public()` on either the route or its group. This simply sets the `permission_callback`
to `__return_true`. This defaults to `__return_false`.

##### Expose

You can expose the endpoint URL to the DOM by chaining `expose()` onto the route or its group. This outputs the url to a
JavaScript object similar to the
way [localize_script](https://developer.wordpress.org/reference/functions/wp_localize_script/) would.

The exposed url can be accessed like so:

```js
const fooEndpointUrl = window.mtwpRestRoutes.foo; // /wp-json/mtwp/v1/foo
```

**Note:** to expose an endpoint it has to be named.

##### Changing the namespace

You can set the namespace the same way you would a prefix.

```php
use Morningtrain\WP\Facades\Rest;

Rest::namespace('foo/v1')->group(function(){
    Rest::get('foo',FooController::class); // /wp-json/foo/v1/foo
});
```

This can be combined with prefixes

```php
use Morningtrain\WP\Facades\Rest;

Rest::namespace('foo/v1')->prefix('bar')->group(function(){
    Rest::get('foo',FooController::class); // /wp-json/foo/bar/v1/foo
});

// -- or --

Rest::namespace('foo/v1')->group(function(){
    Rest::prefix('bar')->group(function(){
        Rest::get('foo',FooController::class); // /wp-json/foo/bar/v1/foo
        Rest::get('baz',FooController::class); // /wp-json/foo/bar/v1/baz
    });
});
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
