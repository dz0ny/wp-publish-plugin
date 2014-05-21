<?php

namespace WPAPlugin;

use Symfony\Component\Console\Application as ConsoleApplication;
use WPAPlugin\Commands\ReleaseCommand;
use WPAPlugin\Commands\PublishCommand;
use WPAPlugin\Commands\RunServerCommand;

/**
 * Class Application
 * @package WPAPlugin
 */
class Application
{

    /**
     * @var ConsoleApplication
     */
    private $application;

    public function boot()
    {
        $this->application = new ConsoleApplication('wprush', '1.0');
        $this->addCommands();

    }

    public function run()
    {
        $this->application->run();
    }

    protected function addCommands()
    {
        $this->application->add(new PublishCommand);
        $this->application->add(new ReleaseCommand);
        $this->application->add(new RunServerCommand);
    }
}