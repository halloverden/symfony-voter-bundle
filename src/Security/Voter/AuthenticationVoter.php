<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EventListener\SetAuthenticatorOnLoginListener;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class AuthenticationVoter extends Voter {

  /**
   * @deprecated use AuthenticationVoter::class instead
   */
  public const AUTHENTICATOR = 'authenticator';

  /**
   * @inheritDoc
   */
  public function supportsAttribute(string $attribute): bool {
    if (self::AUTHENTICATOR === $attribute) {
      trigger_deprecation('halloverden/symfony-voter-bundle', '3.0', 'AUTHENTICATOR constant is deprecated, use AuthenticationVoter::class instead');
      return true;
    }

    return parent::supportsAttribute($attribute);
  }

  /**
   * @inheritDoc
   */
  protected function supportsSubject(int|string $key, mixed $subject, string $attribute): bool {
    return \is_string($subject);
  }

  /**
   * @inheritDoc
   */
  protected function doVote(string $attribute, array $subjects, TokenInterface $token): bool {
    if (!$token->hasAttribute(SetAuthenticatorOnLoginListener::TOKEN_PARAMETER_AUTHENTICATORS)) {
      return false;
    }

    $authenticators = $token->getAttribute(SetAuthenticatorOnLoginListener::TOKEN_PARAMETER_AUTHENTICATORS);

    foreach ($subjects as $subject) {
      if (\in_array($subject, $authenticators)) {
        return true;
      }
    }

    return false;
  }

}
