<?php

namespace releasecheck;

/**
 * Minimal class to show how to remove one test
 *
 * Use CAF as an example
 */
class TestSuiteCAF extends TestSuite
{
  /**
   * Tests that should be in the list for EntityCategory tests
   *
   * Remove cocov2
   */
  protected $ecTests = array(
    'assurance',
    'noec',
    'anonymous',
    'pseudonymous',
    'personalized',
    'rands',
  );

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    /**
     * Changes in order from TestSuite
     *
     * Remove cocov2
     */

    $this->order['personalized']['next'] = 'rands';
    $this->order['rands']['last'] = 'personalized';
  }
}
