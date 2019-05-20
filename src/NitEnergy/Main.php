<?php

namespace NitEnergy;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;

class Main extends PluginBase implements Listener
{

    /** @var $path */
    private static $path;

    private static $scheduler;

    public function onEnable(): void
    {
        self::$path = $this->getDataFolder();
        self::$scheduler = $this->getScheduler();

        if (!file_exists(self::$path))
        {
            @mkdir(self::$path);
        }
    }

    public static function getPath(): string
    {
        return self::$path;
    }

    public static function _getScheduler(): TaskScheduler
    {
        return self::$scheduler;
    }
}