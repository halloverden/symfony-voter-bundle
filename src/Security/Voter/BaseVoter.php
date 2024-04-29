<?php

namespace HalloVerden\VoterBundle\Security\Voter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HalloVerden\VoterBundle\Exception\InvalidSubjectException;
use HalloVerden\VoterBundle\Security\SecurityInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SymfonyVoter;

/**
 * @deprecated Use {@see Voter} instead.
 */
abstract class BaseVoter extends SymfonyVoter {

  /**
   * BaseVoter constructor.
   *
   * @param SecurityInterface $security
   */
  public function __construct(protected readonly SecurityInterface $security) {
  }

  protected abstract function getSupportedAttributes(): array;
  protected abstract function getSupportedClasses(): array;

  /**
   * @inheritDoc
   */
  protected function supports(string $attribute, mixed $subjects): bool {
    if (!\in_array($attribute, $this->getSupportedAttributes())) {
      return false;
    }

    if (null === $subjects) {
      return true;
    }

    if (!\is_array($subjects) && !($subjects instanceof Collection)) {
      return false;
    }

    $supportedClasses = $this->getSupportedClasses();

    foreach ($subjects as $subject) {
      $isSupported = false;

      foreach ($supportedClasses as $supportedClass) {
        if ($this->isSupported($subject, $supportedClass)) {
          $isSupported = true;
          break;
        }
      }

      if (!$isSupported) {
        return false;
      }
    }

    return true;
  }

  /**
   * @param mixed  $subject
   * @param string $supportedClass
   *
   * @return bool
   */
  private function isSupported(mixed $subject, string $supportedClass): bool {
    $type = strtolower($supportedClass);
    $type = 'boolean' == $type ? 'bool' : $supportedClass;
    $isFunction = 'is_'.$type;
    $ctypeFunction = 'ctype_'.$type;

    if (\function_exists($isFunction) && $isFunction($subject)) {
      return true;
    } elseif (\function_exists($ctypeFunction) && $ctypeFunction($subject)) {
      return true;
    } elseif ($subject instanceof $supportedClass) {
      return true;
    }

    return false;
  }

  /**
   * Sorts subjects according to supportedClasses.
   *
   * @param array|Collection $subjects
   * @param array            $supportedClasses
   * @param bool             $strictLength
   * @return array|Collection
   */
  protected function sortSubjects(array|Collection $subjects, array $supportedClasses, bool $strictLength = true): array|Collection {
    $isCollection = false;
    if ($subjects instanceof Collection) {
      $subjects = $subjects->toArray();
      $isCollection = true;
    }

    usort($subjects, function ($a, $b) use ($supportedClasses) {
      $supportedClassIndexA = $this->getSupportedClassIndex($a, $supportedClasses);
      $supportedClassIndexB = $this->getSupportedClassIndex($b, $supportedClasses);

      if ($supportedClassIndexA === $supportedClassIndexB) {
        return 0;
      }

      return $supportedClassIndexA < $supportedClassIndexB ? -1 : 1;
    });

    if ($strictLength && count($subjects) !== count($supportedClasses)) {
      throw new InvalidSubjectException();
    }

    return $isCollection ? new ArrayCollection($subjects) : $subjects;
  }

  /**
   * @param mixed $subject
   * @param array $supportedClasses
   *
   * @return int
   */
  private function getSupportedClassIndex(mixed $subject, array $supportedClasses): int {
    foreach ($supportedClasses as $i => $supportedClass) {
      if (is_array($supportedClass)) {
        if ($this->isSupportedMulti($subject, $supportedClass)) {
          return $i;
        }

        continue;
      }

      if ($this->isSupported($subject, $supportedClass)) {
        return $i;
      }
    }

    throw new InvalidSubjectException($subject);
  }

  /**
   * @param mixed $subject
   * @param array $supportedClasses
   *
   * @return bool
   */
  private function isSupportedMulti(mixed $subject, array $supportedClasses): bool {
    foreach ($supportedClasses as $supportedClass) {
      if ($this->isSupported($subject, $supportedClass)) {
        return true;
      }
    }

    return false;
  }

}
