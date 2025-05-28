<?php
namespace releasecheck;

class TestSuiteSWAMID extends TestSuite {
  protected $order = array (
    'anonymous' => array (
      'last' => 'noec',
      'next' => 'pseudonymous',
    ),
    'assurance' => array (
      'last' => '',
      'next' => 'noec',
    ),
    'cocov1-1' => array (
      'last' => 'cocov2-3',
      'next' => 'cocov1-2',
    ),
    'cocov1-2' => array (
      'last' => 'cocov1-1',
      'next' => 'cocov1-3',
    ),
    'cocov1-3' => array (
      'last' => 'cocov1-2',
      'next' => 'rands',
    ),
    'cocov2-1' => array (
      'last' => 'personalized',
      'next' => 'cocov2-2',
    ),
    'cocov2-2' => array (
      'last' => 'cocov2-1',
      'next' => 'cocov2-3',
    ),
    'cocov2-3' => array (
      'last' => 'cocov2-2',
      'next' => 'cocov1-1',
    ),
    'esi' => array (
      'last' => '',
      'next' => 'result',
    ),
    'noec' => array (
      'last' => 'assurance',
      'next' => 'anonymous',
    ),
    'pseudonymous' => array (
      'last' => 'anonymous',
      'next' => 'personalized',
    ),
    'personalized' => array (
      'last' => 'pseudonymous',
      'next' => 'cocov2-1',
    ),
    'rands' => array (
      'last' => 'cocov1-3',
      'next' => 'result',
    ),
  );

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
  public function __construct() {
    parent::__construct();
    /**
     * Changes from tests in TestSuite
     */
    $this->tests['assurance']['expected']['eduPersonAssurance'] =
     'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.';

    $this->tests['cocov1-1']['name'] = 'GÉANT CoCo part 1, from SWAMID';
    $this->tests['cocov1-1']['expected']['norEduPersonNIN'] =
      '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work.';
    $this->tests['cocov1-1']['expected']['personalIdentityNumber'] =
      'Swedish 12 digit Socialsecuritynumber. Same as in passport';
    $this->tests['cocov1-1']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['cocov1-2']['name'] = 'GÉANT CoCo part 2, from SWAMID';
    $this->tests['cocov2-1']['name'] = 'REFEDS CoCo part 1, from SWAMID';
    $this->tests['cocov2-1']['expected']['norEduPersonNIN'] = $this->tests['cocov1-1']['expected']['norEduPersonNIN'];
    $this->tests['cocov2-1']['expected']['personalIdentityNumber'] = $this->tests['cocov1-1']['expected']['personalIdentityNumber'];
    $this->tests['cocov2-1']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['cocov2-2']['name'] = 'REFEDS CoCo part 2, from SWAMID';

    $this->tests['mfa']['name'] = 'SWAMID MFA Check';
    $this->tests['mfa']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['pseudonymous']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    $this->tests['personalized']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    // Extra attribute for SWAMID test
    $this->tests['rands']['expected']['eduPersonAssurance'] = $this->tests['assurance']['expected']['eduPersonAssurance'];

    // New test for swamid
    $this->tests['cocov1-3'] = array (
      'name' => 'GÉANT CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'tab' => 'entityCategory',
      'expected' =>array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'sn' => 'Lastname',
        'mail' => 'Mailaddress',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov1',
    );

    // New test for swamid
    $this->tests['cocov2-3'] = array (
      'name' => 'REFEDS CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'tab' => 'entityCategory',
      'expected' => array (
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'sn' => 'Lastname',
        'mail' => 'Mailaddress',
      ),
      'nowarn' => array (
        'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given (not recomended for this test, but should be sent if pairwise-id isn\'t sent) .',
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov2',
    );

    // New test for swamid
    $this->tests['esi'] = array (
      'name' => 'SWAMID Entity Category Release Check - European Student Identifier',
      'esi',
      'expected' =>array (
        'schacPersonalUniqueCode' => 'Usually used within SWAMID for the European Student Identifier.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ),
      'nowarn' => array (
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'ESI',
    );
  }
}
