<?php

namespace Johndodev\JlogBundle;

use Johndodev\JlogBundle\Command\TestLogCommand;
use Johndodev\JlogBundle\Console\ConsoleEventListener;
use Johndodev\JlogBundle\EventListener\ExceptionListener;
use Johndodev\JlogBundle\Monolog\JlogHandler;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class JlogBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (empty($config['project_api_key'])) {
            return;
        }

        $container->services()->set('jlog.monolog_handler')
            ->class(JlogHandler::class)
            ->args([
                '$projectApiKey' => $config['project_api_key'],
                '$httpClient' => service(HttpClientInterface::class),
                '$endpoint' => $config['endpoint'],
            ])
        ;

        $container->services()->set('jlog.console_listener')
            ->class(ConsoleEventListener::class)
            ->args([
                '$logger' => service('monolog.logger.'.$config['console_channel']),
                '$stopwatch' => service('debug.stopwatch'),
            ])
            ->tag('kernel.event_listener', ['event' => 'console.command', 'method' => 'onCommandStart'])
            ->tag('kernel.event_listener', ['event' => 'console.error', 'method' => 'onCommandError'])
            ->tag('kernel.event_listener', ['event' => 'console.terminate', 'method' => 'onCommandTerminate'])
        ;

        // enregistre la commande test
        $container->services()->set('jlog.command.test_log')
            ->class(TestLogCommand::class)
            ->args([
                '$logger' => service('monolog.logger'),
            ])
            ->tag('console.command');

        if ($config['enable_exception_listener']) {
            $container->services()->set('jlog.exception_listener')
                ->class(ExceptionListener::class)
                ->args([
                    '$logger' => service('monolog.logger'),
                    '$ignoreExceptions' => $config['ignore_exceptions'],
                ])
                ->tag('kernel.event_listener')
            ;
        }
    }

    /**
     * https://symfony.com/doc/current/components/config/definition.html
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children() // jlog
                ->scalarNode('project_api_key')->isRequired()->end()
                ->scalarNode('endpoint')->defaultNull()->end()
                ->scalarNode('console_channel')->defaultValue('console')->end()
                ->booleanNode('enable_exception_listener')->defaultTrue()->end()
                ->arrayNode('ignore_exceptions')->scalarPrototype()->end()
            ->end()
        ;
    }
}
