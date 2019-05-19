<?php

namespace NitEnergy;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    private static $instance;

    public function onEnable(): void
    {
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}