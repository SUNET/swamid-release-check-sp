<?php
//Load composer's autoloader
require_once '../vendor/autoload.php';
$config = new \releasecheck\Configuration();

$htmlClass = $config->getExtendedClass('HTML');

$html = new $htmlClass();

$collapseIcons = array();
$tested_idps = array();

if (isset($config->getFederation()['metadataTool'])) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://'.$config->getFederation()['metadataTool'].'/api/v1/');
  curl_setopt($ch, CURLOPT_USERAGENT, 'Release-check');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_NOBODY, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  $res = curl_exec($ch);
  $data = json_decode($res, true, 4);
  foreach ($data['objects'] as $row) {
    $tested_idps[$row['entityID']] = false;
  }
  curl_close($ch);
}

$randsActive = '';
$cocov1Active = '';
$cocov2Active = '';
$anonymousActive = '';
$pseudonymousActive = '';
$personalizedActive = '';
$mfaActive = '';
$esiActive = '';
$allTestsActive = '';
$ecsActive = '';

if (isset($_GET['tab'])) {
  switch ($_GET['tab']) {
    case 'Anon' :
      $anonymousActive = ' active';
      break;
    case 'PAnon' :
      $pseudonymousActive = ' active';
      break;
    case 'Pers' :
      $personalizedActive = ' active';
      break;
    case 'RandS' :
      $randsActive = ' active';
      break;
    case 'CoCov1' :
      $cocov1Active = ' active';
      break;
    case 'CoCov2' :
      $cocov2Active = ' active';
      break;
    case 'MFA' :
      $mfaActive = ' active';
      break;
    case 'ESI' :
      $esiActive = ' active';
      break;
    case 'AllTests' :
      $allTestsActive = ' active';
      break;
    default :
  }
}
$html->showHeaders();
if (! in_array($_SERVER['saml_eduPersonPrincipalName'], $config->getFederation()['adminUsers'] )) {
    print "<h1>No access</h1>";
    $html->showFooter();
    exit;
}
?>
    <div class="row">
      <div class="col">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link<?=$randsActive?>" href="?tab=RandS">R&S</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$cocov1Active?>" href="?tab=CoCov1">CoCov1</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$cocov2Active?>" href="?tab=CoCov2">CoCov2</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$anonymousActive?>" href="?tab=Anon">Anon</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$pseudonymousActive?>" href="?tab=PAnon">Panon</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$personalizedActive?>" href="?tab=Pers">Pers</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$mfaActive?>" href="?tab=MFA">MFA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$esiActive?>" href="?tab=ESI">ESI</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$allTestsActive?>" href="?tab=AllTests">AllTests</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$ecsActive?>" href="?tab=ECS">ECS</a>
          </li>
        </ul>
      </div>
    </div>
<?php
if (isset($_GET['idp']))
  printf ("        <h3>Result for %s</h3>\n", htmlspecialchars($_GET['idp']));

if (isset($_GET['tab'])) {
  switch ($_GET['tab']) {
    case 'Anon' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showAnon($tested_idps);
      break;
    case 'PAnon' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showPAnon($tested_idps);
      break;
    case 'Pers' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showPers($tested_idps);
      break;
    case 'RandS' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showRandS($tested_idps);
      break;
    case 'CoCov1' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showCoCo($tested_idps,1);
      break;
    case 'CoCov2' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showCoCo($tested_idps,2);
      break;
    case 'MFA' :
      if (isset($_GET['idp']))
        showResultsMFA($_GET['idp']);
      else
        showMFA($tested_idps);
      break;
    case 'ESI' :
      if (isset($_GET['idp']))
        showResultsESI($_GET['idp']);
      else
        showESI($tested_idps);
      break;
    case 'AllTests' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showAllTests();
      break;
    case 'ECS' :
      if (isset($_GET['idp']))
        showTestsIdP();
      else
        showEcsStatus($tested_idps);
      break;
  }
}

$html->showFooter();

function sends($string,$Attribute) {
  if ( strpos($string, $Attribute) === false ) {
    return false;
  } else {
    return true;
  }
}

