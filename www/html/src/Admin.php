<?php

namespace releasecheck;

use PDO;

/**
 * Class collect funtions for admin interface
 */
class Admin
{
  /**
   * Configuration of application
   */
  protected Configuration $config;

  /**
   * List of IdPs
   *
   * key   = entityID
   * value = true if IdP have been tested
   */
  protected array $testedIPs;

  /**
   * Array of federation configuration
   */
  protected array $federation = array();

  protected const HTML_MORE_THAN_ONE_SCHAC = "More than one schacPersonalUniqueCode";
  protected const HTML_SHOW_ALL_IDPS = "Show all IdPs";

  /**
   * List of tests/tabs to display
   */
  protected $tests = array(
    'RandS' => array(
      'displayName' => 'R&S',
      'fullName' => 'R&S',
      'dbName' => 'rands',
      'expected' => array (
        'ePPN' => 'eduPersonPrincipalName',
        'mail' => 'mail',
        'displayName' => 'displayName',
        'givenName' => 'givenName',
        'sn' => 'sn',
      ),
      'testResults' => array(
        'OKOK' => 'R&S attributes OK, Entity Category Support OK',
        'OKFail' => 'R&S attributes OK, Entity Category Support missing',
        'Fail' => 'R&S attribute missing, Entity Category Support missing',
        'FailFail' => 'R&S attributes missing, BUT Entity Category Support claimed',
      ),
    ),
    'Anon' => array(
      'displayName' => 'Anon',
      'fullName' => 'Anonymous',
      'dbName' => 'anonymous',
      'expected' => array (
        'ePSA' => 'eduPersonScopedAffiliation',
        'sHO' => 'schacHomeOrganization',
      ),
      'testResults' => array(
        'OKOK' => 'Anonymous attributes OK, Entity Category Support OK',
        'OKFail' => 'Anonymous attributes OK, Entity Category Support missing',
        'Fail' => 'Anonymous attribute missing, Entity Category Support missing',
        'FailFail' => 'Anonymous attributes missing, BUT Entity Category Support claimed',
      ),
    ),
    'PAnon' => array(
      'displayName' => 'Panon',
      'fullName' => 'Pseudonymous',
      'dbName' => 'pseudonymous',
      'expected' => array (
        'pairwise-id' => 'pairwise-id',
        'ePA' => 'eduPersonAssurance',
        'ePSA' => 'eduPersonScopedAffiliation',
        'sHO' => 'schacHomeOrganization',
      ),
      'testResults' => array(
        'OKOK' => 'Pseudonymous attributes OK, Entity Category Support OK',
        'OKFail' => 'Pseudonymous attributes OK, Entity Category Support missing',
        'Fail' => 'Pseudonymous attribute missing, Entity Category Support missing',
        'FailFail' => 'Pseudonymous attributes missing, BUT Entity Category Support claimed',
      ),
    ),
    'Pers' => array(
      'displayName' => 'Pers',
      'fullName' => 'Personalized',
      'dbName' => 'personalized',
      'expected' => array (
        'subject-id' => 'subject-id',
        'mail' => 'mail',
        'displayName' => 'displayName',
        'givenName' => 'givenName',
        'sn' => 'sn',
        'ePA' => 'eduPersonAssurance',
        'ePSA' => 'eduPersonScopedAffiliation',
        'sHO' => 'schacHomeOrganization',
      ),
      'testResults' => array(
        'OKOK' => 'Personalized attributes OK, Entity Category Support OK',
        'OKFail' => 'Personalized attributes OK, Entity Category Support missing',
        'Fail' => 'Personalized attribute missing, Entity Category Support missing',
        'FailFail' => 'Personalized attributes missing, BUT Entity Category Support claimed',
      ),
    ),
    'CoCov2' => array(
      'displayName' => 'CoCov2',
      'fullName' => 'CoCov2',
      'dbName' => 'cocov2',
      'expected' => array (
        'givenName' => 'givenName',
        'sn' => 'sn',
        'mail' => 'mail',
      ),
      'testResults' => array(
        'OKOK' => 'CoCo OK, Entity Category Support OK',
        'OKFail' => 'CoCo OK, Entity Category Support missing',
        'Fail' => 'Support for CoCo missing, Entity Category Support missing',
        'FailFail' => 'CoCo is not supported, BUT Entity Category Support is claimed',
      ),
    ),
  );

