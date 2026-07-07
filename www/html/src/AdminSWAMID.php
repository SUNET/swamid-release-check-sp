<?php

namespace releasecheck;

/**
 * Class extend funtions for admin interface used for Swamid
 */
class AdminSWAMID extends Admin
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

    $this->tests['CoCov1'] = array(
      'displayName' => 'CoCov1',
      'fullName' => 'CoCov1',
      'dbName' => 'cocov1-1',
      'expected' => array (
        'norEduPersonNIN' => 'norEduPersonNIN',
        'personalIdentityNumber' => 'personalIdentityNumber',
      ),
      'testResults' => $this->tests['CoCov2']['testResults']
    );

    # Use same expected as for CoCov1
    $this->tests['CoCov2']['expected'] = $this->tests['CoCov1']['expected'];
    # Use Swamid CoCov2-1
    $this->tests['CoCov2']['dbName'] = 'cocov2-1';
  }
}
