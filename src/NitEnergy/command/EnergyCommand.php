<?php

namespace NitEnergy\command;

use NitEnergy\game\GameHandler;
use NitEnergy\member\MemberHandler;
use NitEnergy\provider\SystemDB;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;

class EnergyCommand extends Command
{

    /** @var string  */
    const FILE_PATH = "%DATA_PATH%/System";

    /** @var string  */
    const FILE_NAME = "System.yml";

    /** @var SystemDB */
    private $config;

    public function __construct()
    {
        parent::__construct("nitenergy", "NitEnergy", "/nitenergy");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     * @throws CommandException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool
    {
        $config = new SystemDB(self::FILE_PATH, self::FILE_NAME, null);
        $this->config = $config;

        if (!$sender instanceof Player) return false;

        switch ($args[0])
        {
            case "gamelist":
                $games = GameHandler::getGames();
                $message = "";
                foreach ($games as $game)
                {
                    $message .= key($game);
                    $message .= "\n";
                }
                $sender->sendMessage($message);
                return true;
            case "select":
                if ($args[1] === null) break;

                $game = GameHandler::getGame($args[1]);
                if ($game === null) break;
                if (!$game->addPlayer($sender))
                {
                    $sender->sendMessage("This game has already started.");
                }
                else
                {
                    $sender->sendMessage("You are joined.");
                }
                return true;
            case "cancel":
                $member = MemberHandler::getMember($sender->getName());
                if ($member === null) return false;
                $game_name = $member->getGameName();
                $game = GameHandler::getGame($game_name);
                if ($game->removePlayer($member->getPlayer()))
                {
                    $sender->sendMessage("You are left this game.");
                }
                else
                {
                    $sender->sendMessage("You are not joined this game.");
                }
                return true;
        }
        return false;
    }
}