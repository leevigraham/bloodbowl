<?php

namespace App\Dto;

use Symfony\Component\String\Slugger\AsciiSlugger;

class PlayerTrait
{
    public function __construct(
        public string $name,
        public string $ruleBookDescription,
        public bool $compulsory,
        public array $playerPositions = [],
        public array $starPlayerPositions = [],
    )
    {
    }

    public function getSlug($prefix = "player-trait-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }
}