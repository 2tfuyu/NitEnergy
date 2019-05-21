<?php

namespace NitEnergy\game;

class GameHandler
{

    /** @var array */
    private static $games = [];

    /**
     * @param Game $game
     * Create new Game instance.
     */
    public static function open(Game $game): void
    {
        self::$games[$game->getName()] = $game;
    }

    /**
     * @param Game $game
     * Close Game.
     */
    public static function close(Game $game): void
    {
        unset(self::$games[$game->getName()]);
    }

    /**
     * @return array
     */
    public static function getGames(): array
    {
        return self::$games;
    }

    /**
     * @param string $name
     * @return Game
     */
    public static function getGame(string $name): ?Game
    {
        return self::$games[$name];
    }
}