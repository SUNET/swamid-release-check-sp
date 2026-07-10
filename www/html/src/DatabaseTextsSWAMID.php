<?php

#IdPCheckSWAMID.php:48: #status_ERROR
print _('schacPersonalUniqueCode should not announce SE. Use ladok.se / eduid.se or &lt;sHO&gt;.se');
#IdPCheckSWAMID.php:49: testResult
print _('schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:');
#IdPCheckSWAMID.php:60: #status_ERROR
print _('schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:');
#IdPCheckSWAMID.php:192: #status_ERROR
print _('Identity Provider is not approved for any SWAMID Identity Assurance' .
  ' Profiles but sends Assurance information!.');
#IdPCheckSWAMID.php:193: testResult
print _('Assurance Profile missing. Sends Assurance information!');
#IdPCheckSWAMID.php:195: #status_ERROR
print _('Identity Provider is not approved for any SWAMID Identity Assurance Profiles.');
#IdPCheckSWAMID.php:196: testResult
print _('Assurance Profile missing.');
#IdPCheckSWAMID.php:234: #status_ERROR
print _('Identity Provider is sending invalid Assurance information.');
#IdPCheckSWAMID.php:235: testResult
print _('Have Assurance Profile. Sends invalid Assurance information.');
#IdPCheckSWAMID.php:238: #status_ERROR
print _('Missing Assurance information. Expected at least [[SWAMID_ASSURANCE]]/al1');
#IdPCheckSWAMID.php:239: testResult
print _('Have Assurance Profile. Missing [[SWAMID_ASSURANCE]]/al1 for user.');
#IdPCheckSWAMID.php:243: testResult
print _('Have Assurance Profile. Missing some Assurance information.');
#IdPCheckSWAMID.php:247: testResult
print _('Have Assurance Profile. Sends recommended Assurance information.');

#IdPCheckSWAMID.php:233: status_OK
#IdPCheckSWAMID.php:237: status_OK
#IdPCheckSWAMID.php:241: status_OK
print _('Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.');
#IdPCheckSWAMID.php:245: status_OK
#IdPCheckSWAMID.php:246: status_OK
print _("Identity Provider is approved for at least one SWAMID Identity Assurance Profiles'" .
" and attribute release for current user follows SWAMID's recommendations.");
