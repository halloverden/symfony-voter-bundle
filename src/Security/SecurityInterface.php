<?php

namespace HalloVerden\VoterBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security as SymfonySecurity;

/**
 * @deprecated use {@see SymfonySecurity} instead
 */
interface SecurityInterface {

  /**
   * @param UserInterface $user
   * @param mixed         $attributes
   * @param mixed|null    $subject
   *
   * @return bool
   */
  public function isGrantedOnUser(UserInterface $user, mixed $attributes, mixed $subject = null): bool;

  /**
   * @param UserInterface|null $user
   *
   * @return bool
   */
  public function isGrantedAnyAdmin(?UserInterface $user = null): bool;

  /**
   * @param array $attributes
   *
   * @return bool
   */
  public function isGrantedEitherOf(array $attributes): bool;

  /**
   * @return UserInterface|null
   */
  public function getUser(): ?UserInterface;

  /**
   * @param mixed $attributes
   * @param mixed $subject
   *
   * @return bool
   */
  public function isGranted(mixed $attributes, mixed $subject = null): bool;

  /**
   * @return TokenInterface|null
   */
  public function getToken(): ?TokenInterface;

}