  protected const HTML_ACTIVE = ' active';
  protected const HTML_CHECK_SP = 'check">   ';
  protected const HTML_EXCLAMATION_SP = 'exclamation"> ';
  protected const HTML_EXCLAMATION_TR_SP = 'exclamation-triangle">  ';

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    global $config;
    if (isset($config)) {
      $this->config = $config;
    } else {
      $this->config = new Configuration();
    }
    $this->federation = $this->config->getFederation();
    $this->getTestedIPs();
  }

  /**
   * Create a list of IdPs in federation
   *
   * @return void
   */
  protected function getTestedIPs()
  {
    if (isset($this->federation['metadataTool'])) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://' . $this->federation['metadataTool'] . '/api/v1/');
      curl_setopt($ch, CURLOPT_USERAGENT, 'Release-check');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_NOBODY, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      $res = curl_exec($ch);
      $data = json_decode($res, true, 4);
      foreach ($data['objects'] as $row) {
        $this->testedIPs[$row['entityID']] = false;
      }
    }
  }

  /**
   * Check if user have access to admin interface
   *
   * If you want another key tha subject-id create your own in Admin<extend> class
   */
  public function checkAccess()
  {
    return isset($_SERVER['saml_subject-id']) ?
      in_array($_SERVER['saml_subject-id'], $this->federation['adminUsers']) :
      in_array($_SERVER['saml_eduPersonPrincipalName'], $this->federation['adminUsers']);
  }

  /**
   * Show tab list
   *
   * @param string $tab that is active.
   *
   * @return void
   */
  public function showNavTabs($tab)
  {
    $idpParam = isset($_GET['idp']) ? '&idp=' . urlencode($_GET['idp']) : '';

    printf('        <ul class="nav nav-tabs">%s', "\n");
    foreach ($this->tests as $test => $data) {
      printf(
        '          <li class="nav-item">
            <a class="nav-link%s" href="?tab=%s%s">%s</a>
          </li>%s',
        $tab == $test ? self::HTML_ACTIVE : '',
        $test,
        $idpParam,
        $data['displayName'],
        "\n"
      );
    }
    printf(
      '          <li class="nav-item">
            <a class="nav-link%s" href="?tab=mfa%s">MFA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link%s" href="?tab=esi%s">ESI</a>
          </li>
        </ul>%s',
      $tab == 'mfa' ? self::HTML_ACTIVE : '',
      $idpParam,
      $tab == 'esi' ? self::HTML_ACTIVE : '',
      $idpParam,
      "\n"
    );
  }

  /**
   * Check if string is present in attribute or not
   *
   * @param string $string String to search for
   *
   * @param string $attribute Attribute value to search for string in
   *
   * @return bool
   */
  protected function sends($string, $attribute)
  {
    return strpos($string, $attribute) !== false;
  }

  /**
   * Show info tab for selected test
   *
   * @param string $tab Tab to show testresults for
   *
   * @return void
   */
  public function showTab($tab)
  {
    $selectedIdp = isset($_GET['idp']) ? $_GET['idp'] : false;
    printf('    <div class="row">
      <div class="col">%s', "\n");
    if ($selectedIdp) {
      printf(
        '      <h1>' . _('%s tests run by %s') . '</h1>
      <a href="./admin.php?tab=%s">
        <button type="button" class="btn btn-success">' . _(self::HTML_SHOW_ALL_IDPS) . '</button>
      </a>
      <a href="./admin.php?tab=AllTests&idp=%s">
        <button type="button" class="btn btn-success">' . _('Show all EC tests for this IdP') . '</button>
      </a>%s',
        $this->tests[$tab]['fullName'],
        htmlspecialchars($selectedIdp),
        $tab,
        urlencode($selectedIdp),
        "\n"
      );
    } else {
      printf(
        '      <h1>' . _('Data based on IdPs that have run %s test') . '</h1>%s',
        $this->tests[$tab]['fullName'],
        "\n"
      );
    }
    printf(
      '      <table class="table table-striped table-bordered">
          <tr>
            <td>' . _('%s data') . ' </td>
            <td>%s',
      $this->tests[$tab]['fullName'],
      "\n"
    );
    switch ($tab) {
      case 'CoCov1':
      case 'CoCov2':
        printf('              <i class="fas fa-check"> = ' . _('Only send reqested data or less') . '</i><br>
              <i class="fas fa-exclamation"> = ' . _('Send too much data') . '</i>%s', "\n");
        break;
      default:
        printf('              <i class="fas fa-check"> = ' . _('Only send reqested data') . '</i><br>
              <i class="fas fa-exclamation"> = ' . _('Send too much/less data') . '</i>%s', "\n");
    }
    printf(
      '            </td>
          </tr>
          <tr>
            <td>%s ECS</td>
            <td>
              <i class="fas fa-check"> = ' . _('Have ECS for %s') . '</i><br>
              <i class="fas fa-exclamation-triangle"> = ' . _('Missing ECS for %s') . '</i><br>
              <i class="fas fa-exclamation"> = ' . _('Have ECS for %s but sends too much data > not %s') . '</i>
            </td>
          </tr>
          <tr>
            <td>%s</td>
            <td>
              <i class="fas fa-check"> = ' . _('Sends attribute') . '</i><br>
              <i class="fas fa-exclamation"> = ' . _("Doesn't send attribute") . '</i>
            </td>
          </tr>
        </table>
        <br>
        <table id="resultTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              %s
              <th>' . _('Tested') . '</th>
              <th>' . _('Data') . '</th>
              <th>' . _('ECS') . '</th>%s',
      $this->tests[$tab]['fullName'],
      $this->tests[$tab]['fullName'],
      $this->tests[$tab]['fullName'],
      $this->tests[$tab]['fullName'],
      $this->tests[$tab]['fullName'],
      implode('<br>', $this->tests[$tab]['expected']),
      $selectedIdp ? '' : '<th>' . _('IdP') . '</th>',
      "\n"
    );
    foreach ($this->tests[$tab]['expected'] as $shortName => $SAML) {
      printf('              <th>%s</th>%s', $shortName, "\n");
    }
    printf('            </tr>
          </thead>
          <tbody>%s', "\n");

    $testHandler = $this->config->getDB()->prepare($selectedIdp ?
      'SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
        FROM `tests`, `testRuns`, `idps`
        WHERE `tests`.`testRun_id` = `testRuns`.`id`
          AND `testRuns`.`idp_id` = `idps`.`id`
          AND `test` = :Test
          AND `entityID` = :Idp
        ORDER BY `time` DESC;'
      : 'SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
        FROM `tests`, `testRuns`, `idps`
        WHERE `tests`.`testRun_id` = `testRuns`.`id`
          AND `testRuns`.`idp_id` = `idps`.`id`
          AND `test` = :Test
        ORDER BY `entityID`, `time` DESC;');
    $okData = 0;
    $warnData = 0;
    $failData = 0;
    $okEC = 0;
    $warnEC = 0;
    $failEC = 0;
    $lastIdp = '';
    $testHandler->execute($selectedIdp ?
      array('Test' => $this->tests[$tab]['dbName'], 'Idp' => $selectedIdp)
      : array('Test' => $this->tests[$tab]['dbName']));
    while ($testResult = $testHandler->fetch(PDO::FETCH_ASSOC)) {
      $idp = $testResult['entityID'];
      if ($selectedIdp || $lastIdp != $idp) {
        $lastIdp = $idp;
        $this->testedIPs[$idp] = true;

        if ($selectedIdp) {
          printf('            <tr>
              <td>%s</td>%s', $testResult['time'], "\n");
        } else {
          printf('            <tr>
              <td><a href="?tab=%s&idp=%s">%s</a></td>
              <td>%s</td>%s', $tab, $idp, $idp, $testResult['time'], "\n");
        }
        switch ($testResult['testResult']) {
          case $this->tests[$tab]['testResults']['OKOK']:
            printf('              <td><i class="fas fa-check">   </td>
              <td><i class="fas fa-check">   </td>%s', "\n");
            $okData++;
            $okEC++;
            break;
          case $this->tests[$tab]['testResults']['OKFail']:
            printf('              <td><i class="fas fa-check">   </td>
              <td><i class="fas fa-exclamation-triangle">  </td>%s', "\n");
            $okData++;
            $warnEC++;
            break;
          case $this->tests[$tab]['testResults']['Fail']:
            printf('              <td><i class="fas fa-exclamation"> </td>
              <td></td>%s', "\n");
            $failData++;
            break;
          case $this->tests[$tab]['testResults']['FailFail']:
            printf('              <td><i class="fas fa-exclamation"> </td>
              <td><i class="fas fa-exclamation"> </td>%s', "\n");
            $failData++;
            $failEC++;
            break;
          default:
            printf('              <td>%s</td>
              <td></td>%s', $testResult['testResult'], "\n");
        }
        foreach ($this->tests[$tab]['expected'] as $SAML) {
          printf(
            '              <td><i class="fas fa-%s</td>%s',
            $this->sends($testResult['attr_OK'], $SAML) ? self::HTML_CHECK_SP : self::HTML_EXCLAMATION_SP,
            "\n"
          );
        }
        printf('            </tr>%s', "\n");
      }
    }
    printf('          </tbody>%s', "\n");
    $this->printFooterSummary(
      $okData,
      $warnData,
      $failData,
      $okEC,
      $warnEC,
      $failEC,
      sizeof($this->tests[$tab]['expected'])
    );
  }

  /**
   * Show results for mfa tests
   *
   * @return void
   */
  public function showMFA()
  {
    $selectedIdp = isset($_GET['idp']) ? $_GET['idp'] : false;
    printf('    <div class="row">
      <div class="col">%s', "\n");
    if ($selectedIdp) {
      printf(
        '      <h1>' . _('MFA tests run by %s') . '</h1>
      <a href="./admin.php?tab=mfa">
        <button type="button" class="btn btn-success">' . _(self::HTML_SHOW_ALL_IDPS) . '</button>
      </a>%s',
        htmlspecialchars($selectedIdp),
        "\n"
      );
    } else {
      printf(
        '      <h1>' . _("Data based on IdPs that have run MFA test") . '</h1>%s',
        "\n"
      );
    }
    printf(
      '        <table class="table table-striped table-bordered">
          <tr>
            <td>' . _('MFA') . ' </td>
            <td>
              <i class="fas fa-check"> = ' . _('Responds with REFEDS MFA') . '</i><br>
              <i class="fas fa-exclamation"> = ' .
        _('Wrongly sends something else (SHOULD break an not return anything)') . '</i>
            </td>
          </tr>
          <tr>
            <td>' . _('ForceAuthn') . '</td>
            <td>
              <i class="fas fa-check"> = ' . _('Sends a new Authentication-Instant in step 2') . '</i><br>
              <i class="fas fa-exclamation"> = ' . _('Sends same Authentication-Instant in step 2') . '</i>
            </td>
          </tr>
        </table>
        <br>
        <br>
        <table id="resultTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              %s
              <th>' . _('Tested') . '</th>
              <th>' . _('MFA') . '</th>
              <th>' . _('ForceAuthn') . '</th>
            </tr>
          </thead>
          <tbody>%s',
      $selectedIdp ? '' : '<th>' . _('IdP') . '</th>',
      "\n"
    );
    $testHandler = $this->config->getDB()->prepare($selectedIdp ?
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'mfa'
        AND `entityID` = :Idp
      ORDER BY `time` DESC;"
      : "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'mfa'
      ORDER BY `entityID`, `time` DESC;");
    $okMFA = 0;
    $okForceAuthn = 0;
    $failMFA = 0;
    $failForceAuthn = 0;
    $lastIdp = '';
    $testHandler->execute($selectedIdp ?
      array('Idp' => $selectedIdp) : array());
    while ($testResult = $testHandler->fetch(PDO::FETCH_ASSOC)) {
      $idp = $testResult['entityID'];
      if ($selectedIdp || $lastIdp != $idp) {
        $lastIdp = $idp;
        $this->testedIPs[$idp] = true;

        if ($selectedIdp) {
          printf('            <tr>
              <td>%s</td>%s', $testResult['time'], "\n");
        } else {
          printf('            <tr>
              <td><a href="?tab=mfa&idp=%s">%s</a></td>
              <td>%s</td>%s', $idp, $idp, $testResult['time'], "\n");
        }
        switch ($testResult['testResult']) {
          case 'Supports REFEDS MFA and ForceAuthn.':
            printf('              <td><i class="fas fa-check"></i> OK</td>
                <td><i class="fas fa-check"></i> OK</td>%s', "\n");
            $okMFA++;
            $okForceAuthn++;
            break;
          case 'Does support ForceAuthn but not REFEDS MFA.':
            printf('              <td><i class="fas fa-exclamation"></i> ' . _('Fail') . '</td>
              <td><i class="fas fa-check"></i> OK</td>%s', "\n");
            $failMFA++;
            $okForceAuthn++;
            break;
          case 'Supports REFEDS MFA but not ForceAuthn.':
            printf('              <td><i class="fas fa-check"></i> OK</td>
              <td><i class="fas fa-exclamation"></i> ' . _('Fail') . '</td>%s', "\n");
            $okMFA++;
            $failForceAuthn++;
            break;
          case 'Does neither support REFEDS MFA or ForceAuthn.':
            printf('              <td><i class="fas fa-exclamation"></i> Fail</td>
              <td><i class="fas fa-exclamation"></i> ' . _('Fail') . '</td>%s', "\n");
            $failMFA++;
            $failForceAuthn++;
            break;
          case 'Supports REFEDS MFA.':
            printf('              <td><i class="fas fa-check"></i> OK</td>
              <td></td>%s', "\n");
            $okMFA++;
            break;
          default:
            printf('              <td>%s</td>%s', $testResult['testResult'], "\n");
        }
        print "            </tr>\n";
      }
    }
    printf('          </tbody>%s', "\n");
    $this->printFooterSummary($okMFA, 0, $failMFA, $okForceAuthn, 0, $failForceAuthn);
  }

  /**
   * Show results for ESI tests
   *
   * @return void
   */
  public function showESI()
  {
    $selectedIdp = isset($_GET['idp']) ? $_GET['idp'] : false;
    printf('    <div class="row">
      <div class="col">%s', "\n");
    if ($selectedIdp) {
      printf(
        '      <h1>' . _('ESI tests run by %s') . '</h1>
      <a href="./admin.php?tab=esi">
        <button type="button" class="btn btn-success">' . _(self::HTML_SHOW_ALL_IDPS) . '</button>
      </a>
      <a href="./admin.php?tab=AllTests&idp=%s">
        <button type="button" class="btn btn-success">' . _('Show all EC tests for this IdP') . '</button>
      </a>
      <br>%s',
        htmlspecialchars($selectedIdp),
        urlencode($selectedIdp),
        "\n"
      );
    } else {
      printf(
        '      <h1>' . _('Data based on IdPs that have run ESI test') . '</h1>%s',
        "\n"
      );
    }
    printf(
      '        <i class="fas fa-check"> = ' . ('Correct schacPersonalUniqueCode') . '</i><br>
        <i class="fas fa-exclamation-triangle"> = ' . _('Missing schacPersonalUniqueCode or too many') . '</i><br>
        <i class="fas fa-exclamation"> = ' . _('Error in schacPersonalUniqueCode') . '</i>
        <br>
        <br>
        <table id="resultTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              %s
              <th>' . _('Tested') . '</th>
              <th>' . _('ESI (any)') . '</th>
              <th>' . _('Tested') . '</th>
              <th>' . _('ESI (as student)') . '</th>
            </tr>
          </thead>
          <tbody>',
      $selectedIdp ? '' : '<th>' . _('IdP') . '</th>',
      "\n"
    );
    $testRun = 0;
    $testHandler = $this->config->getDB()->prepare($selectedIdp ?
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`, `testRun_id`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'esi'
        AND `entityID` = :Idp
      ORDER BY `time` DESC;"
      : "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`, `testRun_id`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'esi'
      ORDER BY `entityID`, `time` DESC;");
    $testStudHandler = $this->config->getDB()->prepare(
      "SELECT `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests` WHERE `testRun_id` = :testrun
        AND `test` = 'esi-stud'"
    );
    $ok = 0;
    $warn = 0;
    $fail = 0;
    $okStud = 0;
    $warnStud = 0;
    $failStud = 0;
    $lastIdp = '';
    $testHandler->execute($selectedIdp ?
      array('Idp' => $selectedIdp) : array());
    while ($testResult = $testHandler->fetch(PDO::FETCH_ASSOC)) {
      $idp = $testResult['entityID'];
      if ($selectedIdp || $lastIdp != $idp) {
        $lastIdp = $idp;
        $testRun = $testResult['testRun_id'];
        $this->testedIPs[$idp] = true;

        if ($selectedIdp) {
          printf('            <tr>
              <td>%s</td>%s', $testResult['time'], "\n");
        } else {
          printf('            <tr>
              <td><a href="?tab=esi&idp=%s">%s</a></td>
              <td>%s</td>%s', $idp, $idp, $testResult['time'], "\n");
        }
        switch ($testResult['testResult']) {
          case 'schacPersonalUniqueCode OK':
            print "              <td><i class=\"fas fa-check\"></i> OK</td>\n";
            $ok++;
            break;
          case 'schacPersonalUniqueCode OK. BUT wrong case':
            print '              <td><i class="fas fa-check"></i> OK, <i class="fas fa-exclamation-triangle"></i> ' .
              _("Wrong case") . "</td>\n";
            $ok++;
            break;
          case 'Missing schacPersonalUniqueCode':
            print '              <td><i class="fas fa-exclamation-triangle"></i> ' .
              _('No schacPersonalUniqueCode') . "</td>\n";
            $warn++;
            break;
          case self::HTML_MORE_THAN_ONE_SCHAC:
            print '              <td><i class="fas fa-exclamation-triangle"></i> ' .
              _(self::HTML_MORE_THAN_ONE_SCHAC) . "</td>\n";
            $warn++;
            break;
          case 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:':
            print '              <td><i class="fas fa-exclamation"></i> ' . _('Not correct code') . "</td>\n";
            $fail++;
            break;
          case 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:':
            print '              <td><i class="fas fa-exclamation"></i> ' . _('sHO = se') . "</td>\n";
            $fail++;
            break;
          default:
            print "              <td>" . $testResult['testResult'] . "</td>\n";
        }
        $testStudHandler->bindParam(':testrun', $testRun);
        $testStudHandler->execute();
        if ($testResult = $testStudHandler->fetch(PDO::FETCH_ASSOC)) {
          printf("              <td>%s</td>\n", $testResult['time']);
          switch ($testResult['testResult']) {
            case 'schacPersonalUniqueCode OK':
              print "              <td><i class=\"fas fa-check\"></i> OK</td>\n";
              $okStud++;
              break;
            case 'schacPersonalUniqueCode OK. BUT wrong case':
              print '              <td><i class="fas fa-check"></i> OK, <i class="fas fa-exclamation-triangle"></i> ' .
                _("Wrong case") . "</td>\n";
              $okStud++;
              break;
            case 'Missing schacPersonalUniqueCode':
              print '              <td><i class="fas fa-exclamation-triangle"></i> ' .
                _("No schacPersonalUniqueCode") . "</td>\n";
              $warnStud++;
              break;
            case self::HTML_MORE_THAN_ONE_SCHAC:
              print '              <td><i class="fas fa-exclamation-triangle"></i> ' .
                _(self::HTML_MORE_THAN_ONE_SCHAC) . "</td>\n";
              $warnStud++;
              break;
            case 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:':
              print "              <td><i class=\"fas fa-exclamation\"></i> " . _("Not correct code") . "</td>\n";
              $failStud++;
              break;
            case 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:':
              print "              <td><i class=\"fas fa-exclamation\"></i> " . _("sHO = se") . "</td>\n";
              $failStud++;
              break;
            default:
              print "              <td>" . $testResult['testResult'] . "</td>\n";
          }
        } else {
          print '              <td>' . _('No test run as Student') . '</td>
                  <td></td>' . "\n";
        }
        print "            </tr>\n";
      }
    }
    printf(
      '          </tbody>
          <tfooter>
            <tr>
              <td colspan="%d"></td>
              <td>%s',
      $selectedIdp ? 1 : 2,
      "\n"
    );
    if ($ok) {
      printf("                <i class=\"fas fa-check\"></i> = %s<br>\n", $ok);
    }
    if ($warn) {
      printf("                <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n", $warn);
    }
    if ($fail) {
      printf("                <i class=\"fas fa-exclamation\"></i> = %s<br>\n", $fail);
    }
    printf('              </td>
              <td></td>
              <td>%s', "\n");
    if ($okStud) {
      printf("                <i class=\"fas fa-check\"></i> = %s<br>\n", $okStud);
    }
    if ($warnStud) {
      printf("                <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n", $warnStud);
    }
    if ($failStud) {
      printf("                <i class=\"fas fa-exclamation\"></i> = %s<br>\n", $failStud);
    }
    printf(
      '              </td>
            </tr>
          <tfooter>
        </table>%s',
      "\n"
    );
    if (isset($this->federation['metadataTool'])) {
      printf('        <table class="table table-striped table-bordered">
          <tr><th>' . _('IdPs not tested') . '</th></tr>%s', "\n");
      foreach ($this->testedIPs as $idp => $value) {
        if (! $value) {
          printf("          <tr><td>%s</td></tr>\n", $idp);
        }
      }
      print "        </table>\n";
    }
    print "      </div><!-- End col-->
    </div><!-- End row-->\n";
  }

  /**
   * Print footer of table with stats
   */
  private function printFooterSummary($okData, $warnData, $failData, $okEC, $warnEC, $failEC, $restCols = 0)
  {
    printf(
      '          <tfooter>
            <tr>
              <td colspan="%d"></td>
              <td>%s',
      isset($_GET['idp']) ? 1 : 2,
      "\n"
    );
    if ($okData) {
      printf("                <i class=\"fas fa-check\"></i> = %s<br>\n", $okData);
    }
    if ($warnData) {
      printf("                <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n", $warnData);
    }
    if ($failData) {
      printf("                <i class=\"fas fa-exclamation\"></i> = %s<br>\n", $failData);
    }
    printf('              </td>
              <td>%s', "\n");
    if ($okEC) {
      printf("                <i class=\"fas fa-check\"></i> = %s<br>\n", $okEC);
    }
    if ($warnEC) {
      printf("                <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n", $warnEC);
    }
    if ($failEC) {
      printf("                <i class=\"fas fa-exclamation\"></i> = %s<br>\n", $failEC);
    }
    printf(
      '              </td>
              %s
            </tr>
          </tfooter>
        </table>%s',
      $restCols > 0 ? sprintf('<td colspan="%d"></td>', $restCols) : '',
      "\n"
    );
    if (isset($this->federation['metadataTool'])) {
      printf(
        '        <table class="table table-striped table-bordered">
          <tr><th>' . _("IdPs not tested") . '</th></tr>',
        "\n"
      );
      foreach ($this->testedIPs as $idp => $value) {
        if (! $value) {
          printf('          <tr><td>%s</a></td></tr>%s', $idp, "\n");
        }
      }
      print "        </table>\n";
    }
    printf('      </div><!-- End col-->
    </div><!-- End row-->%s', "\n");
  }

  /**
   * Return all configured tests
   *
   * @return array
   */
  public function getTests()
  {
    return $this->tests;
  }
}
