<?php

namespace HalloVerden\VoterBundle\EndpointScope;

class CommaSeparatedValueExtractor {

  /**
   * CommaSeparatedValueExtractor constructor.
   */
  public function __construct(private readonly string $value) {
  }

  /**
   * @param string $value
   *
   * @return string|null
   */
  public function __invoke(string $value): ?string {
    if (in_array($this->value, explode(',', $value), true)) {
      return $this->value;
    }

    return null;
  }

}
