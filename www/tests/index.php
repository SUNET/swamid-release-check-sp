<?php

//Load composer's autoloader
require_once '../html/vendor/autoload.php';

$config = new \releasecheck\Configuration();

$test = str_replace('.' . $config->basename(), '', strtolower($_SERVER['HTTP_HOST']));
$quickTest = isset($_GET['quickTest']);
$singleTest = isset($_GET['singleTest']);

$testSuite = $config->getExtendedClass('TestSuite');

if ($testInfo = $testSuite->getTest($test)) {
  if (! $order = $testSuite->getOrder($test)) {
    $order = array ('last' => '', 'next' => 'result');
  }

  $IdPTest = $config->getExtendedClass(
    'IdPCheck',
    $test,
    $testInfo['name'],
    $testInfo['tab'],
    $testInfo['expected'],
    $testInfo['nowarn']
  );

  if ($quickTest) {
    $IdPTest->testAttributes($testInfo['subtest'], $order['next']);
  } else {
    $queryStr = isset($_GET['singleTest']) ? 'singleTest&' : '';
    foreach (['session'] as $param) {
      $queryStr .= isset($_GET[$param]) ? sprintf('%s=%s&', $param, urlencode($_GET[$param])) : '';
    }
    $html = $config->getExtendedClass('HTML');
    $html->showHTMLHead($testInfo['name']);
    $html->showContentHeader($queryStr);
    $IdPTest->showTestHeaders($order['last'], $order['next'], $singleTest);
    $IdPTest->testAttributes($testInfo['subtest']);
    $html->showContentFooter();
    $html->showScripts();
  }
} else {
  print "Unknown test : $test";
  exit;
}
