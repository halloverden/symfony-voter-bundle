<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EndpointScope\EndpointScopeQueryParamContext;
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
    return [EndpointScopeQueryParamContext::class];
  }

  /**
   * @inheritDoc
   */
  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
    switch ($attribute) {
      case self::ENDPOINT_SCOPE:
        return $this->hasEndpointScope($this->sortSubjects(\is_array($subject) ? $subject : [], [EndpointScopeQueryParamContext::class], strictLength: false));
    }

    throw new \LogicException('This code should not be reached!');
  }

  /**
   * @param EndpointScopeQueryParamContext[] $endpointScopeQueryParamContexts
   *
   * @return bool
   */
  private function hasEndpointScope(array $endpointScopeQueryParamContexts): bool {
    $request = $this->requestStack->getCurrentRequest();
    if (null === $request) {
      return false;
    }

    $route = $this->getRoute($request);
    if (null === $route) {
      return false;
    }

    $scopeName = $this->createScopeName($request, $route);
    if (!$this->security->isGranted(OauthAuthorizationVoter::OAUTH_SCOPE, [$scopeName])) {
      return false;
    }

    foreach ($endpointScopeQueryParamContexts as $endpointScopeQueryParamContext) {
      if (!$this->isGrantedAccessToQueryParam($endpointScopeQueryParamContext, $request, $scopeName)) {
        return false;
      }
    }

    return true;
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

  /**
   * @param EndpointScopeQueryParamContext $endpointScopeQueryParamContext
   * @param Request                        $request
   * @param string                         $scopeName
   *
   * @return bool
   */
  private function isGrantedAccessToQueryParam(EndpointScopeQueryParamContext $endpointScopeQueryParamContext, Request $request, string $scopeName): bool {
    $queryParam = $endpointScopeQueryParamContext->getQueryParam();
    if (!$request->query->has($queryParam)) {
      return true;
    }

    $value = $endpointScopeQueryParamContext->getValueExtractor()($request->query->get($queryParam));
    if (!$value) {
      return true;
    }

    $scopeName .= \sprintf('?%s=%s', $queryParam, $value);
    return $this->security->isGranted(OauthAuthorizationVoter::OAUTH_SCOPE, [$scopeName]);
  }

}
