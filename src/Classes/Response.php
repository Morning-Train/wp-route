<?php

namespace Morningtrain\WP\Route\Classes;

use Morningtrain\WP\Route\Responses\WPErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
    public static function with(string $content, int $status = 200, array $headers = []): SymfonyResponse
    {
        return new SymfonyResponse($content, $status, $headers);
    }

    public static function withView(string $view, array $data = []): SymfonyResponse
    {
        if (! class_exists('\Morningtrain\WP\View\View')) {
            throw new \Exception('no_view_lol');
        }

        return new SymfonyResponse(\Morningtrain\WP\View\View::render($view, $data));
    }

    public static function withJSON(mixed $data, int $status = 200, array $headers = []): SymfonyResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public static function with404(string $message = '404'): SymfonyResponse
    {
        return new SymfonyResponse($message, 404);
    }

    public static function withError(\WP_Error $error, int $status = 500, array $headers = []): WPErrorResponse
    {
        return new WPErrorResponse($error, $status, $headers);
    }

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
