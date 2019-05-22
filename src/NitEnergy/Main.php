<?php

namespace NitEnergy;

use NitEnergy\event\JoinEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

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

        $listeners = [
            new JoinEvent()
        ];
        $server = Server::getInstance();
        foreach ($listeners as $listener){
            $server->getPluginManager()->registerEvents($listener, $this);
        }
    }

    /**
     * @return string
     */
    public static function getPath(): string
    {
        return self::$path;
    }

    /**
     * @return TaskScheduler
     */
    public static function _getScheduler(): TaskScheduler
    {
        return self::$scheduler;
    }
}