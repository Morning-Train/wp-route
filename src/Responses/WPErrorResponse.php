<?php

namespace Morningtrain\WP\Route\Responses;

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

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setLink(string $linkUrl, string $linkText): static
    {
        $this->linkUrl = $linkUrl;
        $this->linkText = $linkText;

        return $this;
    }

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

    public function sendContent(): static
    {
        $this->update();

        return parent::sendContent();
    }

}
