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
  }

}
