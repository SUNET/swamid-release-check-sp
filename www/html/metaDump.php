<?php

require_once 'vendor/autoload.php';
$config = new \releasecheck\Configuration();

# Script to dump IdP:s info for import at metadata.swamid.se
$testHandler = $config->getDb()->prepare(
  "SELECT `idps`.`entityID`, `tests`.`time`, `tests`.`test`, `tests`.`testResult`
  FROM `idps`, `testRuns`, `tests`
  WHERE `idps`.`id` = `testRuns`.`idp_id`
    AND `testRuns`.`id` = `tests`.`testRun_id`
    AND (`test` = 'anonymous'
      OR `test` = 'pseudonymous'
      OR `test` = 'personalized'
      OR `test` = 'cocov2-1'
      OR `test` = 'cocov1-1'
      OR `test` = 'rands')
  ORDER BY `idps`.`entityID`, `tests`.`test`, `tests`.`time`  DESC;"
);

$metaObj = new \stdClass();

$oldIdP = '';
$oldTest = '';
$testHandler->execute();
while ($testResult = $testHandler->fetch(PDO::FETCH_ASSOC)) {
  if ($oldIdP == $testResult['entityID'] && $oldTest == $testResult['test']) {
    # Skip older results
    continue;
  }
  $partObj = new \stdClass();
  $partObj->entityID = $testResult['entityID'];
  $partObj->test = $testResult['test'];
  $partObj->time = $testResult['time'];
  $partObj->result = $testResult['testResult'];
  $entityArray[] = $partObj;
  unset($partObj);
  $oldIdP = $testResult['entityID'];
  $oldTest = $testResult['test'];
}

$testESIHandler = $config->getDb()->prepare(
  "SELECT `idps`.`entityID`, `tests`.`time`, `tests`.`test`, `tests`.`testResult`
  FROM `idps`, `testRuns`, `tests`
  WHERE `idps`.`id` = `testRuns`.`idp_id`
    AND `testRuns`.`id` = `tests`.`testRun_id`
    AND (`test` = 'esi'
      OR `test` = 'esi-stud')
  ORDER BY `idps`.`entityID`, `tests`.`test` DESC, `tests`.`time`  DESC;"
);
$testESIHandler->execute();
$oldIdP = '';
$oldTest = '';
$ESITestResult = '';
while ($testResult = $testESIHandler->fetch(PDO::FETCH_ASSOC)) {
  if ($oldIdP == $testResult['entityID'] && $oldTest == $testResult['test']) {
    # Skip older results
    continue;
  }
  if ($testResult['test'] == 'esi') {
    if ($ESITestResult == '' || $ESITestResult <> 'schacPersonalUniqueCode OK') {
      $ESITime = $testResult['time'];
      $ESITestResult = $testResult['testResult'];
    }
    $partObj = new \stdClass();
    $partObj->entityID = $testResult['entityID'];
    $partObj->test = 'esi';
    $partObj->time = $ESITime;
    $partObj->result = $ESITestResult;
    $entityArray[] = $partObj;
    unset($partObj);
    $ESITestResult = '';
  } else {
    $ESITime = $testResult['time'];
    $ESITestResult = $testResult['testResult'];
  }
}

$Obj = new \stdClass();
$Obj->meta = $metaObj;
$Obj->objects = $entityArray;

header('Content-type: application/json');
print json_encode($Obj);
