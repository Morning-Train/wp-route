# Morningtrain\WP\Route

A Route Service for WordPress that uses the WordPress rewrite engine and adds Laravel syntax to it.

## Setting up a Route
Routes are defined in the `/routes` directory of your project.

All files in this directory are loaded by the framework while it is setting up.

Routes MUST call a Controller as callback!

```php
// /routes/myroute.php
use \Morningtrain\WP\Route\Route;

// Set up a route on the /myroute URL and call MyrouteController::myRoute as callback
Route::get('/myroute','MyrouteController::myRoute');
// Set up a route that only accepts POST requests and has a dynamic ID
Route::post('/myotherroute/{id}','MyrouteController::myOtherRoute');

// /Controllers/MyrouteController.php
class MyrouteController extends \Morningtrain\WP\Core\Abstracts\AbstractController{
    public static function myRoute(){
        // Validate request & send http status code
        // Fetch data
        // Render view
    }
    public static function myOtherRoute($id){
        // Validate request & send http status code
        // Fetch data
        // Render view
    }
}
```