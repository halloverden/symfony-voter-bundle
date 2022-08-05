<?php

namespace HalloVerden\VoterBundle\Tests\Security\Voter\Mock;

use Doctrine\Common\Collections\Collection;
use HalloVerden\VoterBundle\Security\Voter\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BaseVoterMock extends BaseVoter {

  protected function getSupportedAttributes(): array {
    return ['test'];
  }

  protected function getSupportedClasses(): array {
    return [SubjectMock::class, Subject2Mock::class];
  }

  /**
   * @inheritDoc
   */
  public function supports(string $attribute, mixed $subjects): bool {
    return parent::supports($attribute, $subjects);
  }

  /**
   * @inheritDoc
   */
  public function sortSubjects(Collection|array $subjects, array $supportedClasses, bool $strictLength = true): array|Collection {
    return parent::sortSubjects($subjects, $supportedClasses, $strictLength);
  }

  /**
   * @inheritDoc
   */
  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
    return true;
  }
}
