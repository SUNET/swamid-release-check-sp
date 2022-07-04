<?php
# Script to dump IdP:s info for import at metadata.swamid.se
$db = new SQLite3("/var/www/tests/log/idpStatus");
$testHandler = $db->prepare("SELECT * FROM idpStatus WHERE Test='cocov1-1' OR Test='rands';");

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

$Obj = new \stdClass();
$Obj->meta = $metaObj;
$Obj->objects = $entityArray;

header('Content-type: application/json');
print json_encode($Obj);
