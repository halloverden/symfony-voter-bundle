<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EndpointScope\EndpointScopeContext;
use HalloVerden\VoterBundle\Route\RouteInfoService;
use HalloVerden\VoterBundle\Security\SecurityInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class EndpointScopeVoter extends BaseVoter {
  const ENDPOINT_SCOPE = 'endpoint_scope';

  /**
   * EndpointScopeVoter constructor.
   */
  public function __construct(
    SecurityInterface                 $security,
    private readonly RequestStack     $requestStack,
    private readonly RouteInfoService $routeInfoService,
    private readonly ?string          $scopePrefix = null,
  ) {
    parent::__construct($security);
  }


  /**
   * @return string[]
   */
  protected function getSupportedAttributes(): array {
    return [self::ENDPOINT_SCOPE];
  }

  /**
   * @return string[]
   */
  protected function getSupportedClasses(): array {
    return [EndpointScopeContext::class];
  }

  /**
   * @inheritDoc
   * @throws InvalidArgumentException
   */
  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
    switch ($attribute) {
      case self::ENDPOINT_SCOPE:
        return $this->hasEndpointScope(...$this->sortSubjects($subject, [EndpointScopeContext::class], false));
    }

    throw new \LogicException('This code should not be reached!');
  }

  /**
   * @param EndpointScopeContext|null $context
   *
   * @return bool
   * @throws InvalidArgumentException
   */
  private function hasEndpointScope(?EndpointScopeContext $context = null): bool {
    $request = $this->requestStack->getCurrentRequest();
    if (null === $request) {
      return false;
    }

    $scopeName = $this->createScopeName($request, $context);
    if (!$this->security->isGranted(OauthAuthorizationVoter::OAUTH_SCOPE, [$scopeName])) {
      return false;
    }

    return true;
  }

  /**
   * @param Request                   $request
   * @param EndpointScopeContext|null $context
   *
   * @return string
   * @throws InvalidArgumentException
   */
  private function createScopeName(Request $request, ?EndpointScopeContext $context = null): string {
    return \sprintf(
      '%s%s:%s%s',
        $context->getScopePrefix() ?? $this->scopePrefix,
      \strtolower($request->getMethod()),
      $this->routeInfoService->getRouteInfo($request)->getPath(),
      $context->getScopeSuffix()
    );
  }

}
