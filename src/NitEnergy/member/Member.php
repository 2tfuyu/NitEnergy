<?php

namespace NitEnergy\member;

use pocketmine\Player;

class Member
{

    /** @var Player  */
    private $player;

    private $team;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function setTeam(string $name): void
    {
        $this->team = $name;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getName(): string
    {
        return $this->player->getName();
    }
}