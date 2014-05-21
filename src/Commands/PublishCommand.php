<?php

    namespace WPAPlugin\Commands;

    use Guzzle\Http\Exception\ClientErrorResponseException;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use OpenCloud\Rackspace;
    use WPAPlugin\Utils\WPRUSHProject;

    class PublishCommand extends Command
    {
        protected function configure()
        {
            $this
                ->setName('publish')
                ->setDescription('Publish plugin to remote repo')
                ->addArgument(
                    'repo_url',
                    InputArgument::REQUIRED,
                    'Target repo url rackspace://username:apikey@RACKSPACE_UK/container'
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $uri = parse_url($input->getArgument('repo_url'));

            if($uri['scheme'] == 'rackspace'){
                $client = new Rackspace(constant($uri['host']), array(
                    'username' => $uri['user'],
                    'apiKey' => $uri['pass']
                ));
                $name = substr($uri['path'],1);
                $service = $client->objectStoreService('cloudFiles', 'DFW');

                if (!empty($name)) {
                    try{
                        $container = $service->getContainer($name);
                    }catch (ClientErrorResponseException $e){
                        $container = $service->createContainer($name);
                        $container->enableCdn();
                    }
                    $project = new WPRUSHProject();
                    $project->setManifestURL($container->getCdn()->getCdnSslUri().'/manifest.json');
                    $project->setVersion(getcwd().DIRECTORY_SEPARATOR.'release'.DIRECTORY_SEPARATOR.'plugin');
                    $container->uploadDirectory(getcwd().DIRECTORY_SEPARATOR.'release');
                }
            }else{
                $output->writeln('Unsupported CDN provider');
            }

        }
    }