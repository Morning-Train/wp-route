<?php

namespace Morningtrain\WP\Route\Classes;

use Morningtrain\WP\Route\Responses\WPErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
    /**
     * Respond to a request with a string
     *
     * @param  string  $content  The response content
     * @param  int  $status  The HTTP status code
     * @param  array  $headers  Additional headers
     *
     * @return SymfonyResponse
     */
    public static function with(string $content, int $status = 200, array $headers = []): SymfonyResponse
    {
        return new SymfonyResponse($content, $status, $headers);
    }

    /**
     * Respond with a view
     *
     * @param  string  $view
     * @param  array  $data
     * @return SymfonyResponse
     *
     * @throws \Exception
     */
    public static function withView(string $view, array $data = []): SymfonyResponse
    {
        if (! class_exists('\Morningtrain\WP\View\View')) {
            throw new \Exception('no_view_lol');
        }

        return new SymfonyResponse(\Morningtrain\WP\View\View::render($view, $data));
    }

    /**
     * Respond with JSON. application/json content header is set automatically
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     *
     * @return SymfonyResponse
     */
    public static function withJSON(mixed $data, int $status = 200, array $headers = []): SymfonyResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Set HTTP status code 404
     *
     * @param  string  $message
     * @return SymfonyResponse
     */
    public static function with404(string $message = '404'): SymfonyResponse
    {
        return new SymfonyResponse($message, 404);
    }

    /**
     * Respond with a WordPress error page
     *
     * @param  \WP_Error  $error
     * @param  int  $status
     * @param  array  $headers
     *
     * @return WPErrorResponse
     */
    public static function withError(\WP_Error $error, int $status = 500, array $headers = []): WPErrorResponse
    {
        return new WPErrorResponse($error, $status, $headers);
    }

    /**
     * Respond with a WordPress page template
     *
     * @param  string  $template
     * @param  int  $status
     * @param  array  $headers
     *
     * @return SymfonyResponse
     */
    public static function withWordPressTemplate(
        string $template,
        int $status = 200,
        array $headers = []
    ): SymfonyResponse {
        ob_start();
        // For a block theme
        $blockTemplate = \get_block_template(\get_stylesheet() . '//' . $template);
        if (is_a($blockTemplate, \WP_Block_Template::class)) {
            \wp_head();
            echo \do_blocks($blockTemplate->content);
            \wp_footer();
        }

        return static::with((string) ob_get_clean(), $status, $headers);
    }
}
