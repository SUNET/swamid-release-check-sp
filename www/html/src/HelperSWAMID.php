<?php

namespace releasecheck;

class HelperSWAMID extends Helper
{
  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct(Configuration $config)
  {
    parent::__construct($config);
    $this->replacements['SWAMID_ASSURANCE'] = 'http://www.swamid.se/policy/assurance';  # NOSONAR Should be http://
  }
}
