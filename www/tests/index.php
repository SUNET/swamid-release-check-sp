<?php
####### BEGIN Config #######

$basename='.release-check.swamid.se';

####### END Config #########


require_once("IdPCheck.php");
$test = str_replace($basename,'',strtolower($_SERVER['HTTP_HOST']));
switch ($test) {
	case 'noec' :
		// Test1
		$IdPTest =  new IdPCheck("noec",
			"No EC (shall not send any attributes!)",
			"entityCategory",
			array (),
			array (
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more"
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("","rands");
		$IdPTest->testAttributes("");
		break;
	case 'rands' :
		//Test2
		$IdPTest =  new IdPCheck("rands",
			"REFEDS R&S",
			"entityCategory",
			array (
				"eduPersonPrincipalName"	=> "A scoped identifier for a person. It should be represented in the form \"user@scope\" where 'user' is a name-based identifier for the person and where the \"scope\" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.",
				"mail"	=>	"R&S require mailaddress",
				"displayName"	=> "givenName + sn",
				"givenName"	=> "Firstname",
				"sn"	=> "Lastname",
				"eduPersonAssurance"	=> "AL level.SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level.",
				"eduPersonScopedAffiliation"	=> "eduPersonAffiliation, scoped",
			),
			array (
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more",
				"eduPersonTargetedID"	=> "For R&S release only if eduPersonPrincipalName is reassignable",
				"eduPersonUniqueID"	=> "A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications."
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("noec","cocov1-1");
		$IdPTest->testAttributes("R&S");
		break;
	case 'cocov1-1' :
		// Test3
		$IdPTest =  new IdPCheck("cocov1-1",
			"GÉANT CoCo part 1, from SWAMID",
			"entityCategory",
			array (
				"eduPersonPrincipalName"	=> "A scoped identifier for a person. It should be represented in the form \"user@scope\" where 'user' is a name-based identifier for the person and where the \"scope\" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.",
				"eduPersonOrcid"	=> "ORCID iDs are persistent digital identifiers for individual researchers. Their primary purpose is to unambiguously and definitively link them with their scholarly work products. ORCID iDs are assigned, managed and maintained by the ORCID organization.",
				"norEduPersonNIN"	=> "12 digit Socialsecuritynumber. Same as for example LADOK uses. Required for systems like LADOK to work after 1/9-2020.",
				"personalIdentityNumber"	=> "Swedish 12 digit Socialsecuritynumber. Same as in passport",
				"schacDateOfBirth"	=> "8 digit date of birth (YYYYMMDD)",
				"displayName"	=> "givenName + sn",
				"cn"	=> "givenName + sn",
				"givenName"	=> "Firstname",
				"sn"	=> "Lastname",
				"eduPersonAssurance"	=> "AL level.SWAMID Identity Assurance Profiles can only be asserted for a user if and only if both the organisation and the user is validated for the assurance level.",
				"eduPersonScopedAffiliation"	=> "eduPersonAffiliation, scoped",
				"eduPersonTargetedID"	=> "For Code of Conduct release only if eduPersonTargetedID is requested in metadata.",
				"eduPersonAffiliation"	=> "Specifies the person's relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.",
				"schacHomeOrganizationType"	=> "example urn:schac:homeOrganizationType:eu:higherEducationInstitution"
			),
			array (
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more",
				"eduPersonUniqueID"	=> "A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.",
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("rands","cocov1-2");
		$IdPTest->testAttributes("CoCo");
		break;
	case 'cocov1-2' :
		//Test4
		$IdPTest =  new IdPCheck("cocov1-2",
			"GÉANT CoCo part 2, from SWAMID",
			"entityCategory",
			array (
				"eduPersonPrincipalName"	=> "A scoped identifier for a person. It should be represented in the form \"user@scope\" where 'user' is a name-based identifier for the person and where the \"scope\" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.",
				"mail"	=> "Mailaddress",
				"displayName"	=> "givenName + sn",
				"cn"	=> "givenName + sn",
				"givenName"	=> "Firstname",
				"sn"	=> "Lastname",
				"eduPersonTargetedID"	=> "For Code of Conduct release only if eduPersonTargetedID is requested in metadata.",
				"o"	=> "Organisation name",
				"norEduOrgAcronym"	=> "Shortform of organisation name",
				"c"	=> "ISO_COUNTRY_CODE (se)",
				"co"	=> "ISO_COUNTRY_NAME (Sweden)",
				"schacHomeOrganization"	=> "Specifies a person´s home organization using the domain name of the organization"
			),
			array (
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more",
				"eduPersonUniqueID"	=> "A long-lived, non re-assignable, omnidirectional identifier suitable for use as a principal identifier by authentication providers or as a unique external key by applications.",
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("cocov1-1","cocov1-3");
		$IdPTest->testAttributes("CoCo");
		break;
	case 'cocov1-3' :
		// Test5
		$IdPTest =  new IdPCheck("cocov1-3",
			"GÉANT CoCo, from outside SWAMID",
			"entityCategory",
			array (
				"eduPersonPrincipalName"	=> "A scoped identifier for a person. It should be represented in the form \"user@scope\" where 'user' is a name-based identifier for the person and where the \"scope\" portion MUST be the administrative domain of the identity system where the identifier was created and assigned.",
				"displayName"	=> "givenName + sn",
				"cn"	=> "givenName + sn",
				"givenName"	=> "Firstname",
				"schacDateOfBirth"	=> "8 digit date of birth (YYYYMMDD)",
				"sn"	=> "Lastname",
				"mail"	=> "Mailaddress"
			),
			array (
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more"
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("cocov1-2","result");
		$IdPTest->testAttributes("CoCo");
		break;
	case 'esi' :
		$IdPTest =  new IdPCheck(
			"esi",
			"SWAMID Entity Category Release Check - European Student Identifier",
			"esi",
			array (
				"schacPersonalUniqueCode"	=> "Usually used within SWAMID for the European Student Identifier.",
				"eduPersonScopedAffiliation"	=> "eduPersonAffiliation, scoped",
			),
			array (
				"eduPersonAffiliation"	=> "Specifies the person's relationship(s) to the institution in broad categories such as student, faculty, staff, alum, etc.",
				"persistent-id"	=> "Should not be sent by default any more",
				"transient-id"	=> "Should not be sent by default any more"
			)
		);
		$IdPTest->showHeaders();
		$IdPTest->showTestHeaders("","result");
		$IdPTest->testAttributes("ESI");
		break;
	default:
		print "Okänd test : $test";
		exit;
}
?>
</div>
</body>
</html>
