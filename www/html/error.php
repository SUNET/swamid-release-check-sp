<?php
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
		switch ($_GET['eventType']) {
			case 'Login' :
				switch ($_GET['statusCode']) {
					case 'urn:oasis:names:tc:SAML:2.0:status:Responder' : 
						if (isset($_GET['statusCode2'])) {
							switch ($_GET['statusCode2']) {
								//case 'urn:oasis:names:tc:SAML:2.0:status:AuthnFailed' :
								//case 'urn:oasis:names:tc:SAML:2.0:status:NoPassive' :
								case 'urn:oasis:names:tc:SAML:2.0:status:NoAuthnContext' :
								//case 'urn:oasis:names:tc:SAML:2.0:status:RequestDenied' :
									$errorURL = str_replace(array('ERRORURL_CODE', 'ERRORURL_CTX'), array('AUTHENTICATION_FAILURE', 'https://refeds.org/profile/mfa'), $errorURL);
									break;
							}
						} 
						break;
				}
				break;
		}
		showFatalProfileException();
        
		/*requestURL=https://release-check.swamid.se/Shibboleth.sso/SAML2/POST
errorType=opensaml::FatalProfileException
errorText=SAML response reported an IdP error.
RelayState=https://release-check.swamid.se/result
entityID=http://fs.bth.se/adfs/services/trust
errorURL=https://error.swamid.se/?errorurl_code&ERRORURL_CODE%26errorurl_ts&ERRORURL_TS%26errorurl_rp&ERRORURL_RP%26errorurl_tid&ERRORURL_TID%26errorurl_ctx&ERRORURL_CTX%26entityid&http://fs.bth.se/adfs/services/trust
eventType=Login
statusCode=urn:oasis:names:tc:SAML:2.0:status:Responder
statusCode2=urn:oasis:names:tc:SAML:2.0:status:RequestDenied
*/
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
    <p class="error">Identity provider lookup failed at (<?=$_GET['requestURL']?>)</p>
    <p><strong>EntityID:</strong> <?=$_GET['entityID']?></p>
    <p><?=$_GET['errorType']?>: <?=$_GET['errorText']?></p>
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
    <p class="error">Identity provider lookup failed at (<?=$_GET['requestURL']?>)</p>
    <p><strong>EntityID:</strong> <?=$_GET['entityID']?></p>
    <p><?=$_GET['errorType']?>: <?=$_GET['errorText']?></p><?php
    print isset($_GET['statusCode']) ? "\n<p>statusCode : " . $_GET['statusCode'] . '</p>' : '';
    print isset($_GET['statusCode2']) ? "\n<p>statusCode2 : " . $_GET['statusCode2'] . '</p>' : '';
    #showInfo();
 }

function showInfo() { ?>
    <table>
    <?php
    foreach ($_GET as $key => $value) {
            printf('<tr><td>%s = %s</td></tr>%s', $key, $value, "\n");
    }
    print "</table>";
    #print "<pre>";
    #print_r($_SERVER);
    #print "</pre>";
    ?>
<?php }