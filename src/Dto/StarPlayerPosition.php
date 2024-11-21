<?php

namespace App\Dto;

use Symfony\Component\String\Slugger\AsciiSlugger;

class StarPlayerPosition
{
    public function __construct(
        public string $qty,
        public string $name,
        public int $cost,
        public int $MA,
        public int $ST,
        public string $AG,
        public string $PA,
        public string $AV,
        public array $skills,
        public array $traits,
        public string $specialRules,
        public string $playsForRule,
        public array $playsFor,
        public bool $megaStar,
        public string $source,
        public array $teams = [],
    )
    {
    }

    public function getSlug($prefix = "star-player-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }

    public function skillsAndTraits(): array
    {
        return array_merge($this->skills, $this->traits);
    }

    public function getPlaysForRuleWithLinks(): string {
        $summary = $this->playsForRule;
        foreach ($this->playsFor as $label => $rule) {
            $summary = str_replace($label, sprintf('<a href="#%s" data-controller="fragment-dialog-trigger" data-action="fragment-dialog-trigger#show">%s</a>', $rule->getSlug(), $label), $summary);
        }
        return $summary;
    }
}