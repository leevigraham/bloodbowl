<?php

namespace App;

use App\Dto\DesignersCommentary;
use App\Dto\Inducement;
use App\Dto\PlayerPosition;
use App\Dto\PlayerSkill;
use App\Dto\PlayerTrait;
use App\Dto\SpecialRule;
use App\Dto\StarPlayerPosition;
use App\Dto\Team;
use App\Enum\PlayerSkillCategory;
use App\Enum\SpecialRuleCategory;
use League\Csv\Reader;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Yaml\Yaml;

class DataProvider
{
    /** @var SpecialRule[] $specialRules */
    public array $specialRules = [];

    /** @var Team[] $teams */
    public array $teams = [];

    /** @var PlayerSkill[] $playerSkills */
    public array $playerSkills = [];

    /** @var PlayerTrait[] $playerTraits */
    public array $playerTraits = [];

    /** @var PlayerPosition[] $playerPositions */
    public array $playerPositions = [];

    /** @var StarPlayerPosition[] $starPlayerPositions */
    public array $starPlayerPositions = [];
    /** @var DesignersCommentary[] */
    public array $designersCommentary = [];
    public array $inducements = [];

    public function __construct()
    {
        $this->load();
    }

    private function load(): void
    {
        $srcDir = dirname(__DIR__) . '/data';
        /**
         * Parse the special rules
         */
        $specialRulesRecords = Reader::createFromPath("$srcDir/special-rules.csv")
            ->setHeaderOffset(0)
            ->getRecords();

        foreach ($specialRulesRecords as $record) {
            $specialRule = new SpecialRule(
                match($record['Category']){
                    'Team' => SpecialRuleCategory::TEAM,
                    'Regional' => SpecialRuleCategory::REGIONAL,
                    default => throw new \Exception("Unknown special rule category: {$record['Category']}"),
                },
                $record['Name'],
                $record['Rule Book Description'],
                $record['Source'],
            );
            $this->specialRules[$specialRule->name] = $specialRule;
        }

        /**
         * Parse the teams
         */
        $teamRecords = Reader::createFromPath("$srcDir/teams.csv")
            ->setHeaderOffset(0)
            ->getRecords();

        foreach ($teamRecords as $record) {
            $teamSpecialRules = [];
            $teamSpecialRulesType = null;
            preg_match_all('/(?P<type>all_of|one_of)=\[(?P<rules>.+)?]/', $record['Special Rules'], $matches, PREG_SET_ORDER);
            foreach($matches as $match) {
                $teamSpecialRulesType = $match['type'];
                $rules = array_filter(array_map('trim', explode(",", $match['rules'])));
                foreach ($rules as $rule) {
                    $teamSpecialRules[$rule] = $this->specialRules[$rule];
                }
            }

            $team = new Team(
                $record['Name'],
                (int)$record['Tier'],
                (int)$record['Team Re-rolls - Cost'],
                (bool)$record['Can Have Apothecary'],
                $record['Special Rules - Summary'],
                $teamSpecialRules,
                $teamSpecialRulesType,
                $record['Additional Rules'],
                $record['Rule Book Description'],
                $record['Source'],
                $record['Errata'] ?? '',
            );

            $this->teams[$team->name] = $team;

            foreach ($teamSpecialRules as $specialRule) {
                $specialRule->teams[$team->name] = $team;
            }
        }
        asort($this->teams);

        /**
         * Parse the player skills
         */
        $playerSkillRecords = Reader::createFromPath("$srcDir/player-skills.csv")
            ->setHeaderOffset(0)
            ->getRecords();

        foreach ($playerSkillRecords as $record) {
            $playerSkillCategory = match($record['Category']) {
                'Agility' => PlayerSkillCategory::AGILITY,
                'General' => PlayerSkillCategory::GENERAL,
                'Passing' => PlayerSkillCategory::PASSING,
                'Strength' => PlayerSkillCategory::STRENGTH,
                'Mutations' => PlayerSkillCategory::MUTATIONS,
                default => throw new \Exception("Unknown player skill category: {$record['Category']}"),
            };
            $playerSkill = new PlayerSkill(
                $record['First D6'],
                $record['Second D6'],
                $record['Name'],
                $playerSkillCategory,
                $record['Rule Book Description'],
                (bool)$record['Compulsory'],
                $record['Source'],
            );
            $this->playerSkills[$playerSkill->name] = $playerSkill;
        }

        /**
         * Parse the player traits
         */
        $playerTraitRecords = Reader::createFromPath("$srcDir/player-traits.csv")
            ->setHeaderOffset(0)
            ->getRecords();

        foreach ($playerTraitRecords as $record) {
            $playerTrait = new PlayerTrait(
                $record['Name'],
                $record['Rule Book Description'],
                (bool)$record['Compulsory'],
                $record['Source'],
            );
            $this->playerTraits[$playerTrait->name] = $playerTrait;
        }
        ksort($this->playerTraits);

        $parseSkillCategories = function ($categoryString) {
            $skills = [];
            if(!$categoryString)  {
                return $skills;
            }
            // Split the string into individual characters
            $categories = array_filter(str_split($categoryString));
            // Map each character to the corresponding skill
            foreach ($categories as $category) {
                // Convert the character to an enum
                $skills[] = PlayerSkillCategory::from($category);
            }
            return $skills;
        };

        $parseSkillsAndTraits = function (string $skillsAndTraits): array {

            $ret = [
                'skills' => [],
                'traits' => [],
            ];

            if(!$skillsAndTraits) {
                return $ret;
            }

            // Split the string into individual skills
            $skillsAndTraits = array_map('trim', explode(",", $skillsAndTraits));
            // Remove "None" from the list
            $skillsAndTraits = array_filter($skillsAndTraits, fn($skillOrTrait) => $skillOrTrait !== 'None');
            // Loop over each skill or trait and try and find it in the player skills or player traits

            $lookupMap = [
                "Mighty Blow" => "Mighty Blow (+X)",
                "Loner" => "Loner (X+)",
                "Animosity" => "Animosity (X)",
                "Dirty Player" => "Dirty Player (+X)",
                "Bloodlust" => "Bloodlust (X+)",
            ];

            foreach ($skillsAndTraits as $skillOrTrait) {
                $lookupKey = $skillOrTrait;
                foreach ($lookupMap as $key => $value) {
                    if (str_starts_with($skillOrTrait, $key)) {
                        $lookupKey = $value;
                    }
                }

                $skill = $this->playerSkills[$lookupKey] ?? null;
                if ($skill) {
                    $ret['skills'][$skillOrTrait] = $skill;
                    continue;
                }

                $trait = $this->playerTraits[$lookupKey] ?? null;
                if ($trait) {
                    $ret['traits'][$skillOrTrait] = $trait;
                    continue;
                }

                throw new \Exception("Unknown skill or trait: $lookupKey [$skillOrTrait]");
            }
            return $ret;
        };

        $playerPositionRecords = Reader::createFromPath("$srcDir/player-positions.csv")
            ->setHeaderOffset(0)
            ->getRecords();
        foreach ($playerPositionRecords as $record) {
            $team = $this->teams[$record['Team']];
            ['skills' => $skills, 'traits' => $traits] = $parseSkillsAndTraits($record['Skills and Traits']);
            $playerPosition = new PlayerPosition(
                $team,
                $record['Qty'],
                $record['Name'],
                (int)$record['Cost'],
                (int)$record['MA'],
                (int)$record['ST'],
                $record['AG'],
                $record['PA'],
                $record['AV'],
                $skills,
                $traits,
                $parseSkillCategories($record['Primary Skill Categories']),
                $parseSkillCategories($record['Secondary Skill Categories']),
            );
            foreach($playerPosition->skillsAndTraits() as $skillOrTrait) {
                $skillOrTrait->playerPositions[] = $playerPosition;
            }
            $team->playerPositions[] = $playerPosition;
        }

        $starPlayerPositionRecords = Reader::createFromPath("$srcDir/star-player-positions.csv")
            ->setHeaderOffset(0)
            ->getRecords()
        ;

        $starPlayerPositionRecords = iterator_to_array($starPlayerPositionRecords);
        $slugger = new AsciiSlugger();
        usort($starPlayerPositionRecords, fn($a, $b) => $slugger->slug($a['Name']) <=> $slugger->slug($b['Name']));

        foreach ($starPlayerPositionRecords as $record) {

            if($record['Enabled'] !== 'TRUE') {
                continue;
            }

            ['skills' => $skills, 'traits' => $traits] = $parseSkillsAndTraits($record['Skills and Traits']);
            $starPlayer = new StarPlayerPosition(
                '0-1',
                $record['Name'],
                (int)$record['Cost'],
                (int)$record['MA'],
                (int)$record['ST'],
                $record['AG'],
                $record['PA'],
                $record['AV'],
                $skills,
                $traits,
                $record['Special Rules'],
                $record['Plays For Rule Summary'],
                [],
                $record['Mega Star'] === 'TRUE',
                $record['Source'],
            );
            $this->starPlayerPositions[$starPlayer->name] = $starPlayer;
            foreach($starPlayer->skillsAndTraits() as $skillOrTrait) {
                $skillOrTrait->starPlayerPositions[] = $starPlayer;
            }

            $record['Plays For Rule'] = str_replace("All", join(",", array_keys($this->specialRules)), $record['Plays For Rule']);
            $playsFor = array_filter(array_map('trim', explode(",", $record['Plays For Rule'])));
            $teams = [];
            $specialRuleNames = [];
            foreach($playsFor as $specialRuleName) {
                // Custom rule for "not" … eg Morg'n'Thorg
                if(str_starts_with($specialRuleName, 'not ')) {
                    $specialRuleName = substr($specialRuleName, 4);
                    $specialRuleNames[] = $specialRuleName;
                    $teams = array_diff_key($teams, $this->specialRules[$specialRuleName]->teams);
                    continue;
                }
                $specialRuleNames[] = $specialRuleName;
                $specialRule = $this->specialRules[$specialRuleName];
                $specialRule->starPlayerPositions[] = $starPlayer;
                $teams = array_merge($teams, $specialRule->teams);
            }

            $starPlayer->teams = $teams;
            $starPlayer->playsFor = array_intersect_key($this->specialRules, array_flip($specialRuleNames));

            foreach($starPlayer->teams as $team) {
                $team->starPlayerPositions[] = $starPlayer;
            }
        }


        $designersCommentaryRecords = Reader::createFromPath("$srcDir/designers-commentary.csv")
            ->setHeaderOffset(0)
            ->getRecords()
        ;

        $designersCommentaryRecords = iterator_to_array($designersCommentaryRecords);
        foreach ($designersCommentaryRecords as $record) {
            foreach(explode(" & ", $record['Page Ref']) as $pageRef) {
                $designersCommentary = new DesignersCommentary(
                    $record['Source'],
                    $record['Document Ref'],
                    (int)$pageRef,
                    $record['Question'],
                    $record['Answer'],
                    $record['Team'],
                    $record['Star Player'],
                );
                $this->designersCommentary[] = $designersCommentary;
            }

        }

        $inducementRecords = Reader::createFromPath("$srcDir/inducements.csv")
            ->setHeaderOffset(0)
            ->getRecords()
        ;

        $inducementRecords = iterator_to_array($inducementRecords);
        foreach ($inducementRecords as $record) {
            $inducement = new Inducement(
                $record['Qty'],
                $record['Name'],
                $record['Cost'],
                $record['Rule Book Description - HTML'],
            );
            $this->inducements[$inducement->name] = $inducement;
        }

    }

