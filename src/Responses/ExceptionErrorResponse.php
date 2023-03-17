<?php

namespace Morningtrain\WP\Route\Responses;

use Symfony\Component\HttpFoundation\Response;

/**
 * A Symfony Response that represents an Exception in a WordPress die box
 */
class ExceptionErrorResponse extends WPErrorResponse
{
    public function __construct(\Exception $exception, int $status, array $headers = [])
    {
        $error = new \WP_Error($exception::class, $exception->getMessage());
        parent::__construct($error, $status, $headers);
    }
}
