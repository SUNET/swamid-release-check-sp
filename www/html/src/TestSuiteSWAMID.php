<?php

namespace releasecheck;

class TestSuiteSWAMID extends TestSuite
{
  /**
   * Tests that should be in the list for EntityCategory tests
   */
  protected $ecTests = array(
    'assurance',
    'noec',
    'anonymous',
    'pseudonymous',
    'personalized',
    'cocov2-1',
    'cocov2-2',
    'cocov2-3',
    'cocov1-1',
    'cocov1-2',
    'cocov1-3',
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
    $this->updateOrder();
    $this->updateTest();
    $this->addTests();
  }

  /**
   * Update order for tests
   *
   * Since we add som tests we need to update order
   *
   * @return void
   */
  private function updateOrder()
  {
    /**
     * Changes in order from TestSuite
     */
    $this->order['personalized']['next'] = 'cocov2-1';
    $this->order['cocov1-1'] = array (
      'last' => 'cocov2-3',
      'next' => 'cocov1-2',
    );
    $this->order['cocov1-2'] = array (
      'last' => 'cocov1-1',
      'next' => 'cocov1-3',
    );
    $this->order['cocov1-3'] = array (
      'last' => 'cocov1-2',
      'next' => 'rands',
    );
    $this->order['cocov2-1'] = array (
      'last' => 'personalized',
      'next' => 'cocov2-2',
    );
    $this->order['cocov2-2'] = array (
      'last' => 'cocov2-1',
      'next' => 'cocov2-3',
    );
    $this->order['cocov2-3'] = array (
      'last' => 'cocov2-2',
      'next' => 'cocov1-1',
    );
    $this->order['rands']['last'] = 'cocov1-3';
  }

  /**
   * Update tests that already exists in TestSuite
   *
   * Add some extra attributes and update some descriptions
   *
   * @return void
   */
  private function updateTest()
  {
    $this->tests['assurance']['expected']['eduPersonAssurance'] =
      _('User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only' .
      ' if both the organisation and the user is validated for the assurance level. Furthermore,' .
      ' REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.');
    $this->tests['pseudonymous']['expected']['eduPersonAssurance'] =
      $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['personalized']['expected']['eduPersonAssurance'] =
      $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['rands']['expected']['eduPersonAssurance'] =
      $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['esi']['expected']['schacPersonalUniqueCode'] =
      _('Usually used within SWAMID for the European Student Identifier.');
  }

  /**
   * Add extra tests
   *
   * @return void
   */
  private function addTests()
  {
    // Added tests for swamid
    $this->tests['cocov1-1'] = array (
      'name'     => 'GÉANT CoCo part 1, from SWAMID',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName'     => self::DESC_EDUPERSONPRINCIPALNAME,
        'eduPersonOrcid'             => self::DESC_EDUPERSONORCID,
        'schacDateOfBirth'           => self::DESC_SCHACDATEOFBIRTH,
        'displayName'                => self::DESC_DISPLAYNAME,
        'cn'                         => self::DESC_CN,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'eduPersonAssurance'         => self::DESC_EDUPERSONASSURANCE,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAffiliation'       => self::DESC_EDUPERSONAFFILIATION,
        'schacHomeOrganizationType'  =>
          self::DESC_SCHACHOMEORGANIZATIONTYPE,
        'norEduPersonNIN'            =>
          '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work.',
        'personalIdentityNumber'     => 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
        'eduPersonAssurance'         => $this->tests['assurance']['expected']['eduPersonAssurance'],
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov1',
    );
    $this->tests['cocov1-2'] = array (
      'name'     => 'GÉANT CoCo part 2, from SWAMID',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'mail'                   => self::DESC_MAIL,
        'displayName'            => self::DESC_DISPLAYNAME,
        'cn'                     => self::DESC_CN,
        'givenName'              => self::DESC_GIVENNAME,
        'sn'                     => self::DESC_SN,
        'o'                      => self::DESC_O,
        'norEduOrgAcronym'       => self::DESC_NOREDUORGACRONYM,
        'c'                      => self::DESC_C,
        'co'                     => self::DESC_CO,
        'schacHomeOrganization'  => self::DESC_SCHACHOMEORGANIZATION,
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov1',
    );
    $this->tests['cocov1-3'] = array (
      'name'     =>
        'GÉANT CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'displayName'            => self::DESC_DISPLAYNAME,
        'cn'                     => self::DESC_CN,
        'givenName'              => self::DESC_GIVENNAME,
        'schacDateOfBirth'       => self::DESC_SCHACDATEOFBIRTH,
        'sn'                     => self::DESC_SN,
        'mail'                   => self::DESC_MAIL,
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov1',
    );

    $this->tests['cocov2-1'] = array(
      'name'     => 'REFEDS CoCo part 1, from SWAMID',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName'     => self::DESC_EDUPERSONPRINCIPALNAME,
        'eduPersonOrcid'             => self::DESC_EDUPERSONORCID,
        'schacDateOfBirth'           => self::DESC_SCHACDATEOFBIRTH,
        'displayName'                => self::DESC_DISPLAYNAME,
        'cn'                         => self::DESC_CN,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'eduPersonAssurance'         => $this->tests['assurance']['expected']['eduPersonAssurance'],
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAffiliation'       => self::DESC_EDUPERSONAFFILIATION,
        'schacHomeOrganizationType'  => self::DESC_SCHACHOMEORGANIZATIONTYPE,
        'pairwise-id'                => self::DESC_PAIRWISEID,
        'norEduPersonNIN'            =>
          '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work.',
        'personalIdentityNumber'     => 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov2',
    );
    $this->tests['cocov2-2'] = array (
      'name'     => 'REFEDS CoCo part 2, from SWAMID',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'mail'                   => self::DESC_MAIL,
        'displayName'            => self::DESC_DISPLAYNAME,
        'cn'                     => self::DESC_CN,
        'givenName'              => self::DESC_GIVENNAME,
        'sn'                     => self::DESC_SN,
        'o'                      => self::DESC_O,
        'norEduOrgAcronym'       => self::DESC_NOREDUORGACRONYM,
        'c'                      => self::DESC_C,
        'co'                     => self::DESC_CO,
        'schacHomeOrganization'  => self::DESC_SCHACHOMEORGANIZATION,
        'subject-id'             => self::DESC_SUBJECTID,
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov2',
    );
    $this->tests['cocov2-3'] = array (
      'name'     =>
        'REFEDS CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'tab'      => 'entityCategory',
      'expected' => array (
        'pairwise-id'            => self::DESC_PAIRWISEID,
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'displayName'            => self::DESC_DISPLAYNAME,
        'cn'                     => self::DESC_CN,
        'givenName'              => self::DESC_GIVENNAME,
        'schacDateOfBirth'       => self::DESC_SCHACDATEOFBIRTH,
        'sn'                     => self::DESC_SN,
        'mail'                   => self::DESC_MAIL,
      ),
      'nowarn'   => array (
        'subject-id'    => '',
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov2',
    );
  }
}
