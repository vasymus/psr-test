<?php

namespace App\Service\Config;

interface ConfigRepositoryInterface
{
    public function get(string $path, mixed $default = null): mixed;

    public function set(string $path, mixed $value): void;

    public function all(): array;
}
