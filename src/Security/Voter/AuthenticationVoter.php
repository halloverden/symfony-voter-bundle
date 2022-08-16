<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EventListener\SetAuthenticatorOnLoginListener;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuthenticationVoter extends Voter {
  const AUTHENTICATOR = 'authenticator';

  /**
   * @inheritDoc
   */
  protected function supports(string $attribute, mixed $subjects): bool {
    if ($attribute !== self::AUTHENTICATOR) {
      return false;
    }

    if (!is_array($subjects)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  protected function voteOnAttribute(string $attribute, mixed $subjects, TokenInterface $token): bool {
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
