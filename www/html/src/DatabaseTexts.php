<?php

#IdPCheck.php:239: #status_ERROR
print _('The IDP has sent too many attributes.');
#IdPCheck.php:252: #status_ERROR
print _('Received multi-value for %s, should be single-value!');
#IdPCheck.php:260: #status_WARNING
print _('The IDP has not sent all the expected attributes. See the comments below.');
#IdPCheck.php:297: #status_OK
#IdPCheck.php:299: #testResult
print _('Did not send any attributes that were not requested.');
#IdPCheck.php:388: #status_ERROR
print _('Received multi-value for %s, should be single-value!');

#IdPCheck.php:570: #status_WARNING
print _('R&S requires displayName or givenName + sn.');
#IdPCheck.php:576: #status_WARNING
print _('R&S requires mail.');
#IdPCheck.php:580: #status_WARNING
print _('R&S requires eduPersonPrincipalName.');
#IdPCheck.php:583: #status_OK
print _('All the attributes required to fulfil R&S were sent.');
#IdPCheck.php:585: #testResult
print _('R&S attributes OK, Entity Category Support OK');
#IdPCheck.php:587: #testResult
print _('R&S attributes OK, Entity Category Support missing');
#IdPCheck.php:588: #status_WARNING
print _("The IdP supports R&S but doesn't announce it in its metadata.");
#IdPCheck.php:589: #status_WARNING
print _("Please add '[[EC_RANDS]]' to the list of supported ECs in metadata");
#IdPCheck.php:593: #testResult
print _('R&S attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:594: #status_ERROR
print _('The IdP does NOT support R&S but it claims that it does in its metadata!!');
#IdPCheck.php:596: #testResult
print _('R&S attribute missing, Entity Category Support missing');

#IdPCheck.php:615: #status_WARNING
print _('Anonymous requires schacHomeOrganization.');
#IdPCheck.php:620: #status_WARNING
print _('Anonymous requires eduPersonScopedAffiliation.');
#IdPCheck.php:624: #status_OK
print _('All the attributes required to fulfil Anonymous were sent.');
#IdPCheck.php:626: #testResult
print _('Anonymous attributes OK, Entity Category Support OK');
#IdPCheck.php:628: #testResult
print _('Anonymous attributes OK, Entity Category Support missing');
#IdPCheck.php:629: #status_WARNING
print _("The IdP supports Anonymous but doesn't announce it in its metadata");
#IdPCheck.php:630: #status_WARNING
print _("Please add '[[EC_ANON]]' to the list of supported ECs in metadata");
#IdPCheck.php:634: #testResult
print _('Anonymous attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:635: #status_ERROR
print _('The IdP does NOT support Anonymous but it claims that it does in its metadata!!');
#IdPCheck.php:637: #testResult
print _('Anonymous attribute missing, Entity Category Support missing');

#IdPCheck.php:655: #status_WARNING
print _('Pseudonymous requires eduPersonAssurance.');
#IdPCheck.php:673: #status_WARNING
#IdPCheck.php:745: #status_WARNING
print _('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/IAP/low');
print _('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ID/unique');
print _('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ID/eppn-unique-no-reassign');
print _('[[FED_NAME]] recommends that eduPersonAssurance contains [[RAF_ASSURANCE]]/ATP/ePA-1m');
#IdPCheck.php:677: #status_WARNING
print _('Pseudonymous requires that eduPersonAssurance at least contains [[RAF_ASSURANCE]]');
#IdPCheck.php:682: #status_WARNING
print _('Pseudonymous requires pairwise-id.');
#IdPCheck.php:687: #status_WARNING
print _('Pseudonymous requires schacHomeOrganization.');
#IdPCheck.php:692: #status_WARNING
print _('Pseudonymous requires eduPersonScopedAffiliation.');
#IdPCheck.php:696: #status_OK
print _('All the attributes required to fulfil Pseudonymous were sent.');
#IdPCheck.php:698: #testResult
print _('Pseudonymous attributes OK, Entity Category Support OK');
#IdPCheck.php:700: #testResult
print _('Pseudonymous attributes OK, Entity Category Support missing');
#IdPCheck.php:701: #status_WARNING
print _("The IdP supports Pseudonymous but doesn't announce it in its metadata.");
#IdPCheck.php:702: #status_WARNING
print _("Please add '[[EC_PANON]]' to the list of supported ECs in metadata");
#IdPCheck.php:706: #testResult
print _('Pseudonymous attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:707: #status_ERROR
print _('The IdP does NOT support Pseudonymous but it claims that it does in its metadata!!');
#IdPCheck.php:709: #testResult
print _('Pseudonymous attribute missing, Entity Category Support missing');

#IdPCheck.php:727: #status_WARNING
print _('Personalized requires eduPersonAssurance.');
#IdPCheck.php:749: #status_WARNING
print _('Personalized requires that eduPersonAssurance at least contains [[RAF_ASSURANCE]]');
#IdPCheck.php:755: #status_WARNING
print _('Personalized requires displayName, givenName and sn.');
#IdPCheck.php:760: #status_WARNING
print _('Personalized requires mail.');
#IdPCheck.php:765: #status_WARNING
print _('Personalized requires subject-id.');
#IdPCheck.php:770: #status_WARNING
print _('Personalized requires schacHomeOrganization.');
#IdPCheck.php:775: #status_WARNING
print _('Personalized requires eduPersonScopedAffiliation.');
#IdPCheck.php:779: #status_OK
print _('All the attributes required to fulfil Personalized were sent.');
#IdPCheck.php:781: #testResult
print _('Personalized attributes OK, Entity Category Support OK');
#IdPCheck.php:783: #testResult
print _('Personalized attributes OK, Entity Category Support missing');
#IdPCheck.php:784: #status_WARNING
print _("The IdP supports Personalized but doesn't announce it in its metadata.");
#IdPCheck.php:785: #status_WARNING
print _("Please add '[[EC_PERS]]' to the list of supported ECs in metadata");
#IdPCheck.php:789: #testResult
print _('Personalized attributes missing, BUT Entity Category Support claimed');
#IdPCheck.php:790: #status_ERROR
print _('The IdP does NOT support Personalized but it claims that it does in its metadata!!');
#IdPCheck.php:792: #testResult
print _('Personalized attribute missing, Entity Category Support missing');

#IdPCheck.php:811: #status_ERROR
print _('The IDP has not sent any attributes.');
#IdPCheck.php:813: #status_ERROR
print _('The IDP has sent less than minumum numer of attributes for this test.');
#IdPCheck.php:830: #status_OK
print _('Fulfils Code of Conduct');
#IdPCheck.php:832: #testResult
print _('CoCo OK, Entity Category Support OK');
#IdPCheck.php:834: #testResult
print _('CoCo OK, Entity Category Support missing');
#IdPCheck.php:835: #status_WARNING
print _("The IdP supports CoCo but doesn't announce it in its metadata.");
#IdPCheck.php:836: #status_WARNING
print _("Please add '[[EC_COCO1]]' to the list of supported ECs in metadata");
print _("Please add '[[EC_COCO2]]' to the list of supported ECs in metadata");
#IdPCheck.php:840: #testResult
print _('CoCo is not supported, BUT Entity Category Support is claimed');
#IdPCheck.php:841: #status_ERROR
print _('The IdP does NOT support CoCo but it claims that it does in its metadata!!');
#IdPCheck.php:843: #testResult
print _('Support for CoCo missing, Entity Category Support missing');

#IdPCheck.php:861: #testResult
#IdPCheck.php:882: #testResult
print _('schacPersonalUniqueCode OK');
#IdPCheck.php:865: #status_WARNING
#IdPCheck.php:866: #status_WARNING
print _("schacPersonalUniqueCode in wrong case. Not urn:schac:personalUniqueCode:int:esi. Might create problem in some SP's");
#IdPCheck.php:867: #testResult
print _('schacPersonalUniqueCode OK. BUT wrong case');
#IdPCheck.php:870: #status_ERROR
print _('schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:');
#IdPCheck.php:871: #testResult
print _('schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:');
#IdPCheck.php:876: #status_WARNING
print _('schacPersonalUniqueCode should only contain <b>one</b> value.');
#IdPCheck.php:878: #testResult
print _('More than one schacPersonalUniqueCode');
#IdPCheck.php:885: #testResult
print _('Missing schacPersonalUniqueCode');

#IdPCheck.php:998: #status_ERROR
print _('Identity Provider is sending invalid Assurance information.');
#IdPCheck.php:999: #testResult
print _('Sends invalid Assurance information.');
#IdPCheck.php:1001: #status_ERROR
print _('Missing Assurance information. Expected at least [[RAF_ASSURANCE]]');
#IdPCheck.php:1002: #testResult
print _('Missing [[RAF_ASSURANCE]] for user.');
#IdPCheck.php:1004: #status_WARNING
#IdPCheck.php:1005: #testResult
print _('Missing some Assurance information.');
#IdPCheck.php:1007: #status_OK
print _("Assurance attribute release for current user follows REFED's recommendations.");
#IdPCheck.php:1008: #testResult
print _('Sends recommended Assurance information.');

#IdPCheck.php:1039: #status_ERROR
print _("Authentication-instant hasn't updated after forceAuthn was requested.");
#IdPCheck.php:1076: #status_OK
print _('Identity Provider supports requests with REFEDS MFA and ForceAuthn.');
#IdPCheck.php:1077: #testResult
print _('Supports requests with no AuthnContextClassRef and ForceAuthn.');
print _('Supports requests with PasswordProtectedTransport and ForceAuthn.');
print _('Supports requests with REFEDS SFA and ForceAuthn.');
print _('Supports requests with REFEDS MFA and ForceAuthn.');
#IdPCheck.php:1079: #status_ERROR
print _('Identity Provider supports requests with REFEDS MFA but not ForceAuthn.');
#IdPCheck.php:1080: #testResult
print _('Supports requests with no AuthnContextClassRef but not ForceAuthn.');
print _('Supports requests with PasswordProtectedTransport but not ForceAuthn.');
print _('Supports requests with REFEDS SFA but not ForceAuthn.');
print _('Supports requests with REFEDS MFA but not ForceAuthn.');
#IdPCheck.php:1082: #status_OK
print _('Identity Provider supports requests with no AuthnContextClassRef.');
print _('Identity Provider supports requests with PasswordProtectedTransport.');
print _('Identity Provider supports requests with REFEDS SFA.');
print _('Identity Provider supports requests with REFEDS MFA.');
#IdPCheck.php:1083: #testResult
print _('Supports requests with no AuthnContextClassRef.');
print _('Supports requests with PasswordProtectedTransport.');
print _('Supports requests with REFEDS SFA.');
print _('Supports requests with REFEDS MFA.');
#IdPCheck.php:1087: #status_ERROR
print _('Identity Provider does support ForceAuthn but not requests with REFEDS MFA.');
#IdPCheck.php:1088: #testResult
print _('Does support ForceAuthn but not requests with no AuthnContextClassRef.');
print _('Does support ForceAuthn but not requests with PasswordProtectedTransport.');
print _('Does support ForceAuthn but not requests with REFEDS SFA.');
print _('Does support ForceAuthn but not requests with REFEDS MFA.');
#IdPCheck.php:1090: #status_ERROR
print _('Identity Provider does neither support requests with REFEDS MFA or ForceAuthn.');
#IdPCheck.php:1091: #testResult
print _('Does neither support requests with no AuthnContextClassRef or ForceAuthn.');
print _('Does neither support requests with PasswordProtectedTransport or ForceAuthn.');
print _('Does neither support requests with REFEDS SFA or ForceAuthn.');
print _('Does neither support requests with REFEDS MFA or ForceAuthn.');
#IdPCheck.php:1093: #status_ERROR
print _('Identity Provider does not support requests with REFEDS MFA.');
#IdPCheck.php:1094: #testResult
print _('Does not support requests with no AuthnContextClassRef.');
print _('Does not support requests with PasswordProtectedTransport.');
print _('Does not support requests with REFEDS SFA.');
print _('Does not support requests with REFEDS MFA.');
# Other texts
# Display.php:28
print _('Test not run yet');
