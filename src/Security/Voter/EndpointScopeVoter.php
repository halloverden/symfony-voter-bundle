<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EndpointScope\EndpointScopeContext;
use HalloVerden\VoterBundle\Route\RouteInfoService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class EndpointScopeVoter extends Voter {

  /**
   * @deprecated use EndpointScopeVoter::class instead
   */
  public const ENDPOINT_SCOPE = 'endpoint_scope';

  /**
   * @inheritDoc
   */
  public function __construct(
    Security                          $security,
    AccessDecisionManagerInterface    $accessDecisionManager,
    private readonly RequestStack     $requestStack,
    private readonly RouteInfoService $routeInfoService,
    private readonly ?string          $scopePrefix = null
  ) {
    parent::__construct($security, $accessDecisionManager);
  }

  /**
   * @inheritDoc
   */
  public function supportsAttribute(string $attribute): bool {
    if (self::ENDPOINT_SCOPE === $attribute) {
      trigger_deprecation('halloverden/symfony-voter-bundle', '3.0', 'ENDPOINT_SCOPE constant is deprecated, use EndpointScopeVoter::class instead');
      return true;
    }

    return parent::supportsAttribute($attribute);
  }

  /**
   * @inheritDoc
   */
  protected function supportsSubject(int|string $key, mixed $subject, string $attribute): bool {
    return match ($key) {
      0 => $subject instanceof EndpointScopeContext
    };
  }

  /**
   * @inheritDoc
   * @throws InvalidArgumentException
   */
  protected function doVote(string $attribute, array $subjects, TokenInterface $token): bool {
    return $this->checkEndpointScope($token, $subjects[0] ?? null);
  }

  /**
   * @param TokenInterface            $token
   * @param EndpointScopeContext|null $context
   *
   * @return bool
   * @throws InvalidArgumentException
   */
  private function checkEndpointScope(TokenInterface $token, ?EndpointScopeContext $context = null): bool {
    $request = $this->requestStack->getCurrentRequest();
    if (null === $request) {
      return false;
    }

    $scopeName = $this->createScopeName($request, $context);
    if (!$this->accessDecisionManager->decide($token, [OauthAuthorizationVoter::class], [$scopeName])) {
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
      $context?->getScopePrefix() ?? $this->scopePrefix,
      \strtolower($request->getMethod()),
      $this->routeInfoService->getRouteInfo($request)->getPath(),
      $context?->getScopeSuffix()
    );
  }

}
