<?php


namespace Morningtrain\WP\Route\Abstracts;

/**
 * Class AbstractController
 *
 * Create one method for each possible request that this controller should handle.
 * Make sure to validate the request and user and to set the correct http status code!
 * Refer to these callback methods in your Route or Hook!
 *
 * A Controller should, preferably, be CRUD-like.
 * For instance a controller for a given endpoint at maximum have the 4 CRUD methods.
 * If you need 2 different READS/get routes then you should consider having different controllers for these
 *
 * @package Morningtrain\WP\Route\Abstracts
 */
abstract class AbstractController
{

}