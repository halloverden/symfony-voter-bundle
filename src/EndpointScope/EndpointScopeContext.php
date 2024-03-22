<?php

namespace HalloVerden\VoterBundle\EndpointScope;

readonly final class EndpointScopeContext {

  /**
   * EndpointScopeContext constructor.
   */
  public function __construct(
    private ?string $scopePrefix = null,
    private ?string $scopeSuffix = null,
  ) {
  }

  /**
   * @return string|null
   */
  public function getScopePrefix(): ?string {
    return $this->scopePrefix;
  }

  /**
   * @return string|null
   */
  public function getScopeSuffix(): ?string {
    return $this->scopeSuffix;
  }

}
