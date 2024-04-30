<?php

namespace HalloVerden\VoterBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class HalloVerdenVoterBundle extends AbstractBundle {

  public function configure(DefinitionConfigurator $definition): void {
    $definition->rootNode()
      ->addDefaultsIfNotSet()
      ->children()
        ->arrayNode('admin_roles')
          ->defaultValue([])
          ->scalarPrototype()->end()
        ->end()
        ->arrayNode('endpoint_scope_voter')
          ->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('scope_prefix')->defaultNull()->end()
          ->end()
        ->end()
      ->end();
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void {
    $container->import('../config/services.yaml');

    if (!empty($config['admin_roles'])) {
      $container->services()
        ->get('hallo_verden_voter.voter')
          ->call('setAdminRoles', [$config['admin_roles']])
        ->get('hallo_verden_voter.security')
          ->arg('$adminRoles', $config['admin_roles']);
    }

    if (isset($config['endpoint_scope_voter']['scope_prefix'])) {
      $container->services()
        ->get('hallo_verden_voter.endpoint_scope_voter')
        ->arg('$scopePrefix', $config['endpoint_scope_voter']['scope_prefix']);
    }
  }

}
