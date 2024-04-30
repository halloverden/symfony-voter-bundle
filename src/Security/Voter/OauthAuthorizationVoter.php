<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\JwtAuthenticatorBundle\Security\JwtPostAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


final class OauthAuthorizationVoter extends Voter {

  /**
   * @deprecated use OauthAuthorizationVoter::class instead
   */
  public const OAUTH_SCOPE = 'oauth.scope';

  /**
   * @inheritDoc
   */
  public function supportsAttribute(string $attribute): bool {
    if (self::OAUTH_SCOPE === $attribute) {
      trigger_deprecation('halloverden/symfony-voter-bundle', '3.0', 'OAUTH_SCOPE constant is deprecated, use OauthAuthorizationVoter::class instead');
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
    return $this->hasOauthScope($token, $subjects);
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
