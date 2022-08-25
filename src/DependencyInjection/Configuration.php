<?php

namespace HalloVerden\VoterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

  /**
   * @inheritDoc
   */
  public function getConfigTreeBuilder(): TreeBuilder {
    $treeBuilder = new TreeBuilder('hallo_verden_voter');

    $treeBuilder->getRootNode()
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

    return $treeBuilder;
  }

}
