<?php

//Load composer's autoloader
require_once 'vendor/autoload.php';
$config = new \releasecheck\Configuration();

$html = $config->getExtendedClass('HTML');
$html->showHTMLHead();
$html->showContentHeader();

$errorURL = "";
if (isset($_GET['errorURL']) && filter_var($_GET['errorURL'], FILTER_VALIDATE_URL)) {
  $errorURL = sprintf(_('For more info visit this <a href="%s">support-page</a>.'), $_GET['errorURL']);
}

$errorURL = str_replace(array('ERRORURL_TS'), array(time()), $errorURL);
$errorURL = isset($_GET['RelayState']) ?
  str_replace(array('ERRORURL_RP'), array($_GET['RelayState'] . 'shibboleth'), $errorURL) : $errorURL;
$errorURL = isset($_SERVER['Shib-Session-ID']) ?
  str_replace(array('ERRORURL_TID'), array($_SERVER['Shib-Session-ID']), $errorURL) : $errorURL;

switch ($_GET['errorType']) {
  case 'opensaml::saml2md::MetadataException':
    showMetadataException();
    break;
  case 'opensaml::FatalProfileException':
    if (
      $_GET['eventType'] == 'Login' &&
      $_GET['statusCode'] == 'urn:oasis:names:tc:SAML:2.0:status:Responder' &&
      isset($_GET['statusCode2']) &&
      $_GET['statusCode2'] == 'urn:oasis:names:tc:SAML:2.0:status:NoAuthnContext'
    ) {
      $errorURL = str_replace(
        array('ERRORURL_CODE', 'ERRORURL_CTX'),
        array('AUTHENTICATION_FAILURE', 'https://refeds.org/profile/mfa'),
        $errorURL
      );
    }
    showFatalProfileException();
    break;
  default:
    showInfo();
} ?>
  </div><!-- End container-->
</body>
</html>

<?php
function showMetadataException()
{
  $mail = str_replace('mailto:', '', $_GET['contactEmail']);?>
    <h1><?= _('Unknown Identity Provider') ?></h1>
    <p><?= _('To report this problem, please contact the site administrator at') ?>
      <a href="mailto:<?= htmlspecialchars($mail) ?>"><?= htmlspecialchars($mail) ?></a>.
    </p>
    <p><?= _('Please include the following error message in any email') ?>:</p>
    <p class="error"><?= _('Identity provider lookup failed at') ?>(<?=htmlspecialchars($_GET['requestURL'])?>)</p>
    <p><strong><?=  _('EntityID') ?>:</strong> <?=htmlspecialchars($_GET['entityID'])?></p>
    <p><?=htmlspecialchars($_GET['errorType'])?>: <?=htmlspecialchars($_GET['errorText'])?></p>
<?php }

function showFatalProfileException()
{
  global $errorURL;
  $mail = str_replace('mailto:', '', $_GET['contactEmail']);?>
    <h1><?= _('Unusable Identity Provider') ?></h1>
    <p><?= _('The identity provider supplying your login credentials does' .
      ' not support the necessary capabilities.') ?></p>
    <p><?= _('To report this problem, please contact the IdP administrator.') ?> <?=$errorURL?><br>
    <?= _('If your are the IdP administrator you can reach out to') ?>
    <a href="mailto:<?= htmlspecialchars($mail) ?>"><?= htmlspecialchars($mail) ?></a>.
    </p>
    <p><?= _('Please include the following error message in any email') ?>:</p>
    <p class="error"><?= _('Identity provider lookup failed at') ?> (<?=htmlspecialchars($_GET['requestURL'])?>)</p>
    <p><strong><?=  _('EntityID') ?>:</strong> <?=htmlspecialchars($_GET['entityID'])?></p>
    <p><?=htmlspecialchars($_GET['errorType'])?>: <?=htmlspecialchars($_GET['errorText'])?></p><?php
    print isset($_GET['statusCode']) ? "\n<p>statusCode : " . htmlspecialchars($_GET['statusCode']) . '</p>' : '';
    print isset($_GET['statusCode2']) ? "\n<p>statusCode2 : " . htmlspecialchars($_GET['statusCode2']) . '</p>' : '';
    print isset($_GET['statusMessage']) ?
      "\n<p>statusMessage : " . htmlspecialchars($_GET['statusMessage']) . '</p>' : '';
}

function showInfo()
{
  ?>
    <table>
      <caption><?= _('Values') ?></caption>
      <tr><th><?= _('Key') ?></th><th><?= _('Value') ?></th></tr>
    <?php
    foreach ($_GET as $key => $value) {
      printf('<tr><td>%s = %s</td></tr>%s', htmlspecialchars($key), htmlspecialchars($value), "\n");
    }
    print "</table>";
    ?>
<?php }
