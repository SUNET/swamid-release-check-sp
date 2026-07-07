<?php

const HTML_NO_RUN = 'no run';

//Load composer's autoloader
require_once '../vendor/autoload.php';
$config = new \releasecheck\Configuration();

$html = $config->getExtendedClass('HTML');
$admin = $config->getExtendedClass('Admin');

$collapseIcons = array();

$tab = isset($_GET['tab']) ? $_GET['tab'] : '';

$html->showHTMLHead();
$html->showContentHeader();

if (! $admin->checkAccess()) {
    print '<h1>' . _('No access') . '</h1>';
    $html->showContentFooter();
    $html->showScripts();
    exit;
}
printf('    <div class="row">
      <div class="col">%s', "\n");
$admin->showNavTabs($tab);
printf(
  '      </div>
      <div class="col-4 text-right">
        <a href=".">
          <button type="button" class="btn btn-primary">' . _('Back') . '</button>
        </a>
      </div>
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
        if (isset($_GET['idp'])) {
          $displayName = isset($_SERVER['Meta-displayName']) ? $_SERVER['Meta-displayName'] : '';
          $display = new \releasecheck\Display();
          $testrun = $display->getTestruns($_GET['idp'], 'entityCategory');
          printf(
            '        <h3>' . _('Result for') . ' %s (%s)%s</h3>%s',
            $displayName,
            htmlspecialchars($_GET['idp']),
            $testrun['time'] == HTML_NO_RUN ? '' : ' (' . $testrun['time'] . ')',
            "\n"
          );
          $display->showResultsECTests($_GET['idp'], $testrun);
        }
        break;
      default:
    }
  }
  $html->addTableSort('resultTable');
}

$html->showContentFooter();
$html->showScripts();
