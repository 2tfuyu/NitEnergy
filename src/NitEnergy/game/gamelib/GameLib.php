<?php

namespace NitEnergy\game\gamelib;

use NitEnergy\member\Member;

class GameLib
{

    /**
     * @param string $message
     * @param array $teams
     * Send message to All Team`s member.
     */
    public static function sendMessageToTeams(string $message, array $teams): void
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
        foreach ($team as $member)
        {
            $member->getPlayer()->sendMessage($message);
        }
    }

    public static function sendMessageToMember(string $message, Member $member): void
    {
        $member->getPlayer()->sendMessage($message);
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