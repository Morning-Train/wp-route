<?php

namespace Morningtrain\WP\Route\Classes\Rest;

use Morningtrain\WP\Route\Abstracts\AbstractRoute;

/**
 * @property Group $group
 */
class Route extends AbstractRoute
{
    protected ?bool $public = null;
    protected ?bool $exposed = null;
    protected $permissionCallback = null;

    public function register(): void
    {
        \register_rest_route($this->getNamespace(), $this->getPath(), $this->getArgs());
    }

    public function getUrl(?array $args = []): string
    {
        $tokens = array_map(
            function ($k) {
                return "{" . $k . "}";
            },
            array_keys($args)
        );
        $newPath = str_replace($tokens, array_values($args), $this->getPath());

        return \rest_url($this->getNamespace() . "/" . $newPath);
    }

    public function getNamespace(): string
    {
        $namespace = $this->group?->getNamespace();
        if (empty($namespace)) {
            $namespace = \Morningtrain\WP\Route\Route::restRouter()->getGlobalNamespace();
        }

        return $namespace;
    }

    public function getArgs(): array
    {
        return [
            'methods' => $this->getRequestMethods(),
            'permission_callback' => $this->getPermissionCallback(),
            'callback' => \Morningtrain\WP\Route\Route::restRouter()->addCallback($this),
        ];
    }

    public function getPermissionCallback(): string|callable|null
    {
        if ($this->permissionCallback !== null) {
            return $this->permissionCallback;
        }
        if ($this->public === true || $this->group?->isPublic()) {
            return '__return_true';
        }

        return '__return_false';
    }

    public function permissionCallback(string|callable $callback): static
    {
        $this->permissionCallback = $callback;

        return $this;
    }

    public function public(bool $public = true): static
    {
        $this->public = $public;

        return $this;
    }

    public function expose(bool $exposed = true): static
    {
        $this->exposed = $exposed;

        return $this;
    }

    public function isExposed(): bool
    {
        if ($this->exposed === true || $this->group?->isExposed()) {
            return true;
        }

        return false;
    }
}
