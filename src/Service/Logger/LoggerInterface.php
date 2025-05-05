<?php

namespace App\Service\Logger;

interface LoggerInterface
{
    public function info(string $message): void;

    public function debug(string $message): void;

    public function warn(string $message): void;

    public function error(string $message): void;
}
