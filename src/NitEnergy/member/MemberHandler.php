<?php

namespace NitEnergy\member;

class MemberHandler
{

    /** @var array  */
    private static $members = [];

    /**
     * @param Member $member
     */
    public static function addMember(Member $member): void
    {
        self::$members[$member->getName()] = $member;
    }

    /**
     * @param string $name
     */
    public static function removeMember(string $name): void
    {
        unset(self::$members[$name]);
    }

    /**
     * @param array $members
     */
    public static function removeMembers(array $members): void
    {
        foreach ($members as $member) {
            unset(self::$members[$member->getName()]);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function existsMember(string $name): bool
    {
        return isset(self::$members[$name]);
    }

    /**
     * @param string $name
     * @return Member|null
     */
    public static function getMember(string $name): ?Member
    {
        return self::$members[$name];
    }
}