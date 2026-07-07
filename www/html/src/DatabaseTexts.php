<?php

#IdPCheck.php:239: #status_ERROR
_('The IDP has sent too many attributes.');
#IdPCheck.php:252: #status_ERROR
_('Received multi-value for %s, should be single-value!');
#IdPCheck.php:260: #status_WARNING
_('The IDP has not sent all the expected attributes. See the comments below.');
#IdPCheck.php:297: #status_OK
#IdPCheck.php:299: #testResult
_('Did not send any attributes that were not requested.');
#IdPCheck.php:388: #status_ERROR
_('Received multi-value for %s, should be single-value!');

#IdPCheck.php:570: #status_WARNING
_('R&S requires displayName or givenName + sn.');
#IdPCheck.php:576: #status_WARNING
_('R&S requires mail.');
#IdPCheck.php:580: #status_WARNING
_('R&S requires eduPersonPrincipalName.');
#IdPCheck.php:583: #status_OK
_('All the attributes required to fulfil R&S were sent.');
#IdPCheck.php:585: #testResult
_('R&S attributes OK, Entity Category Support OK');
#IdPCheck.php:587: #testResult
_('R&S attributes OK, Entity Category Support missing');
#IdPCheck.php:588: #status_WARNING
_("The IdP supports R&S but doesn't announce it in its metadata.");
#IdPCheck.php:589: #status_WARNING
_("Please add '[[EC_RANDS]]' to the list of supported ECs in metadata");
#IdPCheck.php:593: #testResult
_('R&S attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:594: #status_ERROR
_('The IdP does NOT support R&S but it claims that it does in its metadata!!');
#IdPCheck.php:596: #testResult
_('R&S attribute missing, Entity Category Support missing');

#IdPCheck.php:615: #status_WARNING
_('Anonymous requires schacHomeOrganization.');
#IdPCheck.php:620: #status_WARNING
_('Anonymous requires eduPersonScopedAffiliation.');
#IdPCheck.php:624: #status_OK
_('All the attributes required to fulfil Anonymous were sent.');
#IdPCheck.php:626: #testResult
_('Anonymous attributes OK, Entity Category Support OK');
#IdPCheck.php:628: #testResult
_('Anonymous attributes OK, Entity Category Support missing');
#IdPCheck.php:629: #status_WARNING
_("The IdP supports Anonymous but doesn't announce it in its metadata");
#IdPCheck.php:630: #status_WARNING
_("Please add '[[EC_ANON]]' to the list of supported ECs in metadata");
#IdPCheck.php:634: #testResult
_('Anonymous attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:635: #status_ERROR
_('The IdP does NOT support Anonymous but it claims that it does in its metadata!!');
#IdPCheck.php:637: #testResult
_('Anonymous attribute missing, Entity Category Support missing');

#IdPCheck.php:655: #status_WARNING
_('Pseudonymous requires eduPersonAssurance.');
#IdPCheck.php:673: #status_WARNING
#IdPCheck.php:745: #status_WARNING
_('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/IAP/low');
_('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ID/unique');
_('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ID/eppn-unique-no-reassign');
_('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ATP/ePA-1m');
#IdPCheck.php:677: #status_WARNING
_('Pseudonymous requires that eduPersonAssurance at least contains [[RAF_ASSURANCE]]');
#IdPCheck.php:682: #status_WARNING
_('Pseudonymous requires pairwise-id.');
#IdPCheck.php:687: #status_WARNING
_('Pseudonymous requires schacHomeOrganization.');
#IdPCheck.php:692: #status_WARNING
_('Pseudonymous requires eduPersonScopedAffiliation.');
#IdPCheck.php:696: #status_OK
_('All the attributes required to fulfil Pseudonymous were sent.');
#IdPCheck.php:698: #testResult
_('Pseudonymous attributes OK, Entity Category Support OK');
#IdPCheck.php:700: #testResult
_('Pseudonymous attributes OK, Entity Category Support missing');
#IdPCheck.php:701: #status_WARNING
_("The IdP supports Pseudonymous but doesn't announce it in its metadata.");
#IdPCheck.php:702: #status_WARNING
_("Please add '[[EC_PANON]]' to the list of supported ECs in metadata");
#IdPCheck.php:706: #testResult
_('Pseudonymous attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:707: #status_ERROR
_('The IdP does NOT support Pseudonymous but it claims that it does in its metadata!!');
#IdPCheck.php:709: #testResult
_('Pseudonymous attribute missing, Entity Category Support missing');

#IdPCheck.php:727: #status_WARNING
_('Personalized requires eduPersonAssurance.');
#IdPCheck.php:749: #status_WARNING
_('Personalized requires that eduPersonAssurance at least contains [[RAF_ASSURANCE]]');
#IdPCheck.php:755: #status_WARNING
_('Personalized requires displayName, givenName and sn.');
#IdPCheck.php:760: #status_WARNING
_('Personalized requires mail.');
#IdPCheck.php:765: #status_WARNING
_('Personalized requires subject-id.');
#IdPCheck.php:770: #status_WARNING
_('Personalized requires schacHomeOrganization.');
#IdPCheck.php:775: #status_WARNING
_('Personalized requires eduPersonScopedAffiliation.');
#IdPCheck.php:779: #status_OK
_('All the attributes required to fulfil Personalized were sent.');
#IdPCheck.php:781: #testResult
_('Personalized attributes OK, Entity Category Support OK');
#IdPCheck.php:783: #testResult
_('Personalized attributes OK, Entity Category Support missing');
#IdPCheck.php:784: #status_WARNING
_("The IdP supports Personalized but doesn't announce it in its metadata.");
#IdPCheck.php:785: #status_WARNING
_("Please add '[[EC_PERS]]' to the list of supported ECs in metadata");
#IdPCheck.php:789: #testResult
_('Personalized attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:790: #status_ERROR
_('The IdP does NOT support Personalized but it claims that it does in its metadata!!');
#IdPCheck.php:792: #testResult
_('Personalized attribute missing, Entity Category Support missing');

#IdPCheck.php:811: #status_ERROR
_('The IDP has not sent any attributes.');
#IdPCheck.php:813: #status_ERROR
_('The IDP has sent less than minumum numer of attributes for this test.');
#IdPCheck.php:830: #status_OK
_('Fulfils Code of Conduct');
#IdPCheck.php:832: #testResult
_('CoCo OK, Entity Category Support OK');
#IdPCheck.php:834: #testResult
_('CoCo OK, Entity Category Support missing');
#IdPCheck.php:835: #status_WARNING
_("The IdP supports CoCo but doesn't announce it in its metadata.");
#IdPCheck.php:836: #status_WARNING
_("Please add '[[EC_COCO1]]' to the list of supported ECs in metadata");
_("Please add '[[EC_COCO2]]' to the list of supported ECs in metadata");
#IdPCheck.php:840: #testResult
_('CoCo is not supported, BUT Entity Category Support is claimed');
#IdPCheck.php:841: #status_ERROR
_('The IdP does NOT support CoCo but it claims that it does in its metadata!!');
#IdPCheck.php:843: #testResult
_('Support for CoCo missing, Entity Category Support missing');

#IdPCheck.php:861: #testResult
#IdPCheck.php:882: #testResult
_('schacPersonalUniqueCode OK');
#IdPCheck.php:865: #status_WARNING
#IdPCheck.php:866: #status_WARNING
_("schacPersonalUniqueCode in wrong case. Not urn:schac:personalUniqueCode:int:esi. Might create problem in some SP's");
#IdPCheck.php:867: #testResult
_('schacPersonalUniqueCode OK. BUT wrong case');
#IdPCheck.php:870: #status_ERROR
_('schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:');
#IdPCheck.php:871: #testResult
_('schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:');
#IdPCheck.php:876: #status_WARNING
_('schacPersonalUniqueCode should only contain <b>one</b> value.');
#IdPCheck.php:878: #testResult
_('More than one schacPersonalUniqueCode');
#IdPCheck.php:885: #testResult
_('Missing schacPersonalUniqueCode');

#IdPCheck.php:998: #status_ERROR
_('Identity Provider is sending invalid Assurance information.');
#IdPCheck.php:999: #testResult
_('Sends invalid Assurance information.');
#IdPCheck.php:1001: #status_ERROR
_('Missing Assurance information. Expected at least [[RAF_ASSURANCE]]');
#IdPCheck.php:1002: #testResult
_('Missing [[RAF_ASSURANCE]] for user.');
#IdPCheck.php:1004: #status_WARNING
#IdPCheck.php:1005: #testResult
_('Missing some Assurance information.');
#IdPCheck.php:1007: #status_OK
_("Assurance attribute release for current user follows REFED's recommendations.");
#IdPCheck.php:1008: #testResult
_('Sends recommended Assurance information.');

#IdPCheck.php:1039: #status_ERROR
_("Authentication-instant hasn't updated after forceAuthn was requested.");
#IdPCheck.php:1076: #status_OK
_('Identity Provider supports requests with REFEDS MFA and ForceAuthn.');
#IdPCheck.php:1077: #testResult
_('Supports requests with no AuthnContextClassRef and ForceAuthn.');
_('Supports requests with PasswordProtectedTransport and ForceAuthn.');
_('Supports requests with REFEDS SFA and ForceAuthn.');
_('Supports requests with REFEDS MFA and ForceAuthn.');
#IdPCheck.php:1079: #status_ERROR
_('Identity Provider supports requests with REFEDS MFA but not ForceAuthn.');
#IdPCheck.php:1080: #testResult
_('Supports requests with no AuthnContextClassRef but not ForceAuthn.');
_('Supports requests with PasswordProtectedTransport but not ForceAuthn.');
_('Supports requests with REFEDS SFA but not ForceAuthn.');
_('Supports requests with REFEDS MFA but not ForceAuthn.');
#IdPCheck.php:1082: #status_OK
_('Identity Provider supports requests with no AuthnContextClassRef.');
_('Identity Provider supports requests with PasswordProtectedTransport.');
_('Identity Provider supports requests with REFEDS SFA.');
_('Identity Provider supports requests with REFEDS MFA.');
#IdPCheck.php:1083: #testResult
_('Supports requests with no AuthnContextClassRef.');
_('Supports requests with PasswordProtectedTransport.');
_('Supports requests with REFEDS SFA.');
_('Supports requests with REFEDS MFA.');
#IdPCheck.php:1087: #status_ERROR
_('Identity Provider does support ForceAuthn but not requests with REFEDS MFA.');
#IdPCheck.php:1088: #testResult
_('Does support ForceAuthn but not requests with no AuthnContextClassRef.');
_('Does support ForceAuthn but not requests with PasswordProtectedTransport.');
_('Does support ForceAuthn but not requests with REFEDS SFA.');
_('Does support ForceAuthn but not requests with REFEDS MFA.');
#IdPCheck.php:1090: #status_ERROR
_('Identity Provider does neither support requests with REFEDS MFA or ForceAuthn.');
#IdPCheck.php:1091: #testResult
_('Does neither support requests with no AuthnContextClassRef or ForceAuthn.');
_('Does neither support requests with PasswordProtectedTransport or ForceAuthn.');
_('Does neither support requests with REFEDS SFA or ForceAuthn.');
_('Does neither support requests with REFEDS MFA or ForceAuthn.');
#IdPCheck.php:1093: #status_ERROR
_('Identity Provider does not support requests with REFEDS MFA.');
#IdPCheck.php:1094: #testResult
_('Does not support requests with no AuthnContextClassRef.');
_('Does not support requests with PasswordProtectedTransport.');
_('Does not support requests with REFEDS SFA.');
_('Does not support requests with REFEDS MFA.');
# Other texts
# Display.php:28
_('Test not run yet');
