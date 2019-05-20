<?php

namespace NitEnergy\game\Debug;

use NitEnergy\game\Game;
use NitEnergy\game\gamelib\GameLib;
use NitEnergy\Main;
use NitEnergy\member\Member;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class Debug implements Game
{

    const TIME = 60 * 5;

    const TEAM = [
        "RED",
        "BLUE"
    ];

    /** @var bool  */
    private $isStarted = false;

    /** @var array */
    private $members = [];

    /** @var array  */
    private $teams = [];

    public function __construct()
    {
        foreach (self::TEAM as $team)
        {
            $this->teams[$team] = [];
        }
        $this->waitTask();
    }

    public function waitTask(): void
    {
        Main::_getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void
            {
                if (count($this->members) < count(self::TEAM))
                {
                    $this->task();
                    return;
                }
                $this->start();
            }
        ), 20 * 60);
    }

    public function timerTask(): void
    {
        Main::_getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void
            {
                $this->finish();
            }
        ), 20 * self::TIME);
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    public function addPlayer(Player $player): void
    {
        $this->members[$player->getName()] = new Member($player);
    }

    public function start(): void
    {
        $this->isStarted = true;
        $this->timerTask();

        $this->randomTeam();
    }

    public function finish(): void
    {
        $this->isStarted = false;
    }

    public function getName(): string
    {
        return "Debug";
    }

    public function randomTeam(): void
    {
        $this->teams = GameLib::randomTeam($this->members, self::TEAM);
    }
}