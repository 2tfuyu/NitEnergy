<?php

namespace NitEnergy\game\gamelib;

use NitEnergy\member\Member;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\block\Wool;
use pocketmine\math\Vector3;

class GameLib
{

    /**
     * @param string $message
     * @param array $teams
     * Send message to All Team`s member.
     */
    public static function sendMessageToMembers(string $message, array $teams): void
    {
        foreach ($teams as $team)
        {
            foreach ($team as $member)
            {
                $member->getPlayer()->sendMessage($message);
            }
        }
    }

    /**
     * @param string $message
     * @param array $team
     * Send message to a Team`s member.
     */
    public static function sendMessageToTeam(string $message, array $team): void
    {
        foreach ($team as $member) {
            $member->getPlayer()->sendMessage($message);
        }
    }

    /**
     * @param string $message
     * @param Member $member
     */
    public static function sendMessageToMember(string $message, Member $member): void
    {
        $member->getPlayer()->sendMessage($message);
    }

    /**
     * @param callable $function
     * @param array $teams
     */
    public static function processMembers(callable $function, array $teams): void
    {
        foreach ($teams as $team) {
            foreach ($team as $member) {
                $function($member);
            }
        }
    }

    /**
     * @param callable $function
     * @param array $team
     */
    public static function processTeam(callable $function, array $team): void
    {
        foreach ($team as $member) {
            $function($member);
        }
    }

    /**
     * @param callable $function
     * @param array $teams
     */
    public static function processTeams(callable $function, array $teams): void
    {
        foreach ($teams as $team) {
            foreach ($team as $member) {
                $function($team, $member);
            }
        }
    }

    /**
     * @param array $members
     * @param array $team
     * @return array
     * Shuffle member.
     */
    public static function randomTeam(array $members, array $team): array
    {
        shuffle($members);
        $i = 0;
        $teams = [];
        foreach ($members as $member)
        {
            if ($i > count($team))
            {
                $i = 0;
            }
            $team = $team[$i];
            $teams[$team] = $member;
            $member->setTeam($team);
            $i++;
        }
        return $teams;
    }

    /**
     * @param array $pos
     * @param Block $wool
     * @return bool
     */
    public static function isWoolPlace(array $pos, Block $wool): bool
    {
        return (
            $wool->getX() === $pos["X"] &&
            $wool->getY() === $pos["Y"] &&
            $wool->getZ() === $pos["Z"]
        );
    }

    public static function isAllWoolPlaced(array $wools): bool
    {
        foreach ($wools as $wool) {
            if (!$wool) return false;
        }
        return true;
    }

    /**
     * @param array $wools
     * @param int $meta
     * @return int|null
     */
    public static function isTeamWool(array $wools, int $meta): ?int
    {
        foreach ($wools as $wool) {
            if (key($wool) === $meta) {
                $id = $wool;
            }
        }
        return $wool ?? null;
    }

    /**
     * @param array $teams
     * @return string
     * Check the most biggest kill count.
     */
    public static function getKillWinnerTeam(array $teams): string
    {
        $kills = [];
        foreach ($teams as $team)
        {
            foreach ($team as $member)
            {
                $kills[key($team)] += $member->getKill();
            }
        }

        sort($kills, SORT_ASC);
        $tmp = [];
        foreach ($kills as $kill)
        {
            $tmp[] = $kill;
        }

        return $tmp[0];
    }
}