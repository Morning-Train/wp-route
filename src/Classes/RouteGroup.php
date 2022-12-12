<?php

    namespace Morningtrain\WP\Route\Classes;

    class RouteGroup extends Group
    {
        private string $prefix = '';

        public function prefix(string $prefix): static
        {
            $this->prefix = trim($prefix, '/');

            return $this;
        }

        public function getPrefix(): string
        {
            return implode('/', array_filter([$this->group?->getPrefix(), $this->prefix]));
        }
    }
