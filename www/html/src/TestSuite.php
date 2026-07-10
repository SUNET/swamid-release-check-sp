<?php

namespace releasecheck;

class TestSuite
{
  protected const DESC_C = 'ISO_COUNTRY_CODE (se)';
  protected const DESC_CN = 'givenName + sn';
  protected const DESC_CO = 'ISO_COUNTRY_NAME (Sweden)';
  protected const DESC_DISPLAYNAME = self::DESC_CN;
  protected const DESC_EDUPERSONAFFILIATION =
    'Specifies the person\'s relationship(s) to the institution in broad categories such as student,' .
    ' faculty, staff, alum, etc.';
  protected const DESC_EDUPERSONASSURANCE = 'User assurance information.';
  protected const DESC_EDUPERSONORCID =
    'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD' .
    ' via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual' .
    ' researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly' .
    ' work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.';
  protected const DESC_EDUPERSONPRINCIPALNAME =
    'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a' .
    ' name-based identifier for the person and where the "scope" portion MUST be the administrative domain of' .
    ' the identity system where the identifier was created and assigned.';
  protected const DESC_EDUPERSONSCOPEDAFFILIATION = 'eduPersonAffiliation, scoped';
  protected const DESC_GIVENNAME = 'Firstname';
  protected const DESC_MAIL = 'Mailaddress (should only be one)';
  protected const DESC_MAILLOCALADDRESS = 'List of additional organizational mailaddresses for the person';
  protected const DESC_NOREDUORGACRONYM = 'Shortform of organisation name';
  protected const DESC_O = 'Organisation name';
  protected const DESC_PAIRWISEID =
    'Its value for a given subject depends upon the relying party to whom it is given,' .
    ' thus preventing unrelated systems from using it as a basis for correlation.';
  protected const DESC_SCHACDATEOFBIRTH = '8 digit date of birth (YYYYMMDD)';
  protected const DESC_SCHACHOMEORGANIZATION =
    'Specifies a person\'s home organization using the domain name of the organization';
  protected const DESC_SCHACHOMEORGANIZATIONTYPE =
    'example urn:schac:homeOrganizationType:eu:higherEducationInstitution';
  protected const DESC_SN = 'Lastname';
  protected const DESC_SUBJECTID =
    'Its value for a given subject is independent of the relying party to whom it is given.';

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
    'cocov2' => array (
      'last' => 'personalized',
      'next' => 'rands',
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
      'next' => 'cocov2',
    ),
    'rands' => array (
      'last' => 'cocov2',
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
    'cocov2',
    'rands',
  );

