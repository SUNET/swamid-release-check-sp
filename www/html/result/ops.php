<?php
include ("../include/functions.php");
$collapseIcons = array();
$tested_idps = array();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://metadata.lab.swamid.se/api/v1/');
curl_setopt($ch, CURLOPT_USERAGENT, 'Release-check/ESI');
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

setupDB();

$randsActive = '';
$cocoActive = '';
$esiActive = '';
$allTestsActive = '';

if (isset($_GET['tab'])) {
	switch ($_GET['tab']) {
		case 'RandS' :
			$randsActive = ' active';
			break;
		case 'CoCo' :
			$cocoActive = ' active';
			break;
		case 'ESI' :
			$esiActive = ' active';
			break;
		case 'AllTests' :
			$allTestsActive = ' active';
			break;
		case 'Download' :
			showDownload($tested_idps);
			exit;
			break;
	}
} 
include ("../include/header.php");
switch ($_SERVER['saml_eduPersonPrincipalName']) {
	case 'bjorn@sunet.se' :
	case 'bmt@bth.se' :
	case 'frkand02@umu.se' :
	case 'paulscot@kau.se' :
	case 'ldc-esw@lu.se' :
	case 'johpe12@liu.se' :
	case 'pax@sunet.se' :
	case 'toylon98@umu.se' :
		break;
	default :
		print "<h1>No access</h1>";
		include ("../include/footer.php");
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
            <a class="nav-link<?=$cocoActive?>" href="?tab=CoCo">CoCo</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$esiActive?>" href="?tab=ESI">ESI</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$allTestsActive?>" href="?tab=AllTests">AllTests</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?tab=Download">Download</a>
          </li>
        </ul>
      </div>
    </div>
<?php
if (isset($_GET['idp']))
	printf ("        <h3>Result for %s</h3>\n",$_GET['idp']);

if (isset($_GET['tab'])) {
	switch ($_GET['tab']) {
		case 'RandS' :
			if (isset($_GET['idp']))
				showResultsSuite1($_GET['idp']);
			else
				showRandS($tested_idps);
			break;
		case 'CoCo' :
			if (isset($_GET['idp']))
				showResultsSuite1($_GET['idp']);
			else
				showCoCo($tested_idps);
			break;
		case 'ESI' :
			if (isset($_GET['idp']))
				showResultsESI($_GET['idp']);
			else
				showESI($tested_idps);
			break;
		case 'AllTests':
			if (isset($_GET['idp']))
				showResultsSuite1($_GET['idp']);
			else
				showAllTests($tested_idps);
			break;
	}
} 

include ("../include/footer.php");

function sends($string,$Attribute) {
	if ( strpos($string, $Attribute) === false ) {
		return false;
	} else {
		return true;
	}
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
	global $db;
	if (isset($_GET["Time"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='rands' ORDER BY Time DESC;");
	else if (isset($_GET["Status"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='rands' ORDER BY length(TestResult) DESC,
			length(Attr_OK) - length(replace(Attr_OK, 'eduPersonPrincipalName', '')) +
			length(Attr_OK) - length(replace(Attr_OK, 'mail', '')) +
			length(Attr_OK) - length(replace(Attr_OK, 'displayName', '')) +
			length(Attr_OK) - length(replace(Attr_OK, 'givenName', '')) +
			length(Attr_OK) - length(replace(Attr_OK, 'sn', ''));");
	else
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='rands' ORDER BY Idp;");

	$okData=0;
	$warnData=0;
	$failData=0;
	$okEC=0;
	$warnEC=0;
	$failEC=0;
	$testResults=$testHandler->execute();
	while ($testResult=$testResults->fetchArray(SQLITE3_ASSOC)) {
		$feed = isset($tested_idps[$testResult["Idp"]]) ? '' : ' (Test)';
		$tested_idps[$testResult["Idp"]] = true;

		printf ("          <tr>\n            <td><a href=\"?tab=RandS&idp=%s\">%s</a>%s</td>\n", $testResult["Idp"],$testResult["Idp"],$feed);
		printf ("            <td>%s</td>\n",$testResult["Time"]);
		switch ($testResult["TestResult"]) {
			case 'R&S attribut OK, Entity Category Support OK' :
			case 'R&S attributes OK, Entity Category Support OK' :
				print "            <td><i class=\"fas fa-check\"></td>\n\t\t\t<td><i class=\"fas fa-check\"></td>\n";
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
			default	:
				print "            <td colspan=\"2\">" . $testResult["TestResult"] . "</td>\n";
		}
		printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s', sends($testResult["Attr_OK"],"eduPersonPrincipalName") ? "check" : "exclamation", "\n", sends($testResult["Attr_OK"],"mail") ? "check" : "exclamation", "\n", sends($testResult["Attr_OK"],"displayName") ? "check" : "exclamation", "\n", sends($testResult["Attr_OK"],"givenName") ? "check" : "exclamation", "\n", sends($testResult["Attr_OK"],"sn") ? "check" : "exclamation", "\n", "\n");
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

function showCoCo($tested_idps) {
	print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run CoCov1-1 test</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <td>Coco data </td>
            <td>
              <i class="fas fa-check"> = Only send reqested data or less</i><br>
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
            <th><a href="?tab=CoCo&Idp">IdP</a></th>
            <th><a href="?tab=CoCo&Time">Tested</a></th>
            <th><a hreF="?tab=CoCo&Status">CoCo data</a></th>
            <th>CoCo ECS</th>
            <th>norEduPersonNIN</th>
            <th>personalIdentityNumber</th>
          </tr>' . "\n";
	global $db;
	if (isset($_GET["Time"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='cocov1-1' ORDER BY Time DESC;");
	else if (isset($_GET["Status"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='cocov1-1' ORDER BY TestResult DESC, length(Attr_OK) - length(replace(Attr_OK, 'norEduPersonNIN', '')) + length(Attr_OK) - length(replace(Attr_OK, 'personalIdentityNumber', ''));");
	else
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='cocov1-1' ORDER BY Idp;");

	$okData=0;
	$warnData=0;
	$failData=0;
	$okEC=0;
	$warnEC=0;
	$failEC=0;
	$testResults=$testHandler->execute();
	while ($testResult=$testResults->fetchArray(SQLITE3_ASSOC)) {
		$feed = isset($tested_idps[$testResult["Idp"]]) ? '' : ' (Test)';
		$tested_idps[$testResult["Idp"]] = true;

		printf ("          <tr>\n            <td><a href=\"?tab=CoCo&idp=%s\">%s</a>%s</td>\n", $testResult["Idp"],$testResult["Idp"],$feed);
		printf ("            <td>%s</td>\n",$testResult["Time"]);
		switch ($testResult["TestResult"]) {
			case "CoCo OK, Entity Category Support OK":
				print "            <td><i class=\"fas fa-check\"></td>\n            <td><i class=\"fas fa-check\"></td>\n";
				$okData++;
				$okEC++;
				break;
			case "CoCo OK, Entity Category Support missing":
			case "CoCo OK, Entity Category Support saknas":
				print "            <td><i class=\"fas fa-check\"></td>\n            <td><i class=\"fas fa-exclamation-triangle\"></td>\n";
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
			default	:
				print "            <td colspan=\"2\">" . $testResult["TestResult"] . "</td>\n";
		}
		printf ('            <td><i class="fas fa-%s"></td>%s            <td><i class="fas fa-%s"></td>%s          </tr>%s', 
		  sends($testResult["Attr_OK"],"norEduPersonNIN") ? "check" : "exclamation", "\n", sends($testResult["Attr_OK"],"personalIdentityNumber") ? "check" : "exclamation", "\n", "\n");
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
	global $db;
	if (isset($_GET["Time"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='esi' ORDER BY Time DESC;");
	else if (isset($_GET["Status"]))
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='esi' ORDER BY length(TestResult) DESC;");
	else
		$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='esi' ORDER BY Idp;");
	$testStudHandler = $db->prepare("SELECT * FROM idpStatus WHERE Idp=:idp AND Test='esi-stud'");
	$testStudHandler->bindParam(':idp', $IdP);

	$ok=0;
	$warn=0;
	$fail=0;
	$okStud=0;
	$warnStud=0;
	$failStud=0;
	$testResults=$testHandler->execute();
	while ($testResult=$testResults->fetchArray(SQLITE3_ASSOC)) {
		$IdP = $testResult["Idp"];
		$feed = isset($tested_idps[$testResult["Idp"]]) ? '' : ' (Test)';
		$tested_idps[$IdP] = true;

		printf ("          <tr>\n            <td><a href=\"?tab=ESI&idp=%s\">%s</a>%s</td>\n", $IdP,$IdP,$feed);
		printf ("            <td>%s</td>\n",$testResult["Time"]);
		switch ($testResult["TestResult"]) {
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
			default	:
				print "            <td>" . $testResult["TestResult"] . "</td>\n";
		}
		$studTest = $testStudHandler->execute();
		if ($testResult=$studTest->fetchArray(SQLITE3_ASSOC)) {
			printf ("            <td>%s</td>\n",$testResult["Time"]);
			switch ($testResult["TestResult"]) {
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
				default	:
					print "            <td>" . $testResult["TestResult"] . "</td>\n";
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

function showAllTests($tested_idps) {
	global $db;
	$tests= array('noec', 'rands', 'cocov1-1', 'cocov1-2', 'cocov1-3','esi');

	$idpHandler = $db->prepare("SELECT DISTINCT Idp FROM idpStatus ORDER BY Idp;");
	$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Idp=:idp AND Test=:test;");
	$testHandler->bindParam(":test",$test);
	print '    <div class="row">
      <div class="col">
        <h1>Data based on IdP:s that have run any of the tests</h1>
        <table class="table table-striped table-bordered">
          <tr>
            <th>IdP</th>
            <th>No EC</th>
            <th>REFEDS R&S</th>
            <th>CoCo part 1</th>
            <th>CoCo part 2</th>
            <th>CoCo, outside</th>
            <th>ESI</th>
          </tr>' . "\n";

	$idps=$idpHandler->execute();
	while ($idp=$idps->fetchArray(SQLITE3_ASSOC)) {
		$testHandler->bindValue(":idp",$idp["Idp"]);
		printf ("          <tr>\n            <td><a href=\"?tab=AllTests&idp=%s\">%s</a></td>\n", $idp["Idp"],$idp["Idp"]);
		foreach ($tests as $test) {
			$testResults=$testHandler->execute();
			if ($testResult=$testResults->fetchArray(SQLITE3_ASSOC)) {
				printf ("            <td>%s<br>", $testResult["Time"]);
				if ($testResult["Status_OK"] ) 
					print "<i class=\"fas fa-check\"></i>";
				if ($testResult["Status_WARNING"] ) 
					print "<i class=\"fas fa-exclamation-triangle\"></i>";
				if ($testResult["Status_ERROR"] ) 
					print "<i class=\"fas fa-exclamation\"></i>";
				print "</td>\n";
				#printf ("<br>%s</td>\n",$testResult["TestResult"]);
			} else 
				print "            <td></td>\n";
		}
		print "          </tr>\n";
	}
	print "        </table>
      </div><!-- End col-->
    </div><!-- End row-->\n";
}

function showDownload($tested_idps) {
	global $db;
	$test2Handler = $db->prepare("SELECT * FROM idpStatus WHERE Test='rands' AND Idp = :Idp;");
	$test3Handler = $db->prepare("SELECT * FROM idpStatus WHERE Test='cocov1-1' AND Idp = :Idp;");
	$ladokHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='ladok' AND Idp = :Idp;");
	$esiHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='esi' AND Idp = :Idp;");
	$test2Handler->bindParam(":Idp",$entityID);
	$test3Handler->bindParam(":Idp",$entityID);
	$ladokHandler->bindParam(":Idp",$entityID);
	$esiHandler->bindParam(":Idp",$entityID);

	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=IdPs.xls");
	header("Pragma: no-cache");
	header("Expires: 0");

	print "IdP\tRS_Tested\tRS_data\tRS_EC\tRS_ePPN\tRS_mail\tRS_displayName\tRS_givenName\tRS_sn\tCoCo_Tested\tCoCo_data\tCoCo_EC\tCoco_norEduPersonNIN\tCoco_personalIdentityNumber\tCoCo_status\tLadok_Tested\tLadok_Staff\tLadok_Student\tESI_Tested\tESI_data\n";


	foreach ($tested_idps as $entityID => $value) {
		print ($entityID);
		$test2Result = $test2Handler->execute();
		if ($test2 = $test2Result->fetchArray(SQLITE3_ASSOC)) {
			printf ("\t%s",$test2["Time"]);
			switch ($test2["TestResult"]) {
				case "R&S attribut OK, Entity Category Support OK":
				case "R&S attributes OK, Entity Category Support OK":
					print "\tOK\tOK";
					break;
				case "R&S attributes OK, Entity Category Support missing":
				case "R&S attribut OK, Entity Category Support saknas":
					print "\tOK\tWARN";
					break;
				case "R&S attribute missing, Entity Category Support missing":
					print "\tFAIL\t";
					break;
				case "CoCo is not supported, BUT Entity Category Support is claimed":
					print "\tFAIL\tFAIL";
					break;
				default	:
					print "\t\t".$test2["TestResult"];
			}

			printf ("\t%s",sends($test2["Attr_OK"],"eduPersonPrincipalName") ? "OK" : "FAIL");
			printf ("\t%s",sends($test2["Attr_OK"],"mail") ? "OK" : "FAIL");
			printf ("\t%s",sends($test2["Attr_OK"],"displayName") ? "OK" : "FAIL");
			printf ("\t%s",sends($test2["Attr_OK"],"givenName") ? "OK" : "FAIL");
			printf ("\t%s",sends($test2["Attr_OK"],"sn") ? "OK" : "FAIL");
		} else
			print "\t\t\t\t\t\t\t\t";

		$test3Result = $test3Handler->execute();
		if ($test3 = $test3Result->fetchArray(SQLITE3_ASSOC)) {
			printf ("\t%s",$test3["Time"]);
			switch ($test3["TestResult"]) {
				case "CoCo OK, Entity Category Support OK":
					print "\tOK\tOK";
					$CoCoStatus=0;
					break;
				case "CoCo OK, Entity Category Support missing":
				case "CoCo OK, Entity Category Support saknas":
					print "\tOK\tWARN";
					$CoCoStatus=0;
					break;
				case "Support for CoCo missing, Entity Category Support missing":
					print "\tFAIL\t";
					$CoCoStatus=2;
					break;
				case "CoCo is not supported, BUT Entity Category Support is claimed":
					print "\tFAIL\tFAIL";
					$CoCoStatus=2;
					break;
				default	:
					print "\t\t".$test3["TestResult"] ;
					$CoCoStatus=2;
			}

			printf ("\t%s",sends($test3["Attr_OK"],"norEduPersonNIN") ? "OK" : "FAIL");
			printf ("\t%s",sends($test3["Attr_OK"],"personalIdentityNumber") ? "OK" : "FAIL");
			printf ("\t%s",($CoCoStatus == 0 && sends($test3["Attr_OK"],"norEduPersonNIN") && sends($test3["Attr_OK"],"personalIdentityNumber")) ? "OK" : "FAIL");
		} else
			print "\t\t\t\t\t\tFAIL";

		$ladokResult = $ladokHandler->execute();
		if ($ladok = $ladokResult->fetchArray(SQLITE3_ASSOC)) {
			printf ("\t%s",$ladok["Time"]);
			switch ($ladok["TestResult"]) {
				case "OK: Staff & Stud":
				case "OK: Staff & Student":
					print "\tOK\tOK";
					break;
				case "OK: Staff":
					print "\tOK\tFAIL";
					break;
				case "OK: Stud":
				case "OK: Student":
					print "\tFAIL\tOK";
					break;
				case "FAIL":
					print "\tFAIL\tFAIL";
					break;
				default	:
					print "\t\t".$ladok["TestResult"] ;
			}

		} else
			print "\t\t";

		$esiResult = $esiHandler->execute();
		if ($esi = $esiResult->fetchArray(SQLITE3_ASSOC)) {
			printf ("\t%s",$esi["Time"]);
			switch ($esi["TestResult"]) {
				case 'schacPersonalUniqueCode OK':
					print "\tOK";
					break;
				case 'Missing schacPersonalUniqueCode':
				case 'More than one schacPersonalUniqueCode';
					print "\tWARN";
					break;
				case 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
				case 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
					print "\tFAIL";
					break;
				default	:
					print "\t".$esi["TestResult"] ;
			}

		} else
			print "\t";


		print "\n";
	}
}
