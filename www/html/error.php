<?php
include ("config.php");
include ("include/header.php");

$errorURL = isset($_GET['errorURL']) ? 'For more info visit this <a href="' . $_GET['errorURL'] . '">support-page</a>.' : '';
$errorURL = str_replace(array('ERRORURL_TS'), array(time()), $errorURL);
$errorURL = isset($_GET['RelayState']) ? str_replace(array('ERRORURL_RP'), array($_GET['RelayState'].'shibboleth'), $errorURL) : $errorURL;
$errorURL = isset($_SERVER['Shib-Session-ID']) ? str_replace(array('ERRORURL_TID'), array($_SERVER['Shib-Session-ID']), $errorURL) : $errorURL;


switch ($_GET['errorType']) {
  case 'opensaml::saml2md::MetadataException' :
    showMetadataException();
    break;
  case 'opensaml::FatalProfileException' :
    if ($_GET['eventType'] == 'Login' &&
      $_GET['statusCode'] == 'urn:oasis:names:tc:SAML:2.0:status:Responder' &&
      isset($_GET['statusCode2']) &&
      $_GET['statusCode2'] == 'urn:oasis:names:tc:SAML:2.0:status:NoAuthnContext') {
                //case 'urn:oasis:names:tc:SAML:2.0:status:AuthnFailed' :
                //case 'urn:oasis:names:tc:SAML:2.0:status:NoPassive' :
                //case 'urn:oasis:names:tc:SAML:2.0:status:RequestDenied' :
      $errorURL = str_replace(array('ERRORURL_CODE', 'ERRORURL_CTX'),
        array('AUTHENTICATION_FAILURE', 'https://refeds.org/profile/mfa'), $errorURL);
    }
    showFatalProfileException();
    break;
  default :
    showInfo();
} ?>
  </div><!-- End container-->
</body>
</html>

<?php
function showMetadataException() {?>
    <h1>Unknown Identity Provider</h1>
    <p>To report this problem, please contact the site administrator at
    <a href="mailto:operations@swamid.se">operations@swamid.se</a>.
    </p>
    <p>Please include the following error message in any email:</p>
    <p class="error">Identity provider lookup failed at (<?=htmlspecialchars($_GET['requestURL'])?>)</p>
    <p><strong>EntityID:</strong> <?=htmlspecialchars($_GET['entityID'])?></p>
    <p><?=htmlspecialchars($_GET['errorType'])?>: <?=htmlspecialchars($_GET['errorText'])?></p>
<?php }

function showFatalProfileException() {
    global $errorURL;?>
    <h1>Unusable Identity Provider</h1>
    <p>The identity provider supplying your login credentials does not support the necessary capabilities.</p>
    <?=$_GET['requestURL'] == 'https://mfa.release-check.swamid.se/Shibboleth.sso/SAML2/POST' ? '<p>The MFA test service requires MFA signaling via REFEDS-MFA (https://refeds.org/profile/mfa).</p>' : '' ?>
    <p>To report this problem, please contact the IdP administrator. <?=$errorURL?><br>
    If your are the IdP administrator you can reach out to <a href="mailto:operations@swamid.se">operations@swamid.se</a>.
    </p>
    <p>Please include the following error message in any email:</p>
    <p class="error">Identity provider lookup failed at (<?=htmlspecialchars($_GET['requestURL'])?>)</p>
    <p><strong>EntityID:</strong> <?=htmlspecialchars($_GET['entityID'])?></p>
    <p><?=htmlspecialchars($_GET['errorType'])?>: <?=htmlspecialchars($_GET['errorText'])?></p><?php
    print isset($_GET['statusCode']) ? "\n<p>statusCode : " . htmlspecialchars($_GET['statusCode']) . '</p>' : '';
    print isset($_GET['statusCode2']) ? "\n<p>statusCode2 : " . htmlspecialchars($_GET['statusCode2']) . '</p>' : '';
 }

function showInfo() { ?>
    <table>
    <?php
    foreach ($_GET as $key => $value) {
      printf('<tr><td>%s = %s</td></tr>%s', $key, htmlspecialchars($value), "\n");
    }
    print "</table>";
    ?>
<?php }
