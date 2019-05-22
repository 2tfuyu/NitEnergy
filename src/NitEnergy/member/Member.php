<?php

namespace NitEnergy\member;

use pocketmine\Player;

class Member
{

    /** @var Player  */
    private $player;

    /** @var string */
    private $team;

    /** @var int */
    private $kill;

    /** @var int */
    private $death;

    /** @var string */
    private $gameName;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function respawn(): void
    {
        // Gameのセッティングデータからリスポーン座標を取得する
    }

    /**
     * @param string $name
     */
    public function setTeam(string $name): void
    {
        $this->team = $name;
    }

    /**
     * @return string
     */
    public function getTeam(): string
    {
        return $this->team;
    }

    public function addKill(): void
    {
        $this->kill++;
    }

    public function addDeath(): void
    {
        $this->death++;
    }

    /**
     * @return int
     */
    public function getKill(): int
    {
        return $this->kill;
    }

    /**
     * @return int
     */
    public function getDeath(): int
    {
        return $this->death;
    }

    /**
     * @param string $name
     */
    public function setGameName(string $name): void
    {
        $this->gameName = $name;
    }

    /**
     * @return string
     */
    public function getGameName(): string
    {
        return $this->gameName;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->player->getName();
    }
}