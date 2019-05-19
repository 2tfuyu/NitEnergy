<?php

namespace NitEnergy\provider;

use pocketmine\Player;

interface Provider
{

    /**
     * Create file.
     */
    public function open(): void;

    /**
     * @param Player $player
     * @return bool
     * Check the account existence.
     */
    public function existsAccount(Player $player): bool;

    /**
     * @param Player $player
     */
    public function createAccount(Player $player): void;

    /**
     * @param Player $player
     */
    public function removeAccount(Player $player): void;
}