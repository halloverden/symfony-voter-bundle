<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\Security\SecurityInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EndpointScopeVoter extends BaseVoter {
  const ENDPOINT_SCOPE = 'endpoint_scope';

  /**
   * EndpointScopeVoter constructor.
   */
  public function __construct(
    SecurityInterface $security,
    private readonly RequestStack $requestStack,
    private readonly RouterInterface $router,
    private readonly ?string $scopePrefix = null,
  ) {
    parent::__construct($security);
  }


  /**
   * @return string[]
   */
  protected function getSupportedAttributes(): array {
    return [static::ENDPOINT_SCOPE];
  }

  /**
   * @return string[]
   */
  protected function getSupportedClasses(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
    switch ($attribute) {
      case self::ENDPOINT_SCOPE:
        return $this->hasEndpointScope();
    }

    throw new \LogicException('This code should not be reached!');
  }

  /**
   * @return bool
   */
  private function hasEndpointScope(): bool {
    $request = $this->requestStack->getCurrentRequest();
    if (null === $request) {
      return false;
    }

    $route = $this->getRoute($request);
    if (null === $route) {
      return false;
    }

    return $this->security->isGranted(OauthAuthorizationVoter::OAUTH_SCOPE, [$this->createScopeName($request, $route)]);
  }

  /**
   * @param Request $request
   *
   * @return Route|null
   */
  private function getRoute(Request $request): ?Route {
    $routeName = $request->attributes->get('_route');
    if (null === $routeName) {
      return null;
    }

    return $this->router->getRouteCollection()->get($routeName);
  }

  /**
   * @param Request $request
   * @param Route   $route
   *
   * @return string
   */
  protected function createScopeName(Request $request, Route $route): string {
    return \sprintf('%s%s:%s', $this->scopePrefix, \strtolower($request->getMethod()), $route->getPath());
  }

}
