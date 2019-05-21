<?php

namespace NitEnergy\provider;

use NitEnergy\Main;
use pocketmine\utils\Config;

abstract class Provider
{

    /** @var Config  */
    private $config;

    /** @var array  */
    private $data;

    public function __construct(string $path, string $name, ?array $inputData)
    {
        $path = str_replace("%DATA_PATH%", Main::getPath(), $path);
        $this->config = new Config($path . $name, Config::YAML, $inputData);
        $this->data = $this->config->getAll(true);
    }

    /**
     * @param string $key
     * @param array $inputData
     */
    public function set(string $key, array $inputData): void
    {
        $this->data[$key] = $inputData;
    }

    /**
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return $this->data[$key];
    }

    /**
     * @param string $key
     */
    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function save(): void
    {
        $this->config->setAll($this->data);
        $this->config->save();
    }
}