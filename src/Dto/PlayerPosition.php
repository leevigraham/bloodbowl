<?php

namespace App\Dto;

use Symfony\Component\String\Slugger\AsciiSlugger;

class PlayerPosition
{
    public function __construct(
        public Team $team,
        public string $qty,
        public string $name,
        public int $cost,
        public int $MA,
        public int $ST,
        public string $AG,
        public string $PA,
        public string $AV,
        /** @var PlayerSkill[] */
        public array $skills,
        /** @var PlayerTrait[] */
        public array $traits,
        public array $primarySkillCategories = [],
        public array $secondarySkillCategories = []
    )
    {
    }

    public function getSlug($prefix = "player-position-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }
    public function skillsAndTraits(): array
    {
        return array_merge($this->skills, $this->traits);
    }
}