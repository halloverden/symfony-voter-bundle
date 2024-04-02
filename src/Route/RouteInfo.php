<?php

namespace HalloVerden\VoterBundle\Route;

readonly final class RouteInfo {

  /**
   * RouteInfo constructor.
   */
  public function __construct(
    private string $path
  ) {
  }

  /**
   * @return string
   */
  public function getPath(): string {
    return $this->path;
  }

}
