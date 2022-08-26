<?php

namespace HalloVerden\VoterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class HalloVerdenVoterExtension extends ConfigurableExtension {

  /**
   * @inheritDoc
   * @throws \Exception
   */
  protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__. '/../../config'));
    $loader->load('services.yaml');

    if (!empty($mergedConfig['admin_roles'])) {
      $container->getDefinition('hallo_verden_voter.security')->setArgument('$adminRoles', $mergedConfig['admin_roles']);
    }

    $this->configureEndpointScopeVoter($mergedConfig['endpoint_scope_voter'], $container);
  }

  /**
   * @param array            $config
   * @param ContainerBuilder $container
   *
   * @return void
   */
  private function configureEndpointScopeVoter(array $config, ContainerBuilder $container): void {
    if (null === ($config['scope_prefix'] ?? null)) {
      return;
    }

    $container->getDefinition('hallo_verden_voter.endpoint_scope_voter')
      ->setArgument('$scopePrefix', $config['scope_prefix']);
  }

}
