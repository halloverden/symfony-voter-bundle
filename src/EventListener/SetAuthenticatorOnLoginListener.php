<?php

namespace HalloVerden\VoterBundle\EventListener;

use HalloVerden\SymfonyAuthenticatorRestrictorBundle\Interfaces\NamedAuthenticatorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Debug\TraceableAuthenticator;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * @final
 */
class SetAuthenticatorOnLoginListener implements EventSubscriberInterface, LoggerAwareInterface {
  use LoggerAwareTrait;

  const TOKEN_PARAMETER_AUTHENTICATORS = '__hallo_verden_voters.authenticators';

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      LoginSuccessEvent::class => 'onLoginSuccess'
    ];
  }

  /**
   * @param LoginSuccessEvent $event
   *
   * @return void
   */
  public function onLoginSuccess(LoginSuccessEvent $event): void {
    $token = $event->getAuthenticatedToken();
    $authenticators = $token->hasAttribute(self::TOKEN_PARAMETER_AUTHENTICATORS) ? $token->getAttribute(self::TOKEN_PARAMETER_AUTHENTICATORS) : [];
    $authenticators[] = $this->getAuthenticatorName($event->getAuthenticator());
    $token->setAttribute(self::TOKEN_PARAMETER_AUTHENTICATORS, $authenticators);
  }

  /**
   * @param AuthenticatorInterface $authenticator
   *
   * @return string
   */
  private function getAuthenticatorName(AuthenticatorInterface $authenticator): string {
    $authenticator = $this->getActualAuthenticator($authenticator);

    if (\interface_exists(NamedAuthenticatorInterface::class) && $authenticator instanceof NamedAuthenticatorInterface) {
      return $authenticator->getAuthenticatorName();
    }

    return \get_class($authenticator);
  }

  /**
   * The authenticator is sometimes wrapped in a TraceableAuthenticator, we need to have the actual authenticator.
   *   Since the getAuthenticator method in TraceableAuthenticator is marked internal we takes some steps to make sure nothing breaks.
   *
   * @param AuthenticatorInterface $authenticator
   *
   * @return AuthenticatorInterface
   */
  private function getActualAuthenticator(AuthenticatorInterface $authenticator): AuthenticatorInterface {
    if (!$authenticator instanceof TraceableAuthenticator) {
      return $authenticator;
    }

    if (\method_exists($authenticator, 'getAuthenticator')) {
      return $authenticator->getAuthenticator();
    }

    $this->logger?->warning(\sprintf('%s is no longer implementing the getAuthenticator method, trying to use reflection', TraceableAuthenticator::class));

    try {
      return (new \ReflectionProperty($authenticator, 'authenticator'))->getValue($authenticator);
    } catch (\ReflectionException) {
      $this->logger?->warning(\sprintf('%s does not have a property "authenticator". Unable to get actual authenticator', TraceableAuthenticator::class));
      return $authenticator;
    }

  }

}
