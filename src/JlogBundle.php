<?php

namespace Johndodev\JlogBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class JlogBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
//        $container->parameters()->set('jlog.project_api_key', $config['project_api_key']);

        $container->import('../config/services.yaml');
    }
}
