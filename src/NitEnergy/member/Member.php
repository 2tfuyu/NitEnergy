<?php

namespace NitEnergy\member;

use NitEnergy\game\GameHandler;
use NitEnergy\provider\PlayerData;
use NitEnergy\provider\Provider;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Member extends Provider implements PlayerData
{

    /** @var string  */
    const FILE_PATH = "%DATA_PATH%/Player";

    /** @var string  */
    const FILE_NAME = "Player.yml";

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
        parent::__construct(self::FILE_PATH, self::FILE_NAME, null);
        $this->player = $player;
    }

    /**
     * Teleport player to player`s team position.
     */
    public function respawn(): void
    {
        $game = GameHandler::getGame($this->getGameName());
        $setting = $game->getSetting()[$this->team];
        $respawn = $setting["respawn"];
        $vector = new Vector3($respawn["x"], $respawn["y"], $respawn["z"]);
        $this->player->teleport($vector, $respawn["level"]);
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
     * @param callable|null $function
     */
    public function processAchievement(?callable $function = null): void
    {
        if (!$function === null) {
            $function($this);
        }
        $kill = $this->get($this->getName() . ".kill");
        $kill += $this->getKill();
        $this->setNested($this->getName() . ".kill", $kill);

        $death = $this->get($this->getName() . ".death");
        $death += $this->getDeath();
        $this->setNested($this->getName() . ".death", $death);
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

    /**
     * @param Player $player
     * @return bool
     * Check the account existence.
     */
    public function existsAccount(Player $player): bool
    {
        return false;
    }

    /**
     * @param Player $player
     */
    public function createAccount(Player $player): void
    {
    }

    /**
     * @param Player $player
     */
    public function removeAccount(Player $player): void
    {
    }
}