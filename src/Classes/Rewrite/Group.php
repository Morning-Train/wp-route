<?php

namespace Morningtrain\WP\Route\Classes\Rewrite;

use Morningtrain\WP\Route\Abstracts\AbstractGroup;
use Morningtrain\WP\Route\Route;

class Group extends AbstractGroup
{
    protected function open(): void
    {
        Route::rewriteRouter()->setCurrentGroup($this);
    }

    protected function close(): void
    {
        Route::rewriteRouter()->setCurrentGroup($this->getGroup());
    }
}
