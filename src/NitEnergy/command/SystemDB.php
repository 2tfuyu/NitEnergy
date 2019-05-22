<?php

namespace NitEnergy\command;

use NitEnergy\provider\Provider;

class SystemDB extends Provider
{
    public function __construct(string $path, string $name, ?array $inputData)
    {
        parent::__construct($path, $name, $inputData);
    }

    /**
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return parent::get($key);
    }

    /**
     * @param string $key
     * @param array $input_data
     */
    public function set(string $key, array $input_data): void
    {
        parent::set($key, $input_data);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return parent::exists($key);
    }
}