function showAnon($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run Anonymous test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>Anonymous data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data</i><br>
              <i class="fas fa-exclamation"> = Send to much/less data</i>
            </td>
          </tr>
          <tr>
            <td>Anonymous ECS</td>
            <td>
              <i class="fas fa-check"> = Have ECS for Anonymous</i><br>
              <i class="fas fa-exclamation-triangle"> = Missing ECS for Anonymous</i><br>
              <i class="fas fa-exclamation"> = Have ECS for Anonymous but sends to much data > not Anonymous</i>
            </td>
          </tr>
          <tr>
            <td>eduPersonScopedAffiliation<br>schacHomeOrganization</td>
            <td>
              <i class="fas fa-check"> = Sends attribute</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send attribute</i>
            </td>
          </tr>
        </table>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=PAnon&Idp">IdP</a></th>
            <th><a href="?tab=PAnon&Time">Tested</a></th>
            <th><a href="?tab=PAnon&Status">Data</a></th>
            <th>ECS</th>
            <th>ePSA</th>
            <th>sHO</th>
          </tr>' . "\n";
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'anonymous'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        ADN `test` = 'anonymous'
      ORDER BY length(testResult) DESC,
      length(attr_OK) - length(replace(attr_OK, 'eduPersonScopedAffiliation', '')) +
      length(attr_OK) - length(replace(attr_OK, 'schacHomeOrganization', ''));");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'anonymous'
      ORDER BY `entityID`;");
  }
  $okData=0;
  $warnData=0;
  $failData=0;
  $okEC=0;
  $warnEC=0;
  $failEC=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=Anon&idp=%s#anonymous">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'Anonymous attributes OK, Entity Category Support OK' :
        printf ('            <td><i class="fas fa-check"></td>%s            <td><i class="fas fa-check"></td>%s', "\n", "\n");
        $okData++;
        $okEC++;
        break;
      case 'Anonymous attributes OK, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-check\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation-triangle\"></td>\n";
        $okData++;
        $warnEC++;
        break;
      case 'Anonymous attribute missing, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td></td>\n";
        $failData++;
        break;
      case 'Anonymous attribute missing, BUT Entity Category Support claimed' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation\"></td>\n";
        $failData++;
        $failEC++;
        break;
      default :
        print "            <td colspan=\"2\">" . $testResult['testResult'] . "</td>\n";
    }
    printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s',
    sends($testResult['attr_OK'],"eduPersonScopedAffiliation") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"schacHomeOrganization") ? "check" : "exclamation", "\n", "\n");
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okData) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okData);
  if ($warnData) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnData);
  if ($failData) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failData);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okEC) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okEC);
  if ($warnEC) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnEC);
  if ($failEC) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failEC);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td><a href=\"?tab=Anon&idp=%s\">%s</a></td></tr>\n", $idp, $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showPAnon($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run Pseudonymous test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>Pseudonymous data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data</i><br>
              <i class="fas fa-exclamation"> = Send to much/less data</i>
            </td>
          </tr>
          <tr>
            <td>Pseudonymous ECS</td>
            <td>
              <i class="fas fa-check"> = Have ECS for Pseudonymous</i><br>
              <i class="fas fa-exclamation-triangle"> = Missing ECS for Pseudonymous</i><br>
              <i class="fas fa-exclamation"> = Have ECS for Pseudonymous but sends to much data > not Pseudonymous</i>
            </td>
          </tr>
          <tr>
            <td>pairwise-id<br>eduPersonAssurance<br>eduPersonScopedAffiliation<br>schacHomeOrganization</td>
            <td>
              <i class="fas fa-check"> = Sends attribute</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send attribute</i>
            </td>
          </tr>
        </table>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=PAnon&Idp">IdP</a></th>
            <th><a href="?tab=PAnon&Time">Tested</a></th>
            <th><a href="?tab=PAnon&Status">Data</a></th>
            <th>ECS</th>
            <th>pairwise-id</th>
            <th>ePA</th>
            <th>ePSA</th>
            <th>sHO</th>
          </tr>' . "\n";
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'pseudonymous'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'pseudonymous'
      ORDER BY length(testResult) DESC,
        length(attr_OK) - length(replace(attr_OK, 'pairwise-id', '')) +
        length(attr_OK) - length(replace(attr_OK, 'eduPersonAssurance', '')) +
        length(attr_OK) - length(replace(attr_OK, 'eduPersonScopedAffiliation', '')) +
        length(attr_OK) - length(replace(attr_OK, 'schacHomeOrganization', ''));");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'pseudonymous'
      ORDER BY `entityID`;");
  }
  $okData=0;
  $warnData=0;
  $failData=0;
  $okEC=0;
  $warnEC=0;
  $failEC=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=PAnon&idp=%s#pseudonymous">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'Pseudonymous attributes OK, Entity Category Support OK' :
        printf ('            <td><i class="fas fa-check"></td>%s            <td><i class="fas fa-check"></td>%s', "\n", "\n");
        $okData++;
        $okEC++;
        break;
      case 'Pseudonymous attributes OK, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-check\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation-triangle\"></td>\n";
        $okData++;
        $warnEC++;
        break;
      case 'Pseudonymous attribute missing, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td></td>\n";
        $failData++;
        break;
      case 'Pseudonymous attribute missing, BUT Entity Category Support claimed' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation\"></td>\n";
        $failData++;
        $failEC++;
        break;
      default :
        print "            <td colspan=\"2\">" . $testResult['testResult'] . "</td>\n";
    }
    printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s',
     sends($testResult['attr_OK'],"pairwise-id") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"eduPersonAssurance") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"eduPersonScopedAffiliation") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"schacHomeOrganization") ? "check" : "exclamation", "\n", "\n");
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okData) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okData);
  if ($warnData) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnData);
  if ($failData) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failData);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okEC) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okEC);
  if ($warnEC) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnEC);
  if ($failEC) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failEC);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td><a href=\"?tab=PAnon&idp=%s\">%s</a></td></tr>\n", $idp, $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showPers($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run Personalized test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>Personalized data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data</i><br>
              <i class="fas fa-exclamation"> = Send to much/less data</i>
            </td>
          </tr>
          <tr>
            <td>Personalized ECS</td>
            <td>
              <i class="fas fa-check"> = Have ECS for Personalized</i><br>
              <i class="fas fa-exclamation-triangle"> = Missing ECS for Personalized</i><br>
              <i class="fas fa-exclamation"> = Have ECS for Personalized but sends to much data > not Personalized</i>
            </td>
          </tr>
          <tr>
            <td>subject-id<br>mail<br>displayName<br>givenName<br>sn<br>eduPersonAssurance<br>eduPersonScopedAffiliation<br>schacHomeOrganization</td>
            <td>
              <i class="fas fa-check"> = Sends attribute</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send attribute</i>
            </td>
          </tr>
        </table>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=Pers&Idp">IdP</a></th>
            <th><a href="?tab=Pers&Time">Tested</a></th>
            <th><a href="?tab=Pers&Status">Data</a></th>
            <th>ECS</th>
            <th>subject-id</th>
            <th>mail</th>
            <th>displayName</th>
            <th>givenName</th>
            <th>sn</th>
            <th>ePA</th>
            <th>ePSA</th>
            <th>sHO</th>
          </tr>' . "\n";
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'personalized'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'personalized'
      ORDER BY length(testResult) DESC,
        length(attr_OK) - length(replace(attr_OK, 'subject-id', '')) +
        length(attr_OK) - length(replace(attr_OK, 'mail', '')) +
        length(attr_OK) - length(replace(attr_OK, 'displayName', '')) +
        length(attr_OK) - length(replace(attr_OK, 'givenName', '')) +
        length(attr_OK) - length(replace(attr_OK, 'sn', '')) +
        length(attr_OK) - length(replace(attr_OK, 'eduPersonAssurance', '')) +
        length(attr_OK) - length(replace(attr_OK, 'eduPersonScopedAffiliation', '')) +
        length(attr_OK) - length(replace(attr_OK, 'schacHomeOrganization', ''));");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'personalized'
      ORDER BY `entityID`;");
  }
  $okData=0;
  $warnData=0;
  $failData=0;
  $okEC=0;
  $warnEC=0;
  $failEC=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;
    printf ('          <tr>%s            <td><a href="?tab=Pers&idp=%s#personalized">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'Personalized attributes OK, Entity Category Support OK' :
        printf ('            <td><i class="fas fa-check"></td>%s            <td><i class="fas fa-check"></td>%s', "\n", "\n");
        $okData++;
        $okEC++;
        break;
      case 'Personalized attributes OK, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-check\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation-triangle\"></td>\n";
        $okData++;
        $warnEC++;
        break;
      case 'Personalized attribute missing, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td></td>\n";
        $failData++;
        break;
      case 'Personalized attribute missing, BUT Entity Category Support claimed' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation\"></td>\n";
        $failData++;
        $failEC++;
        break;
      default :
        print "            <td colspan=\"2\">" . $testResult['testResult'] . "</td>\n";
    }
    printf ('            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
            <td><i class="fas fa-%s"></td>
          </tr>%s',
      sends($testResult['attr_OK'],"subject-id") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"mail") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"displayName") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"givenName") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"sn") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"eduPersonAssurance") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"eduPersonScopedAffiliation") ? "check" : "exclamation",
      sends($testResult['attr_OK'],"schacHomeOrganization") ? "check" : "exclamation", "\n");
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okData) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okData);
  if ($warnData) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnData);
  if ($failData) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failData);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okEC) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okEC);
  if ($warnEC) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnEC);
  if ($failEC) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failEC);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td><a href=\"?tab=Pers&idp=%s\">%s</a></td></tr>\n", $idp, $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showRandS($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run R&S test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>R&S data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data or less</i><br>
              <i class="fas fa-exclamation"> = Send to much data</i>
            </td>
          </tr>
          <tr>
            <td>R&S ECS</td>
            <td>
              <i class="fas fa-check"> = Have ECS for R&S</i><br>
              <i class="fas fa-exclamation-triangle"> = Missing ECS for R&S</i><br>
              <i class="fas fa-exclamation"> = Have ECS for R&S but sends to much data > not R&S</i>
            </td>
          </tr>
          <tr>
            <td>ePPN<br>mail<br>displayName<br>givenName<br>sn</td>
            <td>
              <i class="fas fa-check"> = Sends attribute</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send attribute</i>
            </td>
          </tr>
        </table>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=RandS&Idp">IdP</a></th>
            <th><a href="?tab=RandS&Time">Tested</a></th>
            <th><a href="?tab=RandS&Status">R&S data</a></th>
            <th>R&S ECS</th>
            <th>ePPN</th>
            <th>mail</th>
            <th>displayName</th>
            <th>givenName</th>
            <th>sn</th>
          </tr>' . "\n";
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'rands'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'rands'
      ORDER BY length(testResult) DESC,
        length(attr_OK) - length(replace(attr_OK, 'eduPersonPrincipalName', '')) +
        length(attr_OK) - length(replace(attr_OK, 'mail', '')) +
        length(attr_OK) - length(replace(attr_OK, 'displayName', '')) +
        length(attr_OK) - length(replace(attr_OK, 'givenName', '')) +
        length(attr_OK) - length(replace(attr_OK, 'sn', ''));");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'rands'
      ORDER BY `entityID`;");
  }
  $okData=0;
  $warnData=0;
  $failData=0;
  $okEC=0;
  $warnEC=0;
  $failEC=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=RandS&idp=%s#rands">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'R&S attribut OK, Entity Category Support OK' :
      case 'R&S attributes OK, Entity Category Support OK' :
        printf ('            <td><i class="fas fa-check"></td>%s            <td><i class="fas fa-check"></td>%s', "\n", "\n");
        $okData++;
        $okEC++;
        break;
      case 'R&S attribut OK, Entity Category Support saknas' :
      case 'R&S attributes OK, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-check\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation-triangle\"></td>\n";
        $okData++;
        $warnEC++;
        break;
      case 'R&S attribute missing, Entity Category Support missing' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td></td>\n";
        $failData++;
        break;
      case 'R&S attributes missing, BUT Entity Category Support claimed' :
        print "            <td><i class=\"fas fa-exclamation\"></td>\n\t\t\t<td><i class=\"fas fa-exclamation\"></td>\n";
        $failData++;
        $failEC++;
        break;
      default :
        print "            <td colspan=\"2\">" . $testResult['testResult'] . "</td>\n";
    }
    printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s', sends($testResult['attr_OK'],"eduPersonPrincipalName") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"mail") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"displayName") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"givenName") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"sn") ? "check" : "exclamation", "\n", "\n");
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okData) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okData);
  if ($warnData) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnData);
  if ($failData) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failData);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okEC) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okEC);
  if ($warnEC) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnEC);
  if ($failEC) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failEC);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td><a href=\"?tab=Rands&idp=%s\">%s</a></td></tr>\n", $idp, $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showCoCo($tested_idps, $version = 1) {
  $test = $version == 1 ? 'cocov1-1' : 'cocov2-1';
  printf ('    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run %s test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>Coco data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data or less</i><br>
              <i class="fas fa-exclamation-triangle"> = Only send reqested data or less (not sending norEduPersonNIN)</i><br>
              <i class="fas fa-exclamation"> = Send to much data</i>
            </td>
          </tr>
          <tr>
            <td>CoCo ECS</td>
            <td>
              <i class="fas fa-check"> = Have ECS for CoCo</i><br>
              <i class="fas fa-exclamation-triangle"> = Missing ECS for CoCo</i><br>
              <i class="fas fa-exclamation"> = Have ECS for CoCo but sends to much data > not CoCo</i>
            </td>
          </tr>
          <tr>
            <td>norEduPersonNIN</td>
            <td>
              <i class="fas fa-check"> = Sends norEduPersonNIN</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send norEduPersonNIN</i>
            </td>
          </tr>
          <tr>
            <td>personalIdentityNumber</td>
            <td>
              <i class="fas fa-check"> = Sends personalIdentityNumber</i><br>
              <i class="fas fa-exclamation"> = Doesn\'t send personalIdentityNumber</i>
            </td>
          </tr>
        </table>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=CoCov%d&Idp">IdP</a></th>
            <th><a href="?tab=CoCov%d&Time">Tested</a></th>
            <th><a hreF="?tab=CoCov%d&Status">CoCo data</a></th>
            <th>CoCo ECS</th>
            <th>norEduPersonNIN</th>
            <th>personalIdentityNumber</th>
          </tr>%s', $version == 1 ? 'CoCov1-1' : 'CoCov2-1', $version, $version, $version, "\n");
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = '$test'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = '$test'
      ORDER BY testResult DESC, length(attr_OK) - length(replace(attr_OK, 'norEduPersonNIN', '')) + length(attr_OK) - length(replace(attr_OK, 'personalIdentityNumber', ''));");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = '$test'
      ORDER BY `entityID`;");
  }
  $okData=0;
  $warnData=0;
  $failData=0;
  $okEC=0;
  $warnEC=0;
  $failEC=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=CoCov%d&idp=%s#cocov%d-1">%s</a></td>%s', "\n", $version, $idp, $version, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case "CoCo OK, Entity Category Support OK":
        print "            <td><i class=\"fas fa-check\"></td>\n            <td><i class=\"fas fa-check\"></td>\n";
        $okData++;
        $okEC++;
        break;
      case "CoCo OK, Entity Category Support missing":
      case "CoCo OK, Entity Category Support saknas":
        # Show warning if Fulfiulls CoCo but doesn't send norEduPersonNIN
        printf ("            <td><i class=\"fas fa-%s\"></td>\n            <td><i class=\"fas fa-exclamation-triangle\"></td>\n", sends($testResult['attr_OK'],"norEduPersonNIN") ? 'check' : 'exclamation-triangle');
        $okData++;
        $warnEC++;
        break;
      case "Support for CoCo missing, Entity Category Support missing":
        print "            <td><i class=\"fas fa-exclamation\"></td>\n            <td></td>\n";
        $failData++;
        break;
      case "CoCo is not supported, BUT Entity Category Support is claimed":
        print "            <td><i class=\"fas fa-exclamation\"></td>\n            <td><i class=\"fas fa-exclamation\"></td>\n";
        $failData++;
        $failEC++;
        break;
      default :
        print "            <td colspan=\"2\">" . $testResult['testResult'] . "</td>\n";
    }
    printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s',
      sends($testResult['attr_OK'],"norEduPersonNIN") ? "check" : "exclamation", "\n", sends($testResult['attr_OK'],"personalIdentityNumber") ? "check" : "exclamation", "\n", "\n");
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okData) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okData);
  if ($warnData) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnData);
  if ($failData) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failData);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okEC) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okEC);
  if ($warnEC) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnEC);
  if ($failEC) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failEC);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td><a href=\"?tab=Rands&idp=%s\">%s</a></td></tr>\n", $idp, $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showMFA($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run MFA test</h1>
    <table class="table table-striped table-bordered">
          <tr>
            <td>MFA </td>
            <td>
              <i class="fas fa-check"> = Responds with REFEDS MFA</i><br>
              <i class="fas fa-exclamation"> = Wrongly sends something else (SHOULD break an not return anything)</i>
            </td>
          </tr>
          <tr>
            <td>ForceAuthn</td>
            <td>
              <i class="fas fa-check"> = Sends a new Authentication-Instant in step 2</i><br>
              <i class="fas fa-exclamation"> = Sends same Authentication-Instant in step 2</i>
            </td>
          </tr>
        </table>
        <br>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=MFA&Idp">IdP</a></th>
            <th><a href="?tab=MFA&Time">Tested</a></th>
            <th><a href="?tab=MFA&Status">MFA</a></th>
            <th>ForceAuthn</th>
          </tr>' . "\n";
  global $config;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'mfa'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'mfa'
      ORDER BY length(testResult) DESC;");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'mfa'
      ORDER BY `entityID`;");
  }
  $okMFA = 0;
  $okForceAuthn = 0;
  $failMFA = 0;
  $failForceAuthn = 0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=MFA&idp=%s">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'Supports REFEDS MFA and ForceAuthn.' :
        print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
        print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
        $okMFA++;
        $okForceAuthn++;
        break;
      case 'Does support ForceAuthn but not REFEDS MFA.' :
        print "            <td><i class=\"fas fa-exclamation\"></i> Fail</td>\n";
        print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
        $failMFA++;
        $okForceAuthn++;
        break;
      case 'Supports REFEDS MFA but not ForceAuthn.' :
        print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
        print "            <td><i class=\"fas fa-exclamation\"></i> Fail</td>\n";
        $okMFA++;
        $failForceAuthn++;
        break;
      case 'Does neither support REFEDS MFA or ForceAuthn.' :
        print "            <td><i class=\"fas fa-exclamation\"></i> Fail</td>\n";
        print "            <td><i class=\"fas fa-exclamation\"></i> Fail</td>\n";
        $failMFA++;
        $failForceAuthn++;
        break;
      default :
        print "            <td>" . $testResult['testResult'] . "</td>\n";
    }
    print "          </tr>\n";
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okMFA) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okMFA);
  if ($failMFA) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failMFA);
  printf('            </td>%s            <td>%s', "\n", "\n");
  if ($okForceAuthn) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okForceAuthn);
  if ($failForceAuthn) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failForceAuthn);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td>%s</td></tr>\n", $idp);
    }
  }
  print "        </table>
  </div><!-- End col-->
  </div><!-- End row-->\n";

}

