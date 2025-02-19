<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use HalloVerden\VoterBundle\EndpointScope\EndpointScopeContext;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SymfonyVoter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

abstract class Voter extends SymfonyVoter {

  /**
   * @var string[]
   */
  protected array $adminRoles = [];

  /**
   * AbstractEndpointVoter constructor.
   */
  public function __construct(
    protected readonly Security                       $security,
    protected readonly AccessDecisionManagerInterface $accessDecisionManager,
  ) {
  }

  public final function vote(TokenInterface $token, mixed $subject, array $attributes): int {
    return parent::vote($token, $subject, $attributes);
  }

  /**
   * @inheritDoc
   */
  public function supportsAttribute(string $attribute): bool {
    return $attribute === static::class;
  }

  /**
   * @inheritDoc
   */
  public final function supportsType(string $subjectType): bool {
    return $subjectType === 'array' || $subjectType === 'null';
  }

  /**
   * @param string[] $adminRoles
   *
   * @return void
   */
  public final function setAdminRoles(array $adminRoles): void {
    $this->adminRoles = $adminRoles;
  }

  /**
   * @return array<string|int>
   */
  protected function getMandatorySubjectKeys(string $attribute): array {
    return [];
  }

  /**
   * @param string|int $key
   * @param mixed      $subject
   * @param string     $attribute
   *
   * @return bool
   */
  protected function supportsSubject(string|int $key, mixed $subject, string $attribute): bool {
    return false;
  }

  /**
   * @inheritDoc
   */
  protected final function supports(string $attribute, mixed $subject): bool {
    return $this->supportsAttribute($attribute)
      && (
        (null === $subject && empty($this->getMandatorySubjectKeys($attribute)))
        || (\is_array($subject) && $this->supportsSubjects($attribute, $subject))
      );
  }

  /**
   * @inheritDoc
   */
  protected final function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
    return $this->doVote($attribute, $subject ?? [], $token);
  }

  /**
   * @param string         $attribute
   * @param array          $subjects
   * @param TokenInterface $token
   *
   * @return bool
   */
  protected abstract function doVote(string $attribute, array $subjects, TokenInterface $token): bool;

  /**
   * @param string              $scope
   * @param TokenInterface|null $token
   *
   * @return bool
   */
  protected final function hasScope(string $scope, ?TokenInterface $token = null): bool {
    return $this->isGranted(OauthAuthorizationVoter::class, [$scope], $token);
  }

  /**
   * @param TokenInterface|null       $token
   * @param EndpointScopeContext|null $context
   *
   * @return bool
   */
  protected final function hasEndpointScope(TokenInterface $token = null, EndpointScopeContext $context = null): bool {
    return $this->isGranted(EndpointScopeVoter::class, null !== $context ? [$context] : null, $token);
  }

  /**
   * @param string              $authenticator
   * @param TokenInterface|null $token
   *
   * @return bool
   */
  protected final function isAuthenticatedWithAuthenticator(string $authenticator, ?TokenInterface $token = null): bool {
    return $this->isGranted(AuthenticationVoter::class, [$authenticator], $token);
  }

  /**
   * @param UserInterface $user
   * @param string        $attribute
   * @param mixed         $subject
   *
   * @return bool
   */
  protected final function isGrantedOnUser(UserInterface $user, string $attribute, mixed $subject = null): bool {
    $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
    return $this->isGranted($attribute, $subject, $token);
  }

  /**
   * @param UserInterface|null  $user
   * @param TokenInterface|null $token
   *
   * @return bool
   */
  protected final function isGrantedAnyAdmin(?UserInterface $user = null, ?TokenInterface $token = null): bool {
    if (null !== $user) {
      $token = new PostAuthenticationToken($user, 'main', $user->getRoles());
    }

    foreach ($this->adminRoles as $adminRole) {
      if ($this->isGranted($adminRole, null, $token)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string                            $attribute
   * @param mixed|null                        $subject
   * @param UserInterface|TokenInterface|null $token
   *
   * @return bool
   */
  protected final function isGranted(string $attribute, mixed $subject = null, UserInterface|TokenInterface|null $token = null): bool {
    if ($token instanceof TokenInterface) {
      return $this->accessDecisionManager->decide($token, [$attribute], $subject);
    }

    if ($token instanceof UserInterface) {
      trigger_deprecation('halloverden/symfony-voter-bundle', '3.0.0', 'Providing a user to the "%s()" method is deprecated use %s::isGrantedOnUser() instead.', __METHOD__, self::class);
      return $this->isGrantedOnUser($token, $attribute, $subject);
    }

    trigger_deprecation('halloverden/symfony-voter-bundle', '3.0.0', 'Not providing a token to the "%s()" methods is deprecated.', __METHOD__);
    return $this->security->isGranted($attribute, $subject);
  }

  /**
   * @param string $attribute
   * @param array  $subjects
   *
   * @return bool
   */
  private function supportsSubjects(string $attribute, array $subjects): bool {
    foreach ($this->getMandatorySubjectKeys($attribute) as $key) {
      if (!\array_key_exists($key, $subjects)) {
        return false;
      }
    }

    foreach ($subjects as $key => $s) {
      if (!$this->supportsSubject($key, $s, $attribute)) {
        return false;
      }
    }

    return true;
  }

}
