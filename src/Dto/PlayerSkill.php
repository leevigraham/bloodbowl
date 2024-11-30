<?php

namespace App\Dto;

use App\Enum\PlayerSkillCategory;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\String\s;

class PlayerSkill
{
    public function __construct(
        public string $firstD6,
        public string $secondD6,
        public string $name,
        public PlayerSkillCategory $category,
        public string $ruleBookDescription,
        public bool $compulsory,
        public string $source,
        public array $playerPositions = [],
        public array $starPlayerPositions = [],
    )
    {
    }

    public function getSlug($prefix = "player-skill-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }
}