<?php

declare(strict_types=1);

namespace src\Integration;

final readonly class DataProvider
{
    public function __construct(
        public string $host,
        public string $user,
        public string $password,
    ) {}
}