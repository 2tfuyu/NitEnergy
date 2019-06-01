<?php

namespace NitEnergy\game\Debug;

use NitEnergy\game\Game;
use NitEnergy\game\GameHandler;
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
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class Debug extends Provider implements Game, Listener
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

    /** @var array */
    private $setting = [];

    /** @var array */
    private $events;

    public function __construct(Main $plugin)
    {
        parent::__construct(self::FILE_PATH, self::FILE_NAME, null);
        foreach (self::TEAM as $team) {
            $this->teams[$team] = [];
        }
        $this->setting = $this->get("setting");
        $this->waitTask();
        $this->setEvents();
        Server::getInstance()->getPluginManager()->registerEvents($this, $plugin);
    }

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
        ), 20 * self::GAME_TIME);
    }

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
        $member->setGameName($this->getName());
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
        $events = [];
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
        $this->events = $events;
    }

    public function start(): void
    {
        $this->isStarted = true;
        $this->timerTask();
        $this->randomTeam();

        $function = function (Member $member): void
        {
            $message = "You are " . $member->getTeam();
            GameLib::sendMessageToMember($message, $member);
        };
        GameLib::processMembers($function, $this->members);
    }

    public function finish(): void
    {
        $this->isStarted = false;

        $winner = GameLib::getKillWinnerTeam($this->teams);
        $achirve = [
            "game" => self::GAME_NAME,
            "date" => date("Y/m/d H:i:s"),
            "teams" => self::TEAM,
            "winner" => $winner
        ];
        $function = function (array $team, Member $member) use($winner, $achirve): void
        {
            if (key($team) === $winner) {
                GameLib::sendMessageToMember("You Win!", $member);
                $function = function (Member $member) use($winner, $achirve): void
                {
                    $name = $member->getName();
                    $win = $member->getNested($name . ".win");
                    $member->setNested($name . ".win", ++$win);

                    $achirveData = $member->getNested($name . ".achirve");
                    $achirveData[] = $achirve;
                    $member->setNested($name . ".achirve", $achirveData);
                };
            }
            else {
                GameLib::sendMessageToMember("You Lose!", $member);
                $function = function (Member $member) use($winner, $achirve): void
                {
                    $name = $member->getName();
                    $lose = $member->getNested($name . ".lose");
                    $member->setNested($name . ".lose", ++$lose);

                    $achirveData = $member->getNested($name . ".achirve");
                    $achirveData[] = $achirve;
                    $member->setNested($name . ".achirve", $achirveData);
                };
            }
            $member->processAchievement($function);
        };
        GameLib::processTeam($function, $this->teams);
        GameHandler::close($this);
    }

    /**
     * @return array
     * Get this game`s setting.
     */
    public function getSetting(): array
    {
        return $this->setting;
    }

    /**
     * @return string
     * Get game name.
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