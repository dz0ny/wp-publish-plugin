<?php

namespace WPAPlugin\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('release')
            ->setDescription('Publish plugin to remote repo')
            ->addArgument(
                'repo_url',
                InputArgument::REQUIRED,
                'Target repo url'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('repo_url');
        $output->writeln($name);
    }
}