  /**
   * Configuration of tests
   *
   * * name - full name of test .
   * * tab - tab on resultpage .
   * * expected - expected attributes with description .
   * * nowarn - extra attributes to accept.
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
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'anonymous',
    ),
    'assurance'    => array (
      'name'     => 'Assurance Attribute test',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonAssurance'     => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (
        'eduPersonPrincipalName'     => '',
        'displayName'                => '',
        'eduPersonScopedAffiliation' => '',
        'givenName'                  => '',
         'mail'                      => '',
         'schacHomeOrganization'     => '',
         'sn'                        => '',
         'subject-id'                => '',
      ),
      'subtest'  => 'RAF',
    ),
    'cocov2'     => array (
      'name'     => 'REFEDS CoCo',
      'tab'      => 'entityCategory',
      'expected' => array (
        'subject-id'                 => self::DESC_SUBJECTID,
        'eduPersonPrincipalName'     => self::DESC_EDUPERSONPRINCIPALNAME,
        'eduPersonOrcid'             => self::DESC_EDUPERSONORCID,
        'displayName'                => self::DESC_DISPLAYNAME,
        'cn'                         => self::DESC_CN,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'eduPersonAssurance'         => self::DESC_EDUPERSONASSURANCE,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAffiliation'       => self::DESC_EDUPERSONAFFILIATION,
        'mail'                       => self::DESC_MAIL,
        'schacHomeOrganization'      => self::DESC_SCHACHOMEORGANIZATION,
        'mailLocalAddress'           => self::DESC_MAILLOCALADDRESS
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'CoCov2',
    ),
    'noec'         => array (
      'name'     => 'No EC (shall not send any attributes!)',
      'tab'      => 'entityCategory',
      'expected' => array (),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
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
        'persistent-id' => '',
        'transient-id'  => '',
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
        'mail'                       => self::DESC_MAIL,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
        'eduPersonAssurance'         => self::DESC_EDUPERSONASSURANCE,
      ),
      'nowarn'   => array (
        'persistent-id' => '',
        'transient-id'  => '',
      ),
      'subtest'  => 'personalized',
    ),
    'rands'        => array (
      'name'     => 'REFEDS R&S',
      'tab'      => 'entityCategory',
      'expected' => array (
        'eduPersonPrincipalName'     => self::DESC_EDUPERSONPRINCIPALNAME,
        'mail'                       => self::DESC_MAIL,
        'displayName'                => self::DESC_DISPLAYNAME,
        'givenName'                  => self::DESC_GIVENNAME,
        'sn'                         => self::DESC_SN,
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
      ),
      'nowarn'   => array (
        'persistent-id'       => '',
        'transient-id'        => '',
        'eduPersonAssurance'  => '',
        'eduPersonTargetedID' => '',
        'eduPersonUniqueID'   => '',
      ),
      'subtest'  => 'R&S',
    ),
    'esi' => array (
      'name'     => 'European Student Identifier',
      'tab'      => 'esi',
      'expected' => array (
        'schacPersonalUniqueCode'    => 'Usually used for the European Student Identifier.',
        'eduPersonScopedAffiliation' => self::DESC_EDUPERSONSCOPEDAFFILIATION,
      ),
      'nowarn'   => array (
        'eduPersonAffiliation' => '',
        'persistent-id'        => '',
        'transient-id'         => '',
      ),
      'subtest'  => 'ESI',
    ),
  );

  /**
   * Function ot used more then to add translations
   * Should never be called !!
   *
   * @return void
   */
  protected function strings2Translate()
  {
    $str[] = _('No EC (shall not send any attributes!)');
    $str[] = _('ISO_COUNTRY_CODE (se)');
    $str[] = _('givenName + sn');
    $str[] = _('ISO_COUNTRY_NAME (Sweden)');
    $str[] = _('Specifies the person\'s relationship(s) to the institution in broad categories such as student,' .
      ' faculty, staff, alum, etc.');
    $str[] = _('User assurance information.');
    $str[] = _('This attribute should only be released if and only if the IdP organization has retrived the ORCID iD' .
      ' via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual' .
      ' researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work' .
      ' products. ORCID iDs are assigned, managed and maintained by the ORCID organization.');
    $str[] = _('A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is' .
      ' a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the' .
      ' identity system where the identifier was created and assigned.');
    $str[] = _('eduPersonAffiliation, scoped');
    $str[] = _('Firstname');
    $str[] = _('Mailaddress (should only be one)');
    $str[] = _('List of additional organizational mailaddresses for the person');
    $str[] = _('Shortform of organisation name');
    $str[] = _('Organisation name');
    $str[] = _('Its value for a given subject depends upon the relying party to whom it is given, thus preventing' .
      ' unrelated systems from using it as a basis for correlation.');
    $str[] = _('8 digit date of birth (YYYYMMDD)');
    $str[] = _('Specifies a person\'s home organization using the domain name of the organization');
    $str[] = _('example urn:schac:homeOrganizationType:eu:higherEducationInstitution');
    $str[] = _('Lastname');
    $str[] = _('Its value for a given subject is independent of the relying party to whom it is given.');
    $str[] = _('Usually used for the European Student Identifier.');
  }

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    global $config;
    if (isset($config)) {
      $config = $config;
    } else {
      $config = new Configuration();
    }
    foreach ($config->getFederation()['hideTest'] as $test => $hide) {
      if ($hide) {
        $pos = array_search($test, $this->ecTests);
        if ($pos !== false) {
          unset($this->ecTests[$pos]);
        }
        if (isset($this->tests[$test])) {
          unset($this->tests[$test]);
        }
        if (isset($this->order[$test])) {
          # update last test->next to this text->next
          $this->order[$this->order[$test]['last']]['next'] = $this->order[$test]['next'];
          # update next test->last to this test->last
          $this->order[$this->order[$test]['next']]['last'] = $this->order[$test]['last'];
          unset($this->order[$test]);
        }
      }
    }
  }

  /**
   * Return the testconfig for a specific test.
   *
   * @return array|false
   */
  public function getTest($test)
  {
    return isset($this->tests[$test]) ? $this->tests[$test] : false;
  }

  /**
   * Return the all tests configured.
   *
   * @return array
   */
  public function getTests()
  {
    return $this->tests;
  }

  /**
   * Return the name for a specific test.
   *
   * @return string|false
   */
  public function getTestName($test)
  {
    return isset($this->tests[$test]) ? $this->tests[$test]['name'] : false;
  }

  /**
   * Return the list for EntityCategory tests
   *
   * @return array
   */
  public function getECTests()
  {
    return $this->ecTests;
  }

  /**
   * Return the lst and next test for a specific test.
   *
   * @return array|false
   */
  public function getOrder($test)
  {
    return isset($this->order[$test]) ? $this->order[$test] : false;
  }
}
