<?php

namespace App\Dto;

use App\Enum\SpecialRuleCategory;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SpecialRule
{
    public function __construct(
        public SpecialRuleCategory $category,
        public string              $name,
        public string              $ruleBookDescription,
        public array               $teams = [],
        public array               $starPlayerPositions = [],
    )
    {
    }

    public function getSlug($prefix = "special-rule-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }
}