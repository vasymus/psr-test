<?php

namespace App\Service\Config;

final class ConfigRepository implements ConfigRepositoryInterface
{
    private array $configs = [];

    public function __construct(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            $this->configs[$name] = require $file;
        }
    }

    public function get(string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);

        $current = $this->configs;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    public function set(string $path, mixed $value): void
    {
        $keys = explode('.', $path);

        $current = &$this->configs;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current = $value;
    }

    public function all(): array
    {
        return $this->configs;
    }
}
