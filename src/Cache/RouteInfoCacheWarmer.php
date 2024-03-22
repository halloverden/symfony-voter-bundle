<?php

namespace HalloVerden\VoterBundle\Cache;

use HalloVerden\VoterBundle\Route\RouteInfoService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class RouteInfoCacheWarmer implements CacheWarmerInterface {

  /**
   * RouteCache constructor.
   */
  public function __construct(
    private RouteInfoService $routeInfoService
  ) {
  }

  /**
   * @inheritDoc
   */
  public function isOptional(): bool {
    return true;
  }

  /**
   * @inheritDoc
   * @throws InvalidArgumentException
   */
  public function warmUp(string $cacheDir, ?string $buildDir = null): array {
    $this->routeInfoService->getRouteInfoCollection();
    return [];
  }

}
