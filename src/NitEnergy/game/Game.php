<?php

namespace NitEnergy\game;

use pocketmine\Player;

interface Game
{

    public function waitTask(): void;

    public function timerTask(): void;

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @param Player $player
     */
    public function addPlayer(Player $player): void;

    public function start(): void;

    public function finish(): void;

    /**
     * @return string
     * Return game`s name.
     */
    public function getName(): string;
}