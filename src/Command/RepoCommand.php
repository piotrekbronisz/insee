<?php

namespace App\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RepoCommand extends Command
{
    protected $client;
    protected $container;
    protected static $defaultName = 'repo:last-commit-sha';

    public function __construct(HttpClientInterface $client, ContainerInterface $container)
    {
        parent::__construct();
        $this->client = $client;
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription("Getting last commit SHA")
            ->addOption(
                'service',
                null,
                InputOption::VALUE_OPTIONAL,
                "Enter the repository service name. Allow: github"
            )
            ->addOption(
                'branch',
                null,
                InputOption::VALUE_OPTIONAL,
                "Enter the branch name"
            )
            ->addOption(
                'repo',
                null,
                InputOption::VALUE_REQUIRED,
                "Enter the repository name. Example: owner/repository-name"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(empty($input->getOption("repo"))) {
            $output->writeln("Please enter the repository and owner name.");
            return Command::FAILURE;
        }

        $repo = [];
        if(!preg_match('/([A-z\d-]+)[\/]([A-z\d-]+)/', $input->getOption("repo"), $repo)) {
            $output->writeln("Invalid repository name! Example: owner/repository-name");
            return Command::FAILURE;
        }

        $service = "github";
        if(!empty($input->getOption("service"))) $service = $input->getOption("service");
        $branch = null;
        if(!empty($input->getOption("branch"))) $branch = $input->getOption("branch");

        if($branch != null && !preg_match('/(^[A-z\d-]+$)/', $input->getOption("branch"))) {
            $output->writeln("Invalid branch name!");
            return Command::FAILURE;
        }

        if($service == 'github') {
            $apiUrl = "https://api.github.com/repos/".$repo[1]."/".$repo[2]."/commits";
            if($branch != null) $apiUrl .= '/'.$branch;
            else $apiUrl .= '?per_page=1';

            $response = $this->client->request(
                "GET",
                $apiUrl
            );

            if($response->getStatusCode() != 200) {
                $output->writeln("An occurred unexpected error. Please try again.");
                return Command::FAILURE;
            }

            $json = json_decode($response->getContent(false), true);

            $sha = ($branch == null) ? reset($json)['sha'] : $json['sha'];

            $output->writeln("SHA value of the last commit: ".$sha);
            return Command::SUCCESS;
        } else {
            $output->writeln("Selected incorrect repository");
            return Command::FAILURE;
        }
    }
}