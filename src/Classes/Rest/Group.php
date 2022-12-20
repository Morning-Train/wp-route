<?php

namespace Morningtrain\WP\Route\Classes\Rest;

use Morningtrain\WP\Route\Abstracts\AbstractGroup;
use Morningtrain\WP\Route\Route;

class Group extends AbstractGroup
{
    protected ?string $namespace = null;
    protected ?bool $public = null;
    protected ?bool $exposed = null;

    protected function open(): void
    {
        Route::restRouter()->setCurrentGroup($this);
    }

    protected function close(): void
    {
        Route::restRouter()->setCurrentGroup($this->getGroup());
    }

    public function namespace(string $namespace): static
    {
        $this->namespace = trim($namespace, '/');

        return $this;
    }

    public function getNamespace(): ?string
    {
        if ($this->namespace !== null) {
            return $this->namespace;
        }

        return $this->group?->getNamespace();
    }

    public function public(bool $public = true): static
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): bool
    {
        if ($this->public !== null) {
            return $this->public;
        }

        return $this->group?->isPublic();
    }

    public function expose(bool $expose): static
    {
        $this->exposed = $expose;

        return $this;
    }

    public function isExposed(): bool
    {
        if ($this->exposed !== null) {
            return $this->exposed;
        }

        return $this->group?->isExposed();
    }
}
