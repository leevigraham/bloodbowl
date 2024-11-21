<?php

namespace App\Dto;

use App\Enum\SpecialRuleCategory;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Team
{
    public function __construct(
        public string $name,
        public int $tier,
        public int $reRollCost,
        public bool $canHaveApothecary,
        public string $specialRulesSummary,
        /** @var SpecialRule[] */
        public array $specialRules,
        public string $specialRulesType,
        public string $additionalRules,
        public string $ruleBookDescription,
        public string $source,
        public string $errata,
        public array $playerPositions = [],
        public array $starPlayerPositions = [],
    )
    {
    }

    public function getSlug($prefix = "team-"): string {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->name);
        return $prefix.$slug;
    }

    public function getSpecialRulesSummaryWithLinks(): string {
        $summary = $this->specialRulesSummary;
        foreach ($this->specialRules as $label => $rule) {
            $summary = str_replace($label, sprintf('<a href="#%s" data-controller="fragment-dialog-trigger" data-action="fragment-dialog-trigger#show">%s</a>', $rule->getSlug(), $label), $summary);
        }
        return $summary;
    }

    public function getRegionalSpecialRules(): array {
        return array_filter($this->specialRules, fn(SpecialRule $rule) => $rule->category === SpecialRuleCategory::REGIONAL);
    }

    public function getTeamSpecialRules(): array {
        return array_filter($this->specialRules, fn(SpecialRule $rule) => $rule->category === SpecialRuleCategory::TEAM);
    }

    public function controllerData(): array {
        return [
            'starPlayerPositions' => array_map(fn(StarPlayerPosition $starPlayerPosition) => $starPlayerPosition->controllerData(), $this->starPlayerPositions),
        ];
    }
}