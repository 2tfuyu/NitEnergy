<?php

namespace NitEnergy\game;

use NitEnergy\game\gamelib\GameLib;
use NitEnergy\Main;
use NitEnergy\member\Member;
use NitEnergy\member\MemberHandler;
use NitEnergy\provider\Provider;
use pocketmine\block\BlockIds;
use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class CTW extends Provider implements Game, Listener
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

    /**
     * @var string
     */
    private $winner;

    public function __construct(Main $plugin)
    {
        parent::__construct(self::FILE_PATH, self::FILE_NAME, null);
        foreach (self::TEAM as $team) {
            $this->teams[$team] = [];
        }
        $this->setting = $this->get("setting");
        foreach ($this->setting as $setting) {
            $this->woolCond[key($setting)] = [];
            foreach ($setting["wools"] as $wool) {
                $this->woolCond[key($setting)][$wool] = false;
            }
        }
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
        $events = [];
        $events["BlockPlacceEvent"] = function (BlockPlaceEvent $e): void
        {
            $player = $e->getPlayer();
            if (MemberHandler::existsMember($player->getName())) {
                $block = $e->getBlock();
                if ($block->getId() === BlockIds::WOOL) {
                    $meta = $block->getVariant();
                    $member = MemberHandler::getMember($player->getName());
                    $teamWool = GameLib::isTeamWool($this->setting[$member->getTeam()]["wools"], $meta);
                    if ($teamWool != null) {
                        if (GameLib::isWoolPlace($this->setting[$member->getTeam()]["wool"], $block)) {
                            $wool_name = ColorBlockMetaHelper::getColorFromMeta($meta);
                            $this->woolCond[$member->getTeam()]["wools"][$wool_name] = true;
                            if (GameLib::isAllWoolPlaced($this->woolCond[$member->getTeam()]["wool"])) {
                                $this->winner = $member->getTeam();
                                $this->finish();
                            }
                        }
                    }
                }
            }
        };
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

        $function = function (Member $member): void
        {
            $message = "You are " . $member->getTeam();
            GameLib::sendMessageToMember($message, $member);
        };
        GameLib::processMembers($function, $this->members);
    }

    /**
     * @param bool $normal
     */
    public function finish(bool $normal = true): void
    {
        if (!$normal) {
            GameLib::sendMessageToMembers("異常が発生したためゲームを終了しました。", $this->members);
        }
        else {
            $winner = $this->winner;
            $achirve = [
                "game" => self::GAME_NAME,
                "date" => date("Y/m/d H:i:s"),
                "teams" => self::TEAM,
                "winner" => $winner
            ];
            $function = function (array $team, Member $member) use($winner, $achirve): void {
                $achirveData = $member->getNested($member->getName() . ".achirve");
                $achirveData[] = $achirve;
                $member->setNested($member->getName() . ".achirve", $achirveData);

                if (key($team) === $winner) {
                    GameLib::sendMessageToMember("You Win!", $member);
                    $function = function (Member $member) use ($winner, $achirve): void {
                        $name = $member->getName();
                        $win = $member->getNested($name . ".win");
                        $member->setNested($name . ".win", ++$win);
                    };
                }
                else {
                    GameLib::sendMessageToMember("You Lose!", $member);
                    $function = function (Member $member) use ($winner, $achirve): void {
                        $name = $member->getName();
                        $lose = $member->getNested($name . ".lose");
                        $member->setNested($name . ".lose", ++$lose);
                    };
                }
                $member->processAchievement($function);
            };
            GameLib::processTeam($function, $this->teams);
        }
        $this->isStarted = false;
        MemberHandler::removeMembers($this->members);
        GameHandler::close($this);
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