function showESI($tested_idps) {
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run ESI test</h1>
        <i class="fas fa-check"> = Correct schacPersonalUniqueCode</i><br>
        <i class="fas fa-exclamation-triangle"> = Missing schacPersonalUniqueCode or to many</i><br>
        <i class="fas fa-exclamation"> = Error in schacPersonalUniqueCode</i>
        <br>
        <br>
        <table class="table table-striped table-bordered">
          <tr>
            <th><a href="?tab=ESI&Idp">IdP</a></th>
            <th><a href="?tab=ESI&Time">Tested</a></th>
            <th><a href="?tab=ESI&Status">ESI (any)</a></th>
            <th>Tested</th>
            <th>ESI (as student)</th>
          </tr>' . "\n";
  global $config;
  $testRun = 0;
  if (isset($_GET['Time'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`, `testRun_id`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'esi'
      ORDER BY `time` DESC;");
  } elseif (isset($_GET['Status'])) {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`, `testRun_id`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'esi'
      ORDER BY length(testResult) DESC;");
  } else {
    $testHandler = $config->getDB()->prepare(
      "SELECT `entityID`, `attr_OK`, `testResult`, `tests`.`time`, `testRun_id`
      FROM `tests`, `testRuns`, `idps`
      WHERE `tests`.`testRun_id` = `testRuns`.`id`
        AND `testRuns`.`idp_id` = `idps`.`id`
        AND `test` = 'esi'
      ORDER BY `entityID`;");
  }
  $testStudHandler = $config->getDB()->prepare(
    "SELECT `attr_OK`, `testResult`, `tests`.`time`
    FROM `tests` WHERE `testRun_id` = :testrun
      AND `test` = 'esi-stud'");
  $testStudHandler->bindParam(':testrun', $testRun);

  $ok=0;
  $warn=0;
  $fail=0;
  $okStud=0;
  $warnStud=0;
  $failStud=0;
  $testHandler->execute();
  while ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
    $idp = $testResult['entityID'];
    $testRun = $testResult['testRun_id']; // NO SONAR bound above
    $tested_idps[$idp] = true;

    printf ('          <tr>%s            <td><a href="?tab=ESI&idp=%s">%s</a></td>%s', "\n", $idp, $idp, "\n");
    printf ("            <td>%s</td>\n",$testResult['time']);
    switch ($testResult['testResult']) {
      case 'schacPersonalUniqueCode OK':
        print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
        $ok++;
        break;
      case 'schacPersonalUniqueCode OK. BUT wrong case':
        print "            <td><i class=\"fas fa-check\"></i> OK, <i class=\"fas fa-exclamation-triangle\"></i> Wrong case</td>\n";
        $ok++;
        break;
      case 'Missing schacPersonalUniqueCode':
        print "            <td><i class=\"fas fa-exclamation-triangle\"></i> No schacPersonalUniqueCode</td>\n";
        $warn++;
        break;
      case 'More than one schacPersonalUniqueCode';
        print "            <td><i class=\"fas fa-exclamation-triangle\"></i> More than one schacPersonalUniqueCode</td>\n";
        $warn++;
        break;
      case 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
        print "            <td><i class=\"fas fa-exclamation\"></i> Not correct code</td>\n";
        $fail++;
        break;
      case 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
        print "            <td><i class=\"fas fa-exclamation\"></i> sHO = se</td>\n";
        $fail++;
        break;
      default :
        print "            <td>" . $testResult['testResult'] . "</td>\n";
    }
    $testStudHandler->execute();
    if ($testResult = $testStudHandler->fetch(PDO::FETCH_ASSOC)) {
      printf ("            <td>%s</td>\n",$testResult['time']);
      switch ($testResult['testResult']) {
        case 'schacPersonalUniqueCode OK':
          print "            <td><i class=\"fas fa-check\"></i> OK</td>\n";
          $okStud++;
          break;
        case 'schacPersonalUniqueCode OK. BUT wrong case':
          print "            <td><i class=\"fas fa-check\"></i> OK, <i class=\"fas fa-exclamation-triangle\"></i> Wrong case</td>\n";
          $okStud++;
          break;
        case 'Missing schacPersonalUniqueCode':
          print "            <td><i class=\"fas fa-exclamation-triangle\"></i> No schacPersonalUniqueCode</td>\n";
          $warnStud++;
          break;
        case 'More than one schacPersonalUniqueCode';
          print "            <td><i class=\"fas fa-exclamation-triangle\"></i> More than one schacPersonalUniqueCode</td>\n";
          $warnStud++;
          break;
        case 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
          print "            <td><i class=\"fas fa-exclamation\"></i> Not correct code</td>\n";
          $failStud++;
          break;
        case 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
          print "            <td><i class=\"fas fa-exclamation\"></i> sHO = se</td>\n";
          $failStud++;
          break;
        default :
          print "            <td>" . $testResult['testResult'] . "</td>\n";
      }
    } else {
      print '            <td colspan="2">No test run as Student</td>' . "\n";
    }
    print "          </tr>\n";
  }
  printf('          <tr>%s            <td colspan="2"></td>%s            <td>%s', "\n", "\n", "\n");
  if ($ok) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$ok);
  if ($warn) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warn);
  if ($fail) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$fail);
  printf('            </td>%s            <td></td>%s            <td>%s', "\n", "\n", "\n");
  if ($okStud) printf("              <i class=\"fas fa-check\"></i> = %s<br>\n",$okStud);
  if ($warnStud) printf("              <i class=\"fas fa-exclamation-triangle\"></i> = %s<br>\n",$warnStud);
  if ($failStud) printf("              <i class=\"fas fa-exclamation\"></i> = %s<br>\n",$failStud);
  printf('            </td>%s          </tr>%s        </table>%s', "\n", "\n", "\n");
  print('        <table class="table table-striped table-bordered">'. "\n");
  printf ("          <tr><th>SWAMID 2.0 IdP:s not tested</th></tr>\n");
  foreach ($tested_idps as $idp => $value) {
    if (! $value ) {
      printf ("          <tr><td>%s</td></tr>\n", $idp);
    }
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showAllTests() {
  global $config;
  $lastYear = date('Y-m-d', mktime(0, 0, 0, date("m"),   date("d"),   date("Y")-1));

  $tests = array('assurance', 'noec', 'anonymous', 'pseudonymous', 'personalized', 'cocov2-1', 'cocov2-2', 'cocov2-3', 'cocov1-1', 'cocov1-2', 'cocov1-3', 'rands', 'mfa', 'esi');

  $idpHandler = $config->getDB()->prepare("SELECT DISTINCT `id`, `entityID` FROM `idps` ORDER BY `entityID`;");
  $testHandler = $config->getDB()->prepare(
    "SELECT `status_OK`, `status_WARNING`, `status_ERROR`, `tests`.`time`
    FROM `tests`, `testRuns`
    WHERE `tests`.`testRun_id` = `testRuns`.`id`
      AND `testRuns`.`idp_id` = :idpId
      AND `test` = :test
      ORDER BY `time` DESC;");
  $testHandler->bindParam(":test",$test);
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run any of the tests</h1>
        <p>Result inside () is older than one year.</p>
        <table class="table table-striped table-bordered">
          <tr>
            <th>IdP</th>
            <th>Assurance</th>
            <th>No&nbsp;EC</th>
            <th>Anonymous</th>
            <th>Pseudonymous</th>
            <th>Personalized</th>
            <th>CoCo v2 part 1</th>
            <th>CoCo v2 part 2</th>
            <th>CoCo v2, outside</th>
            <th>CoCo v1 part 1</th>
            <th>CoCo v1 part 2</th>
            <th>CoCo v1, outside</th>
            <th>REFEDS R&S</th>
            <th>MFA</th>
            <th>ESI</th>
          </tr>' . "\n";

  $idpHandler->execute();
  while ($idp = $idpHandler->fetch(PDO::FETCH_ASSOC)) {
    $testHandler->bindValue(":idpId",$idp['id']);
    printf ("          <tr>\n            <td><a href=\"?tab=AllTests&idp=%s\">%s</a></td>\n", $idp['entityID'], $idp['entityID']);
    foreach ($tests as $test) {
      $testHandler->execute();
      if ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
        printf ('            <td>%s', $testResult['time']> $lastYear ? '' : '(');
        print $testResult['status_OK'] ? "<i class=\"fas fa-check\"></i>" : '';
        print $testResult['status_WARNING'] ? "<i class=\"fas fa-exclamation-triangle\"></i>" : '';
        print $testResult['status_ERROR'] ?"<i class=\"fas fa-exclamation\"></i>" : '';
        printf ('%s</td>%s', $testResult['time']> $lastYear ? '' : ')', "\n");
      } else {
        print "            <td></td>\n";
      }
    }
    print "          </tr>\n";
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showEcsStatus($tested_idps) {
  global $config;
  $lastYear = date('Y-m-d', mktime(0, 0, 0, date("m"),   date("d"),   date("Y")-1));

  $tests = array('anonymous', 'pseudonymous', 'personalized', 'cocov2-1', 'cocov1-1', 'rands');

  $idpHandler = $config->getDB()->prepare("SELECT DISTINCT `id`, `entityID` FROM `idps` ORDER BY `entityID`;");
  $testHandler = $config->getDB()->prepare(
    "SELECT `testResult`, `tests`.`time`
    FROM `tests`, `testRuns`
    WHERE `tests`.`testRun_id` = `testRuns`.`id`
      AND `testRuns`.`idp_id` = :idpId
      AND `test` = :test
      ORDER BY `time` DESC;");
  $testHandler->bindParam(":test",$test);
  print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run any of the tests</h1>
        <p>Result inside () is older than one year.</p>
        <table class="table table-striped table-bordered">
          <tr>
            <th>IdP</th>
            <th>Anonymous</th>
            <th>Pseudonymous</th>
            <th>Personalized</th>
            <th>CoCo v2</th>
            <th>CoCo v1</th>
            <th>REFEDS R&S</th>
            <th>ESI</th>
          </tr>' . "\n";

  $idpHandler->execute();
  while ($idp=$idpHandler->fetch(PDO::FETCH_ASSOC)) {
    $testHandler->bindValue(":idpId",$idp['id']);
    printf ("          <tr>\n            <td><a href=\"?tab=AllTests&idp=%s\">%s</a></td>\n", $idp['entityID'],$idp['entityID']);
    foreach ($tests as $test) {
      $testHandler->execute();
      if ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
        printf ('            <td>%s', $testResult['time']> $lastYear ? '' : '(');
        switch ($testResult['testResult']) {
          case 'Anonymous attributes OK, Entity Category Support OK' :
          case 'Pseudonymous attributes OK, Entity Category Support OK' :
          case 'Personalized attributes OK, Entity Category Support OK' :
          case 'CoCo OK, Entity Category Support OK':
          case 'R&S attributes OK, Entity Category Support OK' :
            print '<i class="fas fa-check"><i class="fas fa-check">';
            break;
          case 'Anonymous attributes OK, Entity Category Support missing' :
          case 'Pseudonymous attributes OK, Entity Category Support missing' :
          case 'Personalized attributes OK, Entity Category Support missing' :
          case 'CoCo OK, Entity Category Support missing' :
          case 'R&S attributes OK, Entity Category Support missing' :
            print '<i class="fas fa-check"><i class="fas fa-exclamation-triangle">';
            break;
          case 'Anonymous attribute missing, Entity Category Support missing' :
          case 'Pseudonymous attribute missing, Entity Category Support missing' :
          case 'Personalized attribute missing, Entity Category Support missing' :
          case 'Support for CoCo missing, Entity Category Support missing':
          case 'R&S attribute missing, Entity Category Support missing' :
            print '<i class="fas fa-exclamation"><i class="fas fa-exclamation-triangle">';
            break;
          case 'Anonymous attributes missing, BUT Entity Category Support claimed';
          case 'Pseudonymous attributes missing, BUT Entity Category Support claimed';
          case 'Personalized attributes missing, BUT Entity Category Support claimed';
          case 'CoCo is not supported, BUT Entity Category Support is claimed';
          case 'R&S attributes missing, BUT Entity Category Support claimed';
            print '<i class="fas fa-exclamation"><i class="fas fa-exclamation">';
            break;
          default :
            print $testResult['testResult'];
        }
        printf ('%s</td>%s', $testResult['time']> $lastYear ? '' : ')', "\n");
      } else
        print "            <td></td>\n";
    }
    $esiStatus = '';
    $esiTime = '';
    $test = 'esi-stud';
    $testHandler->execute();
    if ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
      switch ($testResult['testResult']) {
        case 'schacPersonalUniqueCode OK' :
          $esiStatus = 'check';
          break;
        case 'Missing schacPersonalUniqueCode' :
          $esiStatus = 'exclamation-triangle';
          break;
        default :
          print $testResult['testResult'];
      }
      $esiTime = $testResult['time'];
    }
    if ($esiStatus <> 'check') {
      $test = 'esi';
      $testHandler->execute();
      if ($testResult=$testHandler->fetch(PDO::FETCH_ASSOC)) {
        switch ($testResult['testResult']) {
          case 'schacPersonalUniqueCode OK' :
            $esiStatus = 'check';
            break;
          case 'Missing schacPersonalUniqueCode' :
            $esiStatus = 'exclamation-triangle';
            break;
          default :
            print $testResult['testResult'];
        }
        $esiTime = $testResult['time'];
      }
    }
    if ($esiStatus == '') {
      print "            <td></td>\n";
    } else {
      printf ('            <td>%s<i class="fas fa-%s">%s</td>%s', $esiTime > $lastYear ? '' : '(', $esiStatus, $testResult['time']> $lastYear ? '' : ')', "\n");
    }
    print "          </tr>\n";
  }
  print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showTestsIdP() {
  $display = new \releasecheck\Display();
  $idp = $_GET['idp'];
  if ($testruns = $display->getTestruns($idp, 'entityCategory')) {
    $testrun = $testruns[0];
    if (count($testruns) > 1) {
      print "          <h4>Other results</h4>
        <ul>\n";
      foreach($testruns as $run) {
        printf('            <li><a href="./ops.php?tab=%s&idp=%s&id=%d">%s</a></li>%s', urlencode($_GET['tab']), urlencode($idp), $run['id'], $run['time'], "\n");
        # Check if thus run is requested run. In that vase save this run
        if (isset($_GET['id']) && $_GET['id'] == $run['id']) {
          $testrun = $run;
        }
      }
      print "          </ul>\n";
    }
  } else {
    $testrun = array ('id' => 0, 'time' => 'no run');
  }
  $display->showResultsECTests($idp, $testrun['id']);
}

