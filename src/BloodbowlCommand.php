<?php

declare(strict_types=1);

namespace App;

use App\Dto\PlayerPosition;
use App\Dto\PlayerSkill;
use App\Dto\PlayerTrait;
use App\Dto\SpecialRule;
use App\Dto\StarPlayerPosition;
use App\Dto\Team;
use App\Enum\PlayerSkillCategory;
use App\Enum\SpecialRuleCategory;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(name: 'bb', description: 'Hello Sports Fans')]
class BloodbowlCommand extends Command
{


    public function __construct(
        private readonly Environment $twig,
        private DataProvider $csvImporter
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->csvImporter->load();

        $html = $this->twig->render('output.html.twig', $data);

        file_put_contents('output.html', $html);

        return Command::SUCCESS;
    }
}
