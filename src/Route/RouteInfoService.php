<?php

namespace HalloVerden\VoterBundle\Route;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly final class RouteInfoService {
  private const CACHE_KEY = '_hallo_verden.symfony_voter_bundle.route_info';

  /**
   * RouteInfoService constructor.
   */
  public function __construct(
    private RouterInterface $router,
    private CacheInterface  $cache
  ) {
  }

  /**
   * @param Request $request
   *
   * @return RouteInfo
   * @throws InvalidArgumentException
   */
  public function getRouteInfo(Request $request): RouteInfo {
    return $this->getRouteInfoCollection()[$request->attributes->get('_route')];
  }

  /**
   * @return array<string, RouteInfo>
   * @throws InvalidArgumentException
   */
  public function getRouteInfoCollection(): array {
    return $this->cache->get(self::CACHE_KEY, function (): array {
      $routeInfoCollection = [];

      foreach ($this->router->getRouteCollection() as $name => $route) {
        $routeInfoCollection[$name] = new RouteInfo($route->getPath());
      }

      return $routeInfoCollection;
    });
  }

}
