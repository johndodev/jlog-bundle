<?php

namespace Johndodev\JlogBundle;

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
//        $container->parameters()->set('jlog.project_api_key', $config['project_api_key']);

        $container->services()->set('jlog.monolog_handler')
            ->class(JlogHandler::class)
            ->args([
                '$projectApiKey' => $config['project_api_key'],
                '$httpClient' => service(HttpClientInterface::class),
                '$endpoint' => $config['endpoint'],
            ])
        ;

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

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children() // jlog
                ->scalarNode('project_api_key')->isRequired()->end()
                ->scalarNode('endpoint')->defaultNull()->end()
                ->booleanNode('enable_exception_listener')->defaultTrue()->end()
                ->arrayNode('ignore_exceptions')->scalarPrototype()->end()
            ->end()
        ;
    }
}
