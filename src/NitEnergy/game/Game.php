<?php

namespace NitEnergy\game;

use pocketmine\event\Event;
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
     * @return bool
     */
    public function addPlayer(Player $player): bool;

    /**
     * @param Player $player
     * @return bool
     */
    public function removePlayer(Player $player): bool;

    /**
     * @param Player $player
     * @return bool
     */
    public function existsPlayer(Player $player): bool;

    /**
     * @param Event $e
     */
    public function onEvent(Event $e): void;

    public function setEvents(): void;

    public function start(): void;

    /**
     * @param bool $normal
     */
    public function finish(bool $normal = true): void;

    /**
     * @return array
     */
    public function getSetting(): array;

    /**
     * @return string
     * Return game`s name.
     */
    public function getName(): string;
}