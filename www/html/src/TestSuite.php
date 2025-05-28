<?php
namespace releasecheck;

class TestSuite {
  /**
   * Order of the tests
   *
   * Built on arrays with last and next test
   */
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
      'last' => 'cocov2-2',
      'next' => 'cocov1-2',
    ),
    'cocov1-2' => array (
      'last' => 'cocov1-1',
      'next' => 'rands',
    ),
    'cocov2-1' => array (
      'last' => 'personalized',
      'next' => 'cocov2-2',
    ),
    'cocov2-2' => array (
      'last' => 'cocov2-1',
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
      'last' => 'cocov1-2',
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
    'cocov1-1',
    'cocov1-2',
    'rands',
  );

  /**
   * Configuration of tests
   *
   * * name - full name of test .
   * * tab - tab on resultpage .
   * * expected - expected attributes with description .
   * * nowarn - extra attributes to accept with description .
   * * subtest - subtest to validate correctnes of attributes .
   */
  protected $tests = array(
    'anonymous' => array (
      'name' => 'REFEDS Anonymous Access',
      'tab' => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'anonymous',
    ),
    'assurance' => array (
      'name' => 'Assurance Attribute test',
      'tab' => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonAssurance' => 'User assurance information.',
      ),
      'nowarn' => array (),
      'subtest' => 'RAF',
    ),
    'cocov1-1' => array (
      'name' => 'GÉANT CoCo part 1',
      'tab' => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov1',
    ),
    'cocov1-2' => array (
      'name' => 'GÉANT CoCo part 2',
      'tab' => 'entityCategory',
      'expected' =>array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'mail' => 'Mailaddress',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'o' => 'Organisation name',
        'norEduOrgAcronym' => 'Shortform of organisation name',
        'c' => 'ISO_COUNTRY_CODE (se)',
        'co' => 'ISO_COUNTRY_NAME (Sweden)',
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov1',
    ),
    'cocov2-1' => array (
      'name' => 'REFEDS CoCo part 1',
      'tab' => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov2',
    ),
    'cocov2-2' => array (
      'name' => 'REFEDS CoCo part 2',
      'tab' => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'mail' => 'Mailaddress',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'o' => 'Organisation name',
        'norEduOrgAcronym' => 'Shortform of organisation name',
        'c' => 'ISO_COUNTRY_CODE (se)',
        'co' => 'ISO_COUNTRY_NAME (Sweden)',
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
        'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given.',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'CoCov2',
    ),
    'mfa' => array (
      'name' => 'MFA Check',
      'tab' => 'mfa',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonAssurance' => 'User assurance information.',
      ),
      'nowarn' => array (
      ),
      'subtest' => 'MFA',
    ),
    'noec' => array (
      'name' => 'No EC (shall not send any attributes!)',
      'tab' => 'entityCategory',
      'expected' => array (),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => '',
    ),
    'pseudonymous' => array (
      'name' => 'REFEDS Pseudonymous Access',
      'tab' => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAssurance' => 'User assurance information.',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'pseudonymous',
    ),
    'personalized' => array (
      'name' => 'REFEDS Personalized Access',
      'tab' => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
        'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given.',
        'displayName' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'mail' => 'Personalized require mailaddress',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAssurance' => 'User assurance information.',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
      ),
      'subtest' => 'personalized',
    ),
    'rands' => array (
      'name' => 'REFEDS R&S',
      'tab' => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'mail' => 'R&S require mailaddress',
        'displayName' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ),
      'nowarn' => array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
        'eduPersonTargetedID' => 'For R&S release only if eduPersonPrincipalName is reassignable',
        'eduPersonUniqueID' => 'A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.',
      ),
      'subtest' => 'R&S',
    )
  );

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct() {
    $order['mfa'] = isset($_GET['forceAuthn']) ?
      array (
        'last' => 'mfa',
        'next' => 'result') :
      array (
        'last' => '',
        'next' => 'mfa');
  }

  /**
   * Return the testconfig for a specific test.
   *
   * @return array|false
   */
  public function getTest($test) {
    return isset($this->tests[$test]) ? $this->tests[$test] : false;
  }

  /**
   * Return the name for a specific test.
   *
   * @return string|false
   */
  public function getTestName($test) {
    return isset($this->tests[$test]) ? $this->tests[$test]['name'] : false;
  }

  /**
   * Return the list for EntityCategory tests
   *
   * @return array
   */
  public function getECTests() {
    return $this->ecTests;
  }

  /**
   * Return the lst and next test for a specific test.
   *
   * @return array|false
   */
  public function getOrder($test) {
    return isset($this->order[$test]) ? $this->order[$test] : false;
  }
}
