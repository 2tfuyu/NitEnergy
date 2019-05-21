<?php

namespace NitEnergy\command;

use NitEnergy\provider\Provider;

class SystemDB extends Provider
{
    public function __construct(string $path, string $name, ?array $inputData)
    {
        parent::__construct($path, $name, $inputData);
    }

    public function get(string $key): array
    {
        return parent::get($key);
    }
}