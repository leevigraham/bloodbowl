<?php

namespace App\Enum;

enum PlayerSkillCategory: string
{
    case AGILITY = 'A';
    case PASSING = 'P';
    case STRENGTH = 'S';
    case GENERAL = 'G';
    case MUTATIONS = 'M';

    public function getLabel(): string
    {
        return match ($this) {
            self::AGILITY => 'Agility',
            self::PASSING => 'Passing',
            self::STRENGTH => 'Strength',
            self::GENERAL => 'General',
            self::MUTATIONS => 'Mutation',
        };
    }
}
