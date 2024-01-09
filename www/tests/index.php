<?php
####### BEGIN Config #######
include ("../html/config.php");
####### END Config #########


require_once('IdPCheck.php');
$test = str_replace('.'.$basename,'',strtolower($_SERVER['HTTP_HOST']));
$quickTest = isset($_GET['quickTest']);
$singleTest = isset($_GET['singleTest']);
$swamidIdp = isset($_SERVER['Meta-registrationAuthority'])
  && $_SERVER['Meta-registrationAuthority'] == 'http://www.swamid.se/';  # NOSONAR Should be http://

switch ($test) {
  case 'assurance' :
    $IdPTest =  new IdPCheck(
      $basename,
      'assurance',
      'Assurance Attribute test',
      'entityCategory',
      $swamidIdp ?
        array (
          'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
          'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        ) :
        array (
          'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
          'eduPersonAssurance' => 'User assurance information.',
        ),
      array (
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('RAF','noec');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('','noec',$singleTest);
      $IdPTest->testAttributes('RAF');
    }
    break;
  case 'noec' :
    // Test1
    $IdPTest =  new IdPCheck(
      $basename,
      'noec',
      'No EC (shall not send any attributes!)',
      'entityCategory',
      array (),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('','anonymous');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('assurance','anonymous',$singleTest);
      $IdPTest->testAttributes('');
    }
    break;
  case 'anonymous' :
    $IdPTest =  new IdPCheck(
      $basename,
      'anonymous',
      'REFEDS Anonymous Access',
      'entityCategory',
      array (
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('anonymous','pseudonymous');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('noec','pseudonymous',$singleTest);
      $IdPTest->testAttributes('anonymous');
    }
    break;
  case 'pseudonymous' :
    $IdPTest =  new IdPCheck(
      $basename,
      'pseudonymous',
      'REFEDS Pseudonymous Access',
      'entityCategory',
      $swamidIdp ?
        array (
          'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
          'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
          'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
          'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        ) :
        array (
          'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
          'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
          'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
          'eduPersonAssurance' => 'User assurance information.',
        ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('pseudonymous','personalized');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('anonymous','personalized',$singleTest);
      $IdPTest->testAttributes('pseudonymous');
    }
    break;
  case 'personalized' :
    $IdPTest =  new IdPCheck(
      $basename,
      'personalized',
      'REFEDS Personalized Access',
      'entityCategory',
      $swamidIdp ?
        array (
          'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
          'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given.',
          'displayName' => 'givenName + sn',
          'givenName' => 'Firstname',
          'sn' => 'Lastname',
          'mail' => 'Personalized require mailaddress',
          'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
          'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        ) : array (
          'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization',
          'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given.',
          'displayName' => 'givenName + sn',
          'givenName' => 'Firstname',
          'sn' => 'Lastname',
          'mail' => 'Personalized require mailaddress',
          'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
          'eduPersonAssurance' => 'User assurance information.',
        ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('personalized','cocov2-1');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('pseudonymous','cocov2-1',$singleTest);
      $IdPTest->testAttributes('personalized');
    }
    break;
  case 'cocov2-1' :
    $expected = $swamidIdp ?
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'norEduPersonNIN' => '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work after 1/9-2020.',
        'personalIdentityNumber' => 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.'
      ) :
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.'
      );
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov2-1',
      'REFEDS CoCo part 1, from SWAMID',
      'entityCategory',
      $expected,
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('CoCov2','cocov2-2');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('personalized','cocov2-2',$singleTest);
      $IdPTest->testAttributes('CoCov2');
    }
    break;
  case 'cocov2-2' :
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov2-2',
      'REFEDS CoCo part 2, from SWAMID',
      'entityCategory',
      array (
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
        'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given.'
      ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      if ($swamidIdp) {
        $IdPTest->testAttributes('CoCov2','cocov2-3');
      } else {
        $IdPTest->testAttributes('CoCov2','cocov1-1');
      }
    } else {
      $IdPTest->showHeaders();
      if ($swamidIdp) {
        $IdPTest->showTestHeaders('cocov2-1','cocov2-3',$singleTest);
      } else {
        $IdPTest->showTestHeaders('cocov2-1','cocov1-1',$singleTest);
      }
      $IdPTest->testAttributes('CoCov2');
    }
    break;
  case 'cocov2-3' :
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov2-3',
      'REFEDS CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'entityCategory',
      array (
        'pairwise-id' => 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'sn' => 'Lastname',
        'mail' => 'Mailaddress'
      ),
      array (
        'subject-id' => 'Its value for a given subject is independent of the relying party to whom it is given (not recomended for this test, but should be sent if pairwise-id isn\'t sent) .',
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('CoCov2','cocov1-1');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('cocov2-2','cocov1-1',$singleTest);
      $IdPTest->testAttributes('CoCov2');
    }
    break;
  case 'cocov1-1' :
    $expected = $swamidIdp ?
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'norEduPersonNIN' => '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work after 1/9-2020.',
        'personalIdentityNumber' => 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution'
      ) :
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'eduPersonOrcid' => 'This attribute should only be released if and only if the IdP organization has retrived the ORCID iD via the ORCID Collect & Connect service. ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'schacHomeOrganizationType' => 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution'
      );
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov1-1',
      'GÉANT CoCo part 1, from SWAMID',
      'entityCategory',
      $expected,
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('CoCov1','cocov1-2');
    } else {
      $IdPTest->showHeaders();
      if ($swamidIdp) {
        $IdPTest->showTestHeaders('cocov2-3','cocov1-2',$singleTest);
      } else {
        $IdPTest->showTestHeaders('cocov2-2','cocov1-2',$singleTest);
      }
      $IdPTest->testAttributes('CoCov1');
    }
    break;
  case 'cocov1-2' :
    //Test4
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov1-2',
      'GÉANT CoCo part 2, from SWAMID',
      'entityCategory',
      array (
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
        'schacHomeOrganization' => 'Specifies a person\'s home organization using the domain name of the organization'
      ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      if ($singleTest) {
        $IdPTest->testAttributes('CoCov1','cocov1-3');
      } else {
        $IdPTest->testAttributes('CoCov1','rands');
      }
    } else {
      $IdPTest->showHeaders();
      if ($swamidIdp) {
        $IdPTest->showTestHeaders('cocov1-1','cocov1-3',$singleTest);
      } else {
        $IdPTest->showTestHeaders('cocov1-1','rands',$singleTest);
      }
      $IdPTest->testAttributes('CoCov1');
    }
    break;
  case 'cocov1-3' :
    // Test5
    $IdPTest =  new IdPCheck(
      $basename,
      'cocov1-3',
      'GÉANT CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
      'entityCategory',
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'displayName' => 'givenName + sn',
        'cn' => 'givenName + sn',
        'givenName' => 'Firstname',
        'schacDateOfBirth' => '8 digit date of birth (YYYYMMDD)',
        'sn' => 'Lastname',
        'mail' => 'Mailaddress'
      ),
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('CoCov1','rands');
    } else {
      $IdPTest->showHeaders();
      $IdPTest->showTestHeaders('cocov1-2','rands',$singleTest);
      $IdPTest->testAttributes('CoCov1');
    }
    break;
  case 'rands' :
    $expected = $swamidIdp ?
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'mail' => 'R&S require mailaddress',
        'displayName' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ) :
      array (
        'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
        'mail' => 'R&S require mailaddress',
        'displayName' => 'givenName + sn',
        'givenName' => 'Firstname',
        'sn' => 'Lastname',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      );
    $IdPTest =  new IdPCheck(
      $basename,
      'rands',
      'REFEDS R&S',
      'entityCategory',
      $expected,
      array (
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more',
        'eduPersonTargetedID' => 'For R&S release only if eduPersonPrincipalName is reassignable',
        'eduPersonUniqueID' => 'A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.'
      )
    );
    if ($quickTest) {
      $IdPTest->testAttributes('R&S','result');
    } else {
      $IdPTest->showHeaders();
      if ($swamidIdp) {
        $IdPTest->showTestHeaders('cocov1-3','result',$singleTest);
      } else {
        $IdPTest->showTestHeaders('cocov1-2','result',$singleTest);
      }
      $IdPTest->testAttributes('R&S');
    }
    break;
  case 'esi' :
    $IdPTest =  new IdPCheck(
      $basename,
      'esi',
      'SWAMID Entity Category Release Check - European Student Identifier',
      'esi',
      array (
        'schacPersonalUniqueCode' => 'Usually used within SWAMID for the European Student Identifier.',
        'eduPersonScopedAffiliation' => 'eduPersonAffiliation, scoped',
      ),
      array (
        'eduPersonAffiliation' => 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
        'persistent-id' => 'Should not be sent by default any more',
        'transient-id' => 'Should not be sent by default any more'
      )
    );
    $IdPTest->showHeaders();
    $IdPTest->showTestHeaders('','result',$singleTest);
    $IdPTest->testAttributes('ESI');
    break;
  case 'mfa' :
    $IdPTest =  new IdPCheck(
      $basename,
      'mfa',
      'SWAMID MFA Check',
      'mfa',
      $swamidIdp ?
        array (
          'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
          'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
        ) :
        array (
          'eduPersonPrincipalName' => 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
          'eduPersonAssurance' => 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level.',
        ),
      array (
      )
    );
    $IdPTest->showHeaders();
    if (isset($_GET['forceAuthn'])) {
      $IdPTest->showTestHeaders('mfa','result',$singleTest);
    } else {
      $IdPTest->showTestHeaders('','mfa',$singleTest,true);
    }
    $IdPTest->testAttributes('MFA');
    break;

  default:
    print "Okänd test : $test";
    exit;
}
if (!$quickTest) {
  print "  </div>\n</body>\n</html>";
}

