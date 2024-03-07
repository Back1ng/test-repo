<?php

declare(strict_types=1);

namespace src\Integration;

interface ClientInterface
{
    public function get(array $request): array;
}