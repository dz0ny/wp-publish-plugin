<?php

namespace WPAPlugin\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPAPlugin\Utils\WordpressServer;

class RunServerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('runserver')
            ->setDescription('Runs a lightweight built in http server for development.')
            ->addArgument(
                'port',
                InputArgument::OPTIONAL,
                'Server port',
                8000
            )->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to wordpress root (defaults to $cwd/web)',
                getcwd().'/web'
            )->addArgument(
                'debug',
                InputArgument::OPTIONAL,
                'Display more debugging information',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $port = $input->getArgument('port');
        $debug = $input->getArgument('debug');
        $server = new WordpressServer($path, $output, $port, $debug);
        $server->run_forever();
    }
}