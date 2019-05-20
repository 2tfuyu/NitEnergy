<?php

namespace NitEnergy\game;

class GameHandler
{

    /** @var array */
    private static $games = [];

    public static function open(Game $game): void
    {
        self::$games[$game->getName()] = $game;
    }

    public static function close(Game $game): void
    {
        unset(self::$games[$game->getName()]);
    }

    public static function getGame(string $name): Game
    {
        return self::$games[$name];
    }
}