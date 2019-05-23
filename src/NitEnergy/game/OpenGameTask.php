<?php

namespace NitEnergy\game;

use pocketmine\scheduler\Task;

class OpenGameTask extends Task
{

    public function onRun(int $currentTick)
    {
        $gameList = GameHandler::getGameList();
        foreach ($gameList as $gameName) {
            $game = GameHandler::getGame($gameName);
            if (!$game === null) {
                GameHandler::open(GameHandler::createGame($game));
            }
        }
    }
}