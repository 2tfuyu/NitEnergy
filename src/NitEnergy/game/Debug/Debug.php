<?php

namespace NitEnergy\game\Debug;

use NitEnergy\game\Game;
use NitEnergy\game\gamelib\GameLib;
use NitEnergy\Main;
use NitEnergy\member\Member;
use NitEnergy\provider\Provider;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class Debug extends Provider implements Game
{

    /** @var string  */
    const FILE_PATH = "%DATA_PATH%/Debug";

    /** @var string  */
    const FILE_NAME = "Debug.yml";

    /** @var string  */
    const GAME_NAME = "Debug";

    /** @var float|int  */
    const GAME_TIME = 60 * 5;

    /** @var float|int  */
    const WAIT_TIME = 60 * 1;

    /** @var array  */
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
        parent::__construct(self::FILE_PATH, self::FILE_NAME, null);
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
                    $this->waitTask();
                    return;
                }
                $this->start();
            }
        ), 20 * self::WAIT_TIME);
    }

    public function timerTask(): void
    {
        Main::_getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void
            {
                $this->finish();
            }
        ), 20 * self::GAME_TIME);
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    public function addPlayer(Player $player): void
    {
        $this->members[$player->getName()] = new Member($player);
    }

    public function removePlayer(Player $player): void
    {
        unset($this->members[$player->getName()]);
    }

    public function start(): void
    {
        $this->isStarted = true;
        $this->timerTask();
        $this->randomTeam();

        foreach ($this->members as $member)
        {
            $message = "You are " . $member->getTeam();
            GameLib::sendMessageToMember($message, $member);
        }
    }

    public function finish(): void
    {
        $this->isStarted = false;

    }

    public function getName(): string
    {
        return self::GAME_NAME;
    }

    public function randomTeam(): void
    {
        $this->teams = GameLib::randomTeam($this->members, self::TEAM);
    }
}