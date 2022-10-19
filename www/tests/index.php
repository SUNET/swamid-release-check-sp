<?php
####### BEGIN Config #######

$basename='.release-check.swamid.se';

####### END Config #########


require_once('IdPCheck.php');
$test = str_replace($basename,'',strtolower($_SERVER['HTTP_HOST']));
$quickTest = isset($_GET['quickTest']);
$singelTest = isset($_GET['singelTest']);
switch ($test) {
	case 'assurance' :
		$IdPTest =  new IdPCheck('assurance',
			'Assurance Attribute test',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
			),
			array (
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('RAF','noec');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('','noec',$singelTest);
			$IdPTest->testAttributes('RAF');
		}
		break;
	case 'noec' :
		// Test1
		$IdPTest =  new IdPCheck('noec',
			'No EC (shall not send any attributes!)',
			'entityCategory',
			array (),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('','anonymous');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('assurance','anonymous',$singelTest);
			$IdPTest->testAttributes('');
		}
		break;
	case 'anonymous' :
		$IdPTest =  new IdPCheck('anonymous',
			'REFEDS Anonymous Access',
			'entityCategory',
			array (
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('anonymous','pseudonymous');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('noec','pseudonymous',$singelTest);
			$IdPTest->testAttributes('anonymous');
		}
		break;
	case 'pseudonymous' :
		$IdPTest =  new IdPCheck('pseudonymous',
			'REFEDS Pseudonymous Access',
			'entityCategory',
			array (
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization',
				'pairwise-id'	=> 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('pseudonymous','personalized');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('anonymous','personalized',$singelTest);
			$IdPTest->testAttributes('pseudonymous');
		}
		break;
	case 'personalized' :
		$IdPTest =  new IdPCheck('personalized',
			'REFEDS Personalized Access',
			'entityCategory',
			array (
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization',
				'subject-id'	=> 'Its value for a given subject is independent of the relying party to whom it is given.',
				'displayName'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'mail'	=>	'Personalized require mailaddress',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('personalized','cocov2-1');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('pseudonymous','cocov2-1',$singelTest);
			$IdPTest->testAttributes('personalized');
		}
		break;
	case 'cocov2-1' :
		$IdPTest =  new IdPCheck('cocov2-1',
			'REFEDS CoCo part 1, from SWAMID',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'eduPersonOrcid'	=> 'ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
				'norEduPersonNIN'	=> '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work after 1/9-2020.',
				'personalIdentityNumber'	=> 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
				'schacDateOfBirth'	=> '8 digit date of birth (YYYYMMDD)',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
				'eduPersonAffiliation'	=> 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
				'schacHomeOrganizationType'	=> 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
				'pairwise-id'	=> 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov2','cocov2-2');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('personalized','cocov2-2',$singelTest);
			$IdPTest->testAttributes('CoCov2');
		}
		break;
	case 'cocov2-2' :
		$IdPTest =  new IdPCheck('cocov2-2',
			'REFEDS CoCo part 2, from SWAMID',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'mail'	=> 'Mailaddress',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'o'	=> 'Organisation name',
				'norEduOrgAcronym'	=> 'Shortform of organisation name',
				'c'	=> 'ISO_COUNTRY_CODE (se)',
				'co'	=> 'ISO_COUNTRY_NAME (Sweden)',
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization',
				'subject-id'	=> 'Its value for a given subject is independent of the relying party to whom it is given.'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov2','cocov2-3');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov2-1','cocov2-3',$singelTest);
			$IdPTest->testAttributes('CoCov2');
		}
		break;
	case 'cocov2-3' :
		$IdPTest =  new IdPCheck('cocov2-3',
			'REFEDS CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
			'entityCategory',
			array (
				'pairwise-id'	=> 'Its value for a given subject depends upon the relying party to whom it is given, thus preventing unrelated systems from using it as a basis for correlation.',
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'schacDateOfBirth'	=> '8 digit date of birth (YYYYMMDD)',
				'sn'	=> 'Lastname',
				'mail'	=> 'Mailaddress'
			),
			array (
				'subject-id'	=> 'Its value for a given subject is independent of the relying party to whom it is given (not recomended for this test, but should be sent if pairwise-id isn\'t sent) .',
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov2','cocov1-1');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov2-2','cocov1-1',$singelTest);
			$IdPTest->testAttributes('CoCov2');
		}
		break;
	case 'cocov1-1' :
		// Test3
		$IdPTest =  new IdPCheck('cocov1-1',
			'GÉANT CoCo part 1, from SWAMID',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'eduPersonOrcid'	=> 'ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.',
				'norEduPersonNIN'	=> '12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work after 1/9-2020.',
				'personalIdentityNumber'	=> 'Swedish 12 digit Socialsecuritynumber. Same as in passport',
				'schacDateOfBirth'	=> '8 digit date of birth (YYYYMMDD)',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
				'eduPersonAffiliation'	=> 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
				'schacHomeOrganizationType'	=> 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov1','cocov1-2');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov2-3','cocov1-2',$singelTest);
			$IdPTest->testAttributes('CoCov1');
		}
		break;
	case 'cocov1-2' :
		//Test4
		$IdPTest =  new IdPCheck('cocov1-2',
			'GÉANT CoCo part 2, from SWAMID',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'mail'	=> 'Mailaddress',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'o'	=> 'Organisation name',
				'norEduOrgAcronym'	=> 'Shortform of organisation name',
				'c'	=> 'ISO_COUNTRY_CODE (se)',
				'co'	=> 'ISO_COUNTRY_NAME (Sweden)',
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov1','cocov1-3');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov1-1','cocov1-3',$singelTest);
			$IdPTest->testAttributes('CoCov1');
		}
		break;
	case 'cocov1-3' :
		// Test5
		$IdPTest =  new IdPCheck('cocov1-3',
			'GÉANT CoCo, from outside SWAMID (requests civic number (personnummer) but this SHOULD NOT be released)',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'schacDateOfBirth'	=> '8 digit date of birth (YYYYMMDD)',
				'sn'	=> 'Lastname',
				'mail'	=> 'Mailaddress'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('CoCov1','rands');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov1-2','rands',$singelTest);
			$IdPTest->testAttributes('CoCov1');
		}
		break;
	case 'rands' :
		//Test2
		$IdPTest =  new IdPCheck('rands',
			'REFEDS R&S',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'mail'	=>	'R&S require mailaddress',
				'displayName'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'sn'	=> 'Lastname',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more',
				'eduPersonTargetedID'	=> 'For R&S release only if eduPersonPrincipalName is reassignable',
				'eduPersonUniqueID'	=> 'A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.'
			)
		);
		if ($quickTest) {
			$IdPTest->testAttributes('R&S','result');
		} else {
			$IdPTest->showHeaders();
			$IdPTest->showTestHeaders('cocov1-3','result',$singelTest);
			$IdPTest->testAttributes('R&S');
		}
		break;
	case 'esi' :
		$IdPTest =  new IdPCheck(
			'esi',
			'SWAMID Entity Category Release Check - European Student Identifier',
			'esi',
			array (
				'schacPersonalUniqueCode'	=> 'Usually used within SWAMID for the European Student Identifier.',
				'eduPersonScopedAffiliation'	=> 'eduPersonAffiliation, scoped',
			),
			array (
				'eduPersonAffiliation'	=> 'Specifies the person\'s relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.',
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('','result',$singelTest);
		$IdPTest->testAttributes('ESI');
		break;
	case 'mfa' :
	default:
		print "Okänd test : $test";
		exit;
}
if (!$quickTest) {
	print "</div>
	</body>
	</html>
	";
}

