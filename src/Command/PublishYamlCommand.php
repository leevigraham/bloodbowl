<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider;
use App\Dto\PlayerPosition;
use App\Dto\PlayerSkill;
use App\Dto\PlayerTrait;
use App\Dto\SpecialRule;
use App\Dto\StarPlayerPosition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'app:publish:yaml', description: 'Publishes static pages')]
class PublishYamlCommand extends Command
{
    public function __construct(
        private DataProvider $dataProvider
    )
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $teams = array_map(fn($team) => [
            'name' => $team->name,
            'description' => $team->ruleBookDescription,
            'tier' => $team->tier,
            'reRollCost' => $team->reRollCost,
            'canHaveApothecary' => $team->canHaveApothecary,
            'specialRulesSummary' => $team->specialRulesSummary,
            'specialRulesType' => $team->specialRulesType,
            'specialRules' => array_keys($team->specialRules),
            'additionalRules' => $team->additionalRules,
            'source' => $team->source,
            'playerPositions' => array_values(array_map(fn(PlayerPosition $position) => [
                'name' => $position->name,
                'qty' => $position->qty,
                'MA' => $position->MA,
                'ST' => $position->ST,
                'AG' => $position->AG,
                'AV' => $position->AV,
                'skills' => array_keys($position->skills),
                'traits' => array_keys($position->traits),
            ], $team->playerPositions)),
        ], $this->dataProvider->teams);

        $starPlayers = array_map(fn(StarPlayerPosition $starPlayer) => [
            'name' => $starPlayer->name,
            'cost' => $starPlayer->cost,
            'MA' => $starPlayer->MA,
            'ST' => $starPlayer->ST,
            'AG' => $starPlayer->AG,
            'PA' => $starPlayer->PA,
            'AV' => $starPlayer->AV,
            'skills' => array_keys($starPlayer->skills),
            'traits' => array_keys($starPlayer->traits),
            'specialRules' => $starPlayer->specialRules,
            'playsForRuleSummary' => $starPlayer->playsForRule,
            'playsFor' => array_keys($starPlayer->playsFor),
            'megaStar' => $starPlayer->megaStar,
            'source' => $starPlayer->source,
        ], $this->dataProvider->starPlayerPositions);

        $skills = array_map(fn(PlayerSkill $skill) => [
            'name' => $skill->name,
            'firstD6' => $skill->firstD6,
            'secondD6' => $skill->secondD6,
            'category' => $skill->category->value,
            'compulsory' => $skill->compulsory,
            'ruleBookDescription' => $skill->ruleBookDescription,
        ], $this->dataProvider->playerSkills);

        $traits = array_map(fn(PlayerTrait $trait) => [
            'name' => $trait->name,
            'compulsory' => $trait->compulsory,
            'ruleBookDescription' => $trait->ruleBookDescription,
        ], $this->dataProvider->playerTraits);

        $specialRules = array_map(fn(SpecialRule $rule) => [
            'name' => $rule->name,
            'category' => $rule->category->value,
            'ruleBookDescription' => $rule->ruleBookDescription,
        ], $this->dataProvider->specialRules);

        $yaml = Yaml::dump([
            'teams' => $teams,
            'starPlayers' => $starPlayers,
            'skills' => $skills,
            'traits' => $traits,
            'specialRules' => $specialRules,
        ], 5, flags: Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE|Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $filePath = dirname(__DIR__, 2) . '/data/yaml/data.yaml';
        if (false === file_put_contents($filePath, $yaml)) {
            $io->error('Failed to write the yaml content to the file.');
            return Command::FAILURE;
        }

        $io->success("HTML content saved successfully to $filePath");
        return Command::SUCCESS;

    }
}
