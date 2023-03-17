<?php

namespace Morningtrain\WP\Route\Responses;

/**
 * A Symfony Response that represents an error. Displays a wp_die
 */
class WPErrorResponse extends \Symfony\Component\HttpFoundation\Response
{
    protected \WP_Error $error;
    protected string $title = '';
    protected string $linkUrl = '';
    protected string $linkText = '';
    protected bool $backLink = false;

    public function __construct(\WP_Error $error, int $status, array $headers = [])
    {
        $this->error = $error;

        parent::__construct($this->generateContent(), $status, $headers);
    }

    /**
     * Set the <head> title
     *
     * @param  string  $title
     * @return $this
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Display a link
     *
     * @param  string  $linkUrl
     * @param  string  $linkText
     * @return $this
     *
     * @see https://developer.wordpress.org/reference/functions/wp_die/#parameters
     */
    public function setLink(string $linkUrl, string $linkText): static
    {
        $this->linkUrl = $linkUrl;
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * Display a link back
     *
     * @param  bool  $backlink
     * @return $this
     *
     * @see https://developer.wordpress.org/reference/functions/wp_die/#parameters
     */
    public function displayBacklink(bool $backlink = true): static
    {
        $this->backLink = $backlink;

        return $this;
    }

    public function update(): static
    {
        $this->setContent($this->generateContent());

        return $this;
    }

    /**
     * Generate the content
     *
     * @return false|string
     */
    public function generateContent()
    {
        ob_start();

        \wp_die($this->error, $this->title, [
            'response' => $this->statusCode,
            'link_url' => $this->linkUrl,
            'link_text' => $this->linkText,
            'back_link' => $this->backLink,
            'exit' => false,
        ]);

        return ob_get_clean();
    }

    /**
     * Update and send the content
     *
     * @return $this
     */
    public function sendContent(): static
    {
        $this->update();

        return parent::sendContent();
    }

}
