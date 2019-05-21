<?php

namespace NitEnergy\command;

use NitEnergy\game\GameHandler;
use NitEnergy\member\Member;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;

class EnergyCommand extends Command
{

    /** @var string  */
    const FILE_PATH = "%DATA_PATH%/System";

    /** @var string  */
    const FILE_NAME = "System.yml";

    /** @var SystemDB */
    private $config;

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
                $game->addPlayer($sender);
                return true;
        }
        return false;
    }
}