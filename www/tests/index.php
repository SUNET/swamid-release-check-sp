<?php
####### BEGIN Config #######

$basename='.release-check.swamid.se';

####### END Config #########


require_once('IdPCheck.php');
$test = str_replace($basename,'',strtolower($_SERVER['HTTP_HOST']));
switch ($test) {
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('','assurance');
		$IdPTest->testAttributes('');
		break;
	case 'assurance' :
		$IdPTest =  new IdPCheck('raf',
			'SWAMID Entity Category Release Check - RAF',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'eduPersonAssurance'	=> 'User assurance information. SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level. Furthermore, REFEDS Assurance Framework information should be released based on SWAMID Assurance level for the user.',
			),
			array (
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('noec','rands');
		$IdPTest->testAttributes('RAF');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('assurance','cocov1-1');
		$IdPTest->testAttributes('R&S');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('','pseudonymous');
		$IdPTest->testAttributes('anonymous');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('anonymous','personalized');
		$IdPTest->testAttributes('pseudonymous');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('pseudonymous','result');
		$IdPTest->testAttributes('personalized');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('rands','cocov1-2');
		$IdPTest->testAttributes('CoCo');
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('cocov1-1','cocov1-3');
		$IdPTest->testAttributes('CoCo');
		break;
	case 'cocov1-3' :
		// Test5
		$IdPTest =  new IdPCheck('cocov1-3',
			'GÉANT CoCo, from outside SWAMID',
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
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('cocov1-2','result');
		$IdPTest->testAttributes('CoCo');
		break;
	case 'cocov2-1' :
		// Test3
		$IdPTest =  new IdPCheck('cocov2-1',
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
				'schacHomeOrganizationType'	=> 'example urn:schac:homeOrganizationType:eu:higherEducationInstitution',
				'pairwise-id' => 'Replacement for ePPN, uniq for each user/SP'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('cocov1-3','cocov2-2');
		$IdPTest->testAttributes('CoCo');
		break;
	case 'cocov2-2' :
		//Test4
		$IdPTest =  new IdPCheck('cocov2-2',
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
				'schacHomeOrganization'	=> 'Specifies a person\'s home organization using the domain name of the organization',
				'subject-id' => 'Replacement for ePPN, uniq for each user. Same for each user on all SP:s'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more'
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('cocov2-1','cocov2-3');
		$IdPTest->testAttributes('CoCo');
		break;
	case 'cocov2-3' :
		// Test5
		$IdPTest =  new IdPCheck('cocov2-3',
			'GÉANT CoCo, from outside SWAMID',
			'entityCategory',
			array (
				'eduPersonPrincipalName'	=> 'A scoped identifier for a person. It should be represented in the form "user@scope" where \'user\' is a name-based identifier for the person and where the "scope" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.',
				'displayName'	=> 'givenName + sn',
				'cn'	=> 'givenName + sn',
				'givenName'	=> 'Firstname',
				'schacDateOfBirth'	=> '8 digit date of birth (YYYYMMDD)',
				'sn'	=> 'Lastname',
				'mail'	=> 'Mailaddress',
				'pairwise-id' => 'Replacement for ePPN, uniq for each user/SP'
			),
			array (
				'persistent-id'	=> 'Should not be sent by default any more',
				'transient-id'	=> 'Should not be sent by default any more',
				'subject-id' => 'Sould not be sent, SWAMID recommends sending pairwise-id if subject-id:req = any'
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders('cocov2-2','result');
		$IdPTest->testAttributes('CoCo');
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
		$IdPTest->showTestHeaders('','result');
		$IdPTest->testAttributes('ESI');
		break;
	case 'mfa' :
	default:
		print "Okänd test : $test";
		exit;
}
?>
</div>
</body>
</html>