    public function playerSkillsGroupedByCategory(): array
    {
        $skillsGroupedByCategory = [];
        foreach ($this->playerSkills as $skill) {
            if(!isset($skillsGroupedByCategory[$skill->category->name])) {
                $skillsGroupedByCategory[$skill->category->name] = [
                    'category' => $skill->category,
                    'playerSkills' => [],
                ];
            }
            $skillsGroupedByCategory[$skill->category->name]['playerSkills'][] = $skill;
        }
        return $skillsGroupedByCategory;
    }

    /**
     * Creates an array structure of player skills suitable for html
     * The table is first grouped by firstD6, then by secondD6
     * This follows the Blood Bowl 2020 random skill tables
     * The headers for the table are: 1st D6, 2nd D6, Agility, General, Mutations, Passing, Strength
     * The table is then sorted by the firstD6 and secondD6 values
     *
     * @return array
     */
    public function playerSkillsTable(): array {
        $skillsTable = [];
        foreach ($this->playerSkills as $skill) {
            if(!isset($skillsTable[$skill->firstD6])) {
                $skillsTable[$skill->firstD6] = [];
            }
            if(!isset($skillsTable[$skill->firstD6][$skill->secondD6])) {
                $skillsTable[$skill->firstD6][$skill->secondD6] = [
                    'A' => null,
                    'G' => null,
                    'M' => null,
                    'P' => null,
                    'S' => null,
                ];
            }
            $skillsTable[$skill->firstD6][$skill->secondD6][$skill->category->value] = $skill;
        }
        return $skillsTable;
    }

    public function playerSkillsAndTraits(): array {
        $skillsAndTraits = array_merge($this->playerSkills, $this->playerTraits);
        ksort($skillsAndTraits);
        return $skillsAndTraits;
    }
}