<?php

namespace NitEnergy\game;

use NitEnergy\game\gamelib\GameLib;
use NitEnergy\Main;
use NitEnergy\member\Member;
use NitEnergy\member\MemberHandler;
use NitEnergy\provider\Provider;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class Flag extends Provider implements Game, Listener
{

    /** @var string  */
    const FILE_PATH = "%DATA_PATH%/CTW";

    /** @var string  */
    const FILE_NAME = "CTW.yml";

    /** @var string  */
    const GAME_NAME = "CTW";

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

    /** @var array */
    private $setting = [];

    /** @var array */
    private $events;

    /** @var array  */
    private $woolCond = [];

    public function waitTask(): void
    {
        Main::_getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void
            {
                if (count($this->members) < count(self::TEAM)) {
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
        ), 20 * self::WAIT_TIME);
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function addPlayer(Player $player): bool
    {
        if ($this->isStarted) {
            return false;
        }
        $member = new Member($player);
        $member->setGameName(self::GAME_NAME);
        MemberHandler::addMember($member);
        $this->members[$player->getName()] = $member;
        return true;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function removePlayer(Player $player): bool
    {
        if ($this->isStarted) {
            return false;
        }
        MemberHandler::removeMember($player->getName());
        unset($this->members[$player->getName()]);
        return true;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function existsPlayer(Player $player): bool
    {
        return isset($this->members[$player->getName()]);
    }

    /**
     * @param Event $e
     */
    public function onEvent(Event $e): void
    {
        $this->events[$e->getEventName()]($e);
    }

    public function setEvents(): void
    {
        $events["PlayerDeathEvent"] = function (PlayerDeathEvent $e): void
        {
            $player = $e->getPlayer();
            if ($this->existsPlayer($player)) {
                $playerMember = MemberHandler::getMember($player);
                $damageCause = $player->getLastDamageCause();
                if (!$damageCause instanceof EntityDamageByEntityEvent) {
                    $playerMember->respawn();
                    return;
                }
                $damager = $damageCause->getDamager();
                $damagerMember = ($damager instanceof Player) ? MemberHandler::getMember($damager) : null;
                if ($damagerMember === null) return;

                $playerMember->addDeath();
                $playerMember->respawn();
                $damagerMember->addKill();
            }
        };
        $events["EntityDamageEvent"] = function (EntityDamageEvent $e): void
        {
            $entity = $e->getEntity();
            if ($entity instanceof Player) {
                $player = $entity;
                if ($this->existsPlayer($player)) {
                    $playerMember = MemberHandler::getMember($player);
                    if (!$e instanceof EntityDamageByEntityEvent) return;

                    $damager = $e->getDamager();
                    if ($damager instanceof Player) return;

                    $damagerMember = MemberHandler::getMember($damager);
                    if ($playerMember->getTeam() === $damagerMember->getTeam()) {
                        $e->setCancelled();
                        return;
                    }
                }
            }
        };
        $events["PlayerQuitEvent"] = function (PlayerQuitEvent $e): void
        {
            $player = $e->getPlayer();
            $name = $player->getName();
            if (MemberHandler::existsMember($name)) {
                $member_player = MemberHandler::getMember($name);
                $game_name = $member_player->getGameName();
                if ($game_name === self::GAME_NAME) {
                    $team_name = $member_player->getTeam();
                    if (count($this->teams[$team_name]) < 1) {
                        $this->finish(false);
                    }
                    MemberHandler::removeMember($name);
                }
            }
        };
        $this->events = $events;
    }

    public function start(): void
    {
        $this->isStarted = true;
        $this->timerTask();
        $this->randomTeam();
    }

    /**
     * @param bool $normal
     */
    public function finish(bool $normal = true): void
    {
        // TODO: Implement finish() method.
    }

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return $this->setting;
    }

    /**
     * @return string
     * Return game`s name.
     */
    public function getName(): string
    {
        return self::GAME_NAME;
    }

    public function randomTeam(): void
    {
        $this->teams = GameLib::randomTeam($this->members, self::TEAM);
    }
}