<?php

namespace HalloVerden\VoterBundle\EndpointScope;

final class EndpointScopeQueryParamContext {
  private string $queryParam;
  private \Closure $valueExtractor;

  /**
   * EndpointScopeContext constructor.
   */
  public function __construct(string $queryParam, ?callable $valueExtractor = null) {
    $this->queryParam = $queryParam;
    $this->valueExtractor = $valueExtractor ? $valueExtractor(...) : fn(string $value) => $value;
  }

  /**
   * @return string
   */
  public function getQueryParam(): string {
    return $this->queryParam;
  }

  /**
   * @return \Closure
   */
  public function getValueExtractor(): \Closure {
    return $this->valueExtractor;
  }

}
