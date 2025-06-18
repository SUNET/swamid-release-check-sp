<?php
namespace releasecheck;

class TestSuite {

  protected const DESC_C = 'ISO_COUNTRY_CODE (se)';
  protected const DESC_CN = 'givenName + sn';
  protected const DESC_CO = 'ISO_COUNTRY_NAME (Sweden)';
  protected const DESC_DISPLAYNAME = self::DESC_CN;
  protected const DESC_EDUPERSONAFFILIATION = 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.';
  protected const DESC_EDUPERSONASSURANCE = 'User assurance information.';
  protected const DESC_EDUPERSONORCID = 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.';
  protected const DESC_EDUPERSONPRINCIPALNAME = 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.';
  protected const DESC_EDUPERSONSCOPEDAFFILIATION = 'eduPersonAffiliation, scoped';
  protected const DESC_GIVENNAME = 'Firstname';
  protected const DESC_MAIL = 'Mailaddress';
  protected const DESC_NOREDUORGACRONYM = 'Shortform of organisation name';
  protected const DESC_O = 'Organisation name';
  protected const DESC_PAIRWISEID = 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.';
  protected const DESC_PERSISTENTID = 'Should not be sent by default any more';
  protected const DESC_SCHACDATEOFBIRTH = '8 digit date of birth (YYYYMMDD)';
  protected const DESC_SCHACHOMEORGANIZATION = 'Specifies a person\'s home organization using the domain name of the organization';
  protected const DESC_SCHACHOMEORGANIZATIONTYPE = 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution';
  protected const DESC_SN = 'Lastname';
  protected const DESC_SUBJECTID = 'Its value for a given subject is independent of the relying party to whom it is given.';
  protected const DESC_TRANSIENTID = self::DESC_PERSISTENTID;

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
    'anonymous'    => array (
      'name'     => 'REFEDS Anonymous Access',
      'tab'      => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization'      => self::DESC_SCHACHOMEORGANIZATION,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'anonymous',
    ),
    'assurance'    => array (
      'name'     => 'Assurance Attribute test',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'eduPersonAssurance'     => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (),
      'subtest'  => 'RAF',
    ),
    'cocov1-1'     => array (
      'name'     => 'GÉANT CoCo part 1',
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
        'schacHomeOrganizationType'  => self::DESC_SCHACHOMEORGANIZATIONTYPE,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'CoCov1',
    ),
    'cocov1-2'     => array (
      'name'     => 'GÉANT CoCo part 2',
      'tab'      => 'entityCategory',
      'expected' =>array (
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
        'schacHomeOrganization'  =>self::DESC_SCHACHOMEORGANIZATION,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'CoCov1',
    ),
    'cocov2-1'     => array (
      'name'     => 'REFEDS CoCo part 1',
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
        'schacHomeOrganizationType'  => self::DESC_SCHACHOMEORGANIZATIONTYPE,
        'pairwise-id'                => self::DESC_PAIRWISEID,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'CoCov2',
    ),
    'cocov2-2'     => array (
      'name'     => 'REFEDS CoCo part 2',
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
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'CoCov2',
    ),
    'mfa'          => array (
      'name'     => 'MFA Check',
      'tab'      => 'mfa',
      'expected' => array (
        'eduPersonPrincipalName' => self::DESC_EDUPERSONPRINCIPALNAME,
        'eduPersonAssurance'     => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (
      ),
      'subtest'  => 'MFA',
    ),
    'noec'         => array (
      'name'     => 'No EC (shall not send any attributes!)',
      'tab'      => 'entityCategory',
      'expected' => array (),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => '',
    ),
    'pseudonymous' => array (
      'name'     => 'REFEDS Pseudonymous Access',
      'tab'      => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization'      => self::DESC_SCHACHOMEORGANIZATION,
        'pairwise-id'                => self::DESC_PAIRWISEID,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAssurance'         => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'pseudonymous',
    ),
    'personalized' => array (
      'name'     => 'REFEDS Personalized Access',
      'tab'      => 'entityCategory',
      'expected' => array (
        'schacHomeOrganization'      => self::DESC_SCHACHOMEORGANIZATION,
        'subject-id'                 => self::DESC_SUBJECTID,
        'displayName'                => self::DESC_DISPLAYNAME,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'mail'                       => 'Personalized require mailaddress',
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAssurance'         => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (
        'persistent-id' => self::DESC_PERSISTENTID,
        'transient-id'  => self::DESC_TRANSIENTID,
      ),
      'subtest'  => 'personalized',
    ),
    'rands'        => array (
      'name'     => 'REFEDS R&S',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName'     => self::DESC_EDUPERSONPRINCIPALNAME,
        'mail'                       => 'R&S require mailaddress',
        'displayName'                => self::DESC_DISPLAYNAME,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
      ),
      'nowarn'   => array (
        'persistent-id'       => self::DESC_PERSISTENTID,
        'transient-id'        => self::DESC_TRANSIENTID,
        'eduPersonTargetedID' => 'For R&S release only if eduPersonPrincipalName is reassignable',
        'eduPersonUniqueID'   => 'A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.',
      ),
      'subtest'  => 'R&S',
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
