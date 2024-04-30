<?php

namespace HalloVerden\VoterBundle\Exception;

/**
 * @deprecated
 */
class InvalidSubjectException extends \RuntimeException {

  /**
   * InvalidSubjectException constructor.
   *
   * @param mixed|null      $subject
   * @param \Exception|null $previous
   * @param int             $code
   */
  public function __construct(mixed $subject = null, \Exception $previous = null, int $code = 0) {
    parent::__construct('Unsupported subject'. ($subject ? ' (' . get_class($subject) . ')' : ''), $code, $previous);
  }

}
