<?php

const HTML_NO_RUN = 'no run';

//Load composer's autoloader
require_once '../html/vendor/autoload.php';
$fedList = [];
$fullAdmin = false;
$extraContent = '';
require_once './fedlist.php';
$config = new \releasecheck\Configuration();

$federation = $config->getFederation();

$html = $config->getExtendedClass('HTML');
$admin = $config->getExtendedClass('Admin', '');
$collapseIcons = array();

$tab = isset($_GET['tab']) ? $_GET['tab'] : '';

$queryStr = '';
foreach (['tab', 'idp', 'id'] as $param) {
  $queryStr .= isset($_GET[$param]) ? sprintf('%s=%s&', $param, urlencode($_GET[$param])) : '';
}

if (isset($_SERVER['saml_entitlement'])) {
  foreach (explode(';', $_SERVER['saml_entitlement']) as $entitlement) {
    switch ($entitlement) {
      case 'urn:geant:eduteams.org:service:eduteams:group:eduGAIN:eduGAIN-OT#eduteams.org':
      case 'urn:geant:eduteams.org:service:eduteams:group:eduGAIN:eduGAIN-Support#eduteams.org':
      case 'urn:geant:eduteams.org:service:eduteams:group:eduGAIN:release-check-admin#eduteams.org':
        if (isset($_GET['show']) && $_GET['show'] == 'all') {
          $admin->setFullAdmin();
        }
        $fullAdmin = true;
        break;
      default:
        if (substr($entitlement, 0, 66) == 'urn:geant:eduteams.org:service:eduteams:group:eduGAIN:Federations:') {
          # Strip #eduteams.org from end
          # And get SWAMID from SWAMID:Delegates
          $fedparts = explode(':', substr($entitlement, 66, -13));
          /* array is loaded from fedlist.php
          @phpstan-ignoreisset.offset */
          if (isset($fedList[$fedparts[0]])) {
            $admin->addRegAuth($fedList[$fedparts[0]]);
          }
        }
    }
  }
  if ($fullAdmin) {
    $extraContent = sprintf(
      '%s            <li role="presentation" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"' .
      ' aria-expanded="false">
              %s
              <div class="dropdown-menu">
                <a class="dropdown-item" href=".?%sshow=all">%s</a>
                <a class="dropdown-item" href=".?%sshow=reg">%s</a>
              </div>
            </li>',
      "\n",
      _('Filter'),
      $queryStr,
      _('All registrationAuthorities'),
      $queryStr,
      _('Your registered registrationAuthorities')
    );
  }
}

$html->showHTMLHead();
$html->showContentHeader($queryStr, $extraContent);

if (! isset($_SERVER['Shib-Identity-Provider'])) {
  printf(
    '<h1>' . _('Not logged in') . '</h1>
  Please <a href="https://admin.%s/Shibboleth.sso/%s?entityID=https://proxy.eduteams.org/proxy">Login</a>',
    $config->basename(),
    $federation['LoginURL'],
  );
  $html->showContentFooter();
  $html->showScripts();
  exit;
}

printf('      <div class="row">
        <div class="col">%s', "\n");
$admin->showNavTabs($tab);
printf(
  '        </div>
      </div>%s',
  "\n"
);
if ($tab != '') {
  if (isset($admin->getTests()[$tab])) {
    $admin->showTab($tab);
  } else {
    switch ($tab) {
      case 'mfa':
        $admin->showMFA();
        break;
      case 'esi':
        $admin->showESI();
        break;
      case 'AllTests':
        if (isset($_GET['idp']) && $admin->haveAccess2Idp($_GET['idp'])) {
          $displayName = isset($_SERVER['Meta-displayName']) ? $_SERVER['Meta-displayName'] : '';
          $display = new \releasecheck\Display();
          $testrun = $display->getTestruns($_GET['idp'], 'AllTests', true);
          printf(
            '        <h3>' . _('Result for') . ' %s (%s)%s</h3>%s',
            $displayName,
            htmlspecialchars($_GET['idp']),
            $testrun['time'] == HTML_NO_RUN ? '' : ' (' . $testrun['time'] . ')',
            "\n"
          );
          $display->showResultsECTests($_GET['idp'], $testrun, false);
        }
        break;
      default:
    }
  }
  $html->addTableSort('resultTable');
} else {
  printf('      <div class="row">
        <div class="col">
          ' . _('You are registered for the following registrationAuthority') . ':
          <ul>%s', "\n");
  foreach ($admin->getRegAuth() as $regAuth) {
    printf('            <li>%s</li>%s', $regAuth, "\n");
  }
  printf('          </ul>%s', "\n");
  printf('        </div>
      </div>%s', "\n");
}

$html->showContentFooter();
$html->showScripts($collapseIcons);
