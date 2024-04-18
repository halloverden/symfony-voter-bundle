<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\JwtAuthenticatorBundle\Security\JwtPostAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class OauthAuthorizationVoter
 *
 * @package HalloVerden\VoterBundle\Security\Voter
 */
class OauthAuthorizationVoter extends BaseVoter {
  const OAUTH_SCOPE = 'oauth.scope';

  /**
   * @return string[]
   */
  protected function getSupportedAttributes(): array {
    return [self::OAUTH_SCOPE];
  }

  /**
   * @return string[]
   */
  protected function getSupportedClasses(): array {
    return ['string'];
  }

  /**
   * @inheritDoc
   */
  protected function voteOnAttribute(string $attribute, mixed $subjects, TokenInterface $token): bool {
    switch ($attribute) {
      case self::OAUTH_SCOPE:
        return $this->hasOauthScope($token, $this->sortSubjects($subjects, ['string'], false));
    }

    throw new \LogicException('This code should not be reached!');
  }

  /**
   * @param TokenInterface $token
   * @param array          $scopes
   *
   * @return bool
   */
  private function hasOauthScope(TokenInterface $token, array $scopes): bool {
    if (!$token instanceof JwtPostAuthenticationToken) {
      return false;
    }

    $scopeClaim = $token->getJwt()->getClaim('scope');
    if (!\is_string($scopeClaim)) {
      return false;
    }

    $accessTokenScopes = explode(' ', $scopeClaim);

    foreach ($scopes as $scope) {
      if (in_array($scope, $accessTokenScopes)) {
        return true;
      }
    }

    return false;
  }
}
