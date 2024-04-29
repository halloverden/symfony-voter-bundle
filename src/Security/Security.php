<?php

namespace HalloVerden\VoterBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;

/**
 * Class Security
 *
 * @package HalloVerden\VoterBundle\Security
 * @deprecated use {@see SymfonySecurity} instead
 */
class Security implements SecurityInterface {

  /**
   * Security constructor.
   */
  public function __construct(
    private readonly SymfonySecurity                $security,
    private readonly AccessDecisionManagerInterface $accessDecisionManager,
    private readonly array                          $adminRoles = []) {
  }

  /**
   * @inheritDoc
   */
  public function isGrantedOnUser(UserInterface $user, mixed $attributes, mixed $subject = null): bool {
    if (!\is_array($attributes)) {
      $attributes = [$attributes];
    }

    $token = new UsernamePasswordToken($user, 'none', $user->getRoles());
    return $this->accessDecisionManager->decide($token, $attributes, $subject);
  }

  /**
   * @inheritDoc
   */
  public function isGrantedAnyAdmin(?UserInterface $user = null): bool {
    foreach ($this->adminRoles as $adminRole) {
      if ($this->_isGranted($adminRole, $user)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @inheritDoc
   */
  public function isGrantedEitherOf(array $attributes): bool {
    foreach ($attributes as $attribute) {
      if ($this->security->isGranted($attribute)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @inheritDoc
   */
  public function getUser(): ?UserInterface {
    return $this->security->getUser();
  }

  /**
   * @inheritDoc
   */
  public function isGranted(mixed $attributes, mixed $subject = null): bool {
    return $this->security->isGranted($attributes, $subject);
  }

  /**
   * @inheritDoc
   */
  public function getToken(): ?TokenInterface {
    return $this->security->getToken();
  }

  /**
   * @param mixed              $attributes
   * @param UserInterface|null $user
   *
   * @return bool
   */
  private function _isGranted(mixed $attributes, ?UserInterface $user = null): bool {
    if ($user === null) {
      return $this->security->isGranted($attributes);
    }

    return $this->isGrantedOnUser($user, $attributes);
  }

}
