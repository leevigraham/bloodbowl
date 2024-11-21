<?php

namespace App\Enum;

enum SpecialRuleCategory: string
{
    case TEAM = 'T';
    case REGIONAL = 'R';

    public function label(): string {
        return match ($this) {
            self::TEAM => "Team",
            self::REGIONAL => "Regional",
        };
    }
    public function description(): string {
        return match ($this) {
            self::TEAM => "Some teams may have one or more of the following special rules. These detail unique characteristics that set the team apart from others, be it the ability to reanimate the dead or the blessings of a patron Chaos deity.\n\nAll teams have one or more of the following special rules:",
            self::REGIONAL => "As noted in their description, some Inducements are available only to teams with one of the following special rules. Other Inducements may be available for a reduced rate to teams with one of the following special rules.\n\nSome teams may have one or more of the following:",
        };
    }
}
