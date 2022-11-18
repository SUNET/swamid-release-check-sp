<?php
# Script to dump IdP:s info for import at metadata.swamid.se
$db = new SQLite3("/var/www/tests/log/idpStatus");
$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='anonymous' OR Test='pseudonymous' OR Test='personalized' OR Test='cocov2-1' OR Test='cocov1-1' OR Test='rands';");

$metaObj = new \stdClass();

$testResults=$testHandler->execute();
while ($testResult=$testResults->fetchArray(SQLITE3_ASSOC)) {
	$partObj = new \stdClass();
	$partObj->entityID = $testResult['Idp'];
	$partObj->test = $testResult['Test'];
	$partObj->time = $testResult['Time'];
	$partObj->result = $testResult['TestResult'];
	$entityArray[] = $partObj;
	unset($partObj);
}

$testESIHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='esi' OR Test='esi-stud' ORDER BY Idp, Test DESC;");
$testESIResults=$testESIHandler->execute();
$ESITestResult = '';
while ($testResult=$testESIResults->fetchArray(SQLITE3_ASSOC)) {
	if ($testResult['Test'] == 'esi') {
		if ($ESITestResult == '') {
			$ESITime = $testResult['Time'];
			$ESITestResult = $testResult['TestResult'];
		} elseif ($ESITestResult <> 'schacPersonalUniqueCode OK') {
			$ESITime = $testResult['Time'];
			$ESITestResult = $testResult['TestResult'];
		}
		$partObj = new \stdClass();
		$partObj->entityID = $testResult['Idp'];
		$partObj->test = 'esi';
		$partObj->time = $ESITime;
		$partObj->result = $ESITestResult;
		$entityArray[] = $partObj;
		unset($partObj);
		$ESITestResult = '';
	} else {
		$ESITime = $testResult['Time'];
		$ESITestResult = $testResult['TestResult'];
	}
}

$Obj = new \stdClass();
$Obj->meta = $metaObj;
$Obj->objects = $entityArray;

header('Content-type: application/json');
print json_encode($Obj);
