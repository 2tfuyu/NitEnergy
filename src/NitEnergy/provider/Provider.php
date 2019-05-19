<?php

namespace NitEnergy\provider;

use NitEnergy\Main;
use pocketmine\utils\Config;

abstract class Provider
{

    private $config;
    private $data;

    public function __construct(string $name, array $input_data)
    {
        $this->config = new Config(Main::getInstance()->getDataFolder() . $name, Config::YAML, $input_data);
        $data = $this->
    }
}