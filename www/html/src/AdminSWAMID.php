<?php

namespace releasecheck;

/**
 * Class extend funtions for admin interface used for Swamid
 */
class AdminSWAMID extends Admin
{
  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    $this->tests['cocov1'] = array(
      'displayName' => 'CoCov1',
      'fullName' => 'CoCov1',
      'dbName' => 'cocov1-1',
      'expected' => array (
        'norEduPersonNIN' => 'norEduPersonNIN',
        'personalIdentityNumber' => 'personalIdentityNumber',
      ),
      'testResults' => $this->tests['cocov2']['testResults']
    );

    # Use same expected as for cocov1
    $this->tests['cocov2']['expected'] = $this->tests['cocov1']['expected'];
    # Use Swamid cocov2-1
    $this->tests['cocov2']['dbName'] = 'cocov2-1';
  }
}
