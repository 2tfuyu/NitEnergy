<?php

namespace NitEnergy\game\gamelib;

class GameLib
{

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

    public static function sendMessageToTeam(string $message, array $team): void
    {
        foreach ($team as $member)
        {
            $member->getPlayer()->sendMessage($message);
        }
    }

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
}