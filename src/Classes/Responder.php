<?php

namespace Morningtrain\WP\Route\Classes;

use Symfony\Component\HttpFoundation\Response;

class Responder
{
    public static function respond($response, ...$args): void
    {
        // Handle View responses
        if (is_a($response, '\Illuminate\Contracts\View\View')) {
            static::respondWithView($response);
        }
        // Handle thrown exceptions
        if (is_a($response, \Exception::class)) {
            static::respondWithError($response);
        }
        // Handle simple strings
        if (is_string($response)) {
            static::respondWithString($response);
        }
        // Handle Symfony Responses
        if (is_a($response, Response::class)) {
            switch ($response->getStatusCode()) {
                case 404:
                    static::respondWithWordPressTemplate('404');
                default:
                    $response->send();
            }
        }
        exit;
    }

    public static function respondWithString(string $string): void
    {
        echo $string;
    }

    public static function respondWithWordPressTemplate(string $template): void
    {
        // For a block theme
        $blockTemplate = \get_block_template(\get_stylesheet() . '//' . $template);
        if (is_a($blockTemplate, \WP_Block_Template::class)) {
            \wp_head();
            echo \do_blocks($blockTemplate->content);
            \wp_footer();
        }

        exit;
    }

    public static function respondWithView($view): void
    {
        echo $view->render();
    }

    public static function respondWithJSON($response)
    {
        // Send JSON response - this also sets headers
        \wp_send_json($response);
        exit;
    }

    public static function respondWithXml()
    {
        // Set Header
        // Send Response
        // TODO: Well, make some xml, I guess
    }
}
