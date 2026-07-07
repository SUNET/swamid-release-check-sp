<?php

$pot_dir = '../locale/';
require_once '../vendor/autoload.php';

const LC_COMMON = '/LC_MESSAGES/Common.po';

$config = new \releasecheck\Configuration();
$loader = new \Gettext\Loader\PoLoader();

$html = $config->getExtendedClass('HTML');

if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['file']) && isset($_GET['type'])) {
  download($_GET['type'], $_GET['file']);
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
    showInfo($_GET['type'], $_GET['file']);
  } elseif ($_GET['action'] == 'showCompare' && isset($_GET['pot'])) {
    showCompare($_GET['pot'], $_GET['file']);
  } else {
    showOverview();
  }
} else {
  showOverview();
}
printf(
  '      </div>
    </div>%s',
  "\n"
);
$html->showContentFooter();
$html->showScripts();
