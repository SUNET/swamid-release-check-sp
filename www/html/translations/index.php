<?php

$pot_dir = '../locale/';
require_once '../vendor/autoload.php';

const LC_COMMON = '/LC_MESSAGES/Common.po';

$config = new \releasecheck\Configuration();
$loader = new \Gettext\Loader\PoLoader();

$html = $config->getExtendedClass('HTML');
$helper = $config->getExtendedClass('TranslationHelper');

if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['file']) && isset($_GET['type'])) {
  $helper->download($_GET['type'], $_GET['file']);
}

$html->showHTMLHead();
$html->showContentHeader();
printf(
  '    <div class="row">
      <div class="col">%s',
  "\n"
);
if (isset($_GET['action']) && isset($_GET['file'])) {
  if ($_GET['action'] == 'showInfo' && isset($_GET['type'])) {
    $helper->showInfo($_GET['type'], $_GET['file']);
  } elseif ($_GET['action'] == 'showCompare' && isset($_GET['pot'])) {
    $helper->showCompare($_GET['pot'], $_GET['file']);
  } else {
    $helper->showOverview();
  }
} else {
  $helper->showOverview();
}
printf(
  '      </div>
    </div>%s',
  "\n"
);
$html->showContentFooter();
$html->showScripts();
