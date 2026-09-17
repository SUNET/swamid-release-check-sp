<?php

require_once __DIR__ . '/../html/vendor/autoload.php';
$config = new \releasecheck\Configuration();

#'maxMonth'
#'maxTestRuns'
$checkAgeHandler = $config->getDb()->prepare(
  'SELECT `id`
  FROM `testRuns`
  WHERE `time` < :Before;'
);

$checkCountHandler = $config->getDb()->prepare(
  'SELECT `idp_id`, COUNT(`id`) AS count
  FROM `testRuns`
  GROUP BY `idp_id`
  ORDER BY count DESC;'
);
$checkUnusedIdpHandler = $config->getDb()->prepare(
  'SELECT `id`
  FROM `idps`
  WHERE `id` NOT IN (SELECT `idp_id` FROM `testRuns`);'
);

$getTestRunsForIdp = $config->getDb()->prepare(
  'SELECT `id`, `time`
  FROM `testRuns`
  WHERE `idp_id` = :Id
  ORDER BY time;'
);

$removeTestsHandler = $config->getDb()->prepare(
  'DELETE FROM `tests` WHERE `testRun_id`= :Id;'
);
$removeTestRunsHandler = $config->getDb()->prepare(
  'DELETE FROM `testRuns` WHERE `id`= :Id;'
);

$removeIdpHandler = $config->getDb()->prepare(
  'DELETE FROM `idps` WHERE `id`= :Id;'
);

$year = intval(date('Y', time()));
$month = intval(date('m', time()));
$day = intval(date('d', time()));
$removeDate = date('Y-m-d', mktime(0, 0, 0, $month - $config->getFederation()['maxMonth'], $day, $year));

$checkAgeHandler->execute(['Before' => $removeDate]);

while ($testRun = $checkAgeHandler->fetch(PDO::FETCH_ASSOC)) {
  $removeTestsHandler->execute(['Id' => $testRun['id']]);
  $removeTestRunsHandler->execute(['Id' => $testRun['id']]);
}

$checkCountHandler->execute();
$max = $config->getFederation()['maxTestRuns'];
while ($idp = $checkCountHandler->fetch(PDO::FETCH_ASSOC)) {
  if ($max < $idp['count']) {
    $count = $idp['count'] - $max;
    $getTestRunsForIdp->execute(['Id' => $idp['idp_id']]);
    while ($count > 0 && $testRun = $getTestRunsForIdp->fetch(PDO::FETCH_ASSOC)) {
      $count--;
      $removeTestsHandler->execute(['Id' => $testRun['id']]);
      $removeTestRunsHandler->execute(['Id' => $testRun['id']]);
    }
  }
}

$checkUnusedIdpHandler->execute();
while ($idp = $checkUnusedIdpHandler->fetch(PDO::FETCH_ASSOC)) {
  $removeIdpHandler->execute(['Id' => $idp['id']]);
}
