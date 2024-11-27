<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(name: 'app:publish', description: 'Publishes static pages')]
class PublishCommand extends Command
{
    public function __construct(
        private HttpKernelInterface $httpKernel,
        private RouterInterface $router)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $src = dirname(__DIR__, 2) . '/public/images/*.{png,jpg}';
        $dest = dirname(__DIR__, 2) . '/docs/images/';
        shell_exec("cp -r $src $dest");

        foreach([
            'index' => 'index.html',
            'reference' => 'reference.html',
            'rule-book' => 'rule-book.html',
        ] as $route => $fileName) {
            // Generate the route URL
            $routeUrl = $this->router->generate($route, [], RouterInterface::ABSOLUTE_URL);

            // Create a sub-request
            $request = Request::create($routeUrl);

            // Handle the sub-request
            $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

            // Check if the response is successful
            if (!$response->isSuccessful()) {
                $io->error('Failed to fetch the HTML content from the route.');
                return Command::FAILURE;
            }

            // Save the HTML content to a file
            $htmlContent = $response->getContent();

            $filePath = dirname(__DIR__, 2) . '/docs/' . $fileName;
            if (false === file_put_contents($filePath, $htmlContent)) {
                $io->error('Failed to write the HTML content to the file.');
                return Command::FAILURE;
            }

            $io->success("HTML content saved successfully to $filePath");
        }

        return Command::SUCCESS;
    }
}
