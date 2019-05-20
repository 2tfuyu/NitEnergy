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
        $path = replace("%DATA_PATH%", Main::getPath(), $path);
        $this->config = new Config($path . $name, Config::YAML, $inputData);
        $this->data = $this->config->getAll(true);
    }

    public function set(string $key, array $inputData): void
    {
        $this->data[$key] = $inputData;
    }

    public function save(): void
    {
        $this->config->setAll($this->data);
        $this->config->save();
    }
}