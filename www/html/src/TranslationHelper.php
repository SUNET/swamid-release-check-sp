<?php

namespace releasecheck;

class TranslationHelper
{
  public function showOverview()
  {
    global $loader, $pot_dir;
    printf('        <h3>POT files to translate :</h3>
          <table class="table table-striped table-bordered">
            <tr><th>File</th><th># of msgids</th><th>Last updated</th></tr>%s', "\n");

    if ($pot_dh = opendir($pot_dir)) {
      while (false !== ($pot_file = readdir($pot_dh))) {
        $fullPath = $pot_dir . '/' . $pot_file;
        if (is_file($fullPath) && substr($pot_file, -4) == '.pot') {
          $translations = $loader->loadFile($fullPath);
          printf(
            '          <tr><th><a href="?action=showInfo&type=pot&file=%s">%s</a></th><td>%d</td><td>%s</td></tr>%s',
            $pot_file,
            $pot_file,
            $translations->count(),
            $this->convertDateTime($translations->getHeaders()->get('POT-Creation-Date')),
            "\n"
          );
        }
      }
      closedir($pot_dh);
    }
    printf('        </table>
        <br>
        <h3>Translated PO files :</h3>
        <table class="table table-striped table-bordered">
          <tr>
            <th>File</th>
            <th># translated</th>
            <th># not translated in .po</th>
            <th>Date of translation</th>
          </tr>%s', "\n");

    if ($pot_dh = opendir($pot_dir)) {
      while (false !== ($lang_dir = readdir($pot_dh))) {
        $dirPath = $pot_dir . '/' . $lang_dir;
        $fullPath = $pot_dir . '/' . $lang_dir . LC_COMMON;
        if (is_dir($dirPath) && is_file($fullPath)) {
          $translations = $loader->loadFile($fullPath);
          $translated = 0;
          foreach ($translations->getTranslations() as $msg) {
            $translated += $msg->isTranslated() ? 1 : 0;
          }
          printf(
            '          <tr><th><a href="?action=showInfo&type=po&file=%s">%s</a>' .
            '</th><td>%d</td><td>%d</td><td>%s</td></tr>%s',
            $lang_dir,
            $lang_dir,
            $translated,
            $translations->count() - $translated,
            $this->convertDateTime($translations->getHeaders()->get('PO-Revision-Date')),
            "\n"
          );
        }
      }
      closedir($pot_dh);
    }
    printf('        </table>%s', "\n");
  }

  public function showInfo($type, $file)
  {
    global $loader, $pot_dir;

    $fullPath = $pot_dir . '/' . basename($file);
    $fullPath .= $type == 'pot' ? '' : LC_COMMON;

    printf('        <h3>Info about file :</h3>
        <table class="table table-striped table-bordered">
          <tr><th>File</th><td>%s</td></tr>%s', htmlspecialchars($file), "\n");

    if (is_file($fullPath)) {
      $translations = $loader->loadFile($fullPath);
      if ($type == 'pot') {
        printf(
          '          <tr><th># of msgid\'s<td>%d</td></tr>
          <tr><th>Created</th><td>%s</td></tr>%s',
          $translations->count(),
          convertDateTime($translations->getHeaders()->get('POT-Creation-Date')),
          "\n"
        );
      } else {
        $translated = 0;
        foreach ($translations->getTranslations() as $msg) {
          $translated += $msg->isTranslated() ? 1 : 0;
        }
        printf(
          '          <tr><th># translated</th><td>%d</td></tr>
          <tr><th># not translated in .po</th><td>%d</td></tr>
          <tr><th>Date of translation</th><td>%s</td></tr>
          <tr><th>Date of POT-file translation is based on</th><td>%s</td></tr>
          <tr><th>Last Translator</th><td>%s</td></tr>%s',
          $translated,
          $translations->count() - $translated,
          $this->convertDateTime($translations->getHeaders()->get('PO-Revision-Date')),
          $this->convertDateTime($translations->getHeaders()->get('POT-Creation-Date')),
          htmlspecialchars($translations->getHeaders()->get('Last-Translator')),
          "\n"
        );
        print '          <tr><td colspan="2">Compare with : ';
        if ($pot_dh = opendir($pot_dir)) {
          while (false !== ($pot_file = readdir($pot_dh))) {
            $fullPath = $pot_dir . '/' . $pot_file;
            if (is_file($fullPath) && substr($pot_file, -4) == '.pot') {
              printf(
                '<a href="?action=showCompare&pot=%s&file=%s">' .
                '<button type="button" class="btn btn-primary">%s</button></a> ',
                $pot_file,
                htmlspecialchars($file),
                $pot_file,
                "\n"
              );
            }
          }
          closedir($pot_dh);
        }
        print "</td></tr>\n";
      }
    }
    printf(
      '        </table>
        <a href="?action=download&type=%s&file=%s"><button type="button" class="btn btn-primary">Download</button></a>
        <a href="."><button type="button" class="btn btn-primary">Back</button></a>
        <br><br>%s',
      htmlspecialchars($type),
      htmlspecialchars($file),
      "\n"
    );
  }

  public function showCompare($pot, $file)
  {
    global $loader, $pot_dir;

    $poPath = $pot_dir . '/' . basename($file) . LC_COMMON;
    $potPath = $pot_dir . '/' . basename($pot);

    printf(
      '        <h3>Missing translations in %s compared to %s:</h3>
        <ul>%s',
      htmlentities(basename($file)),
      htmlentities(basename($pot)),
      "\n"
    );

    if (is_file($poPath) && is_file($potPath)) {
      $poTranslations = $loader->loadFile($poPath)->getTranslations();
      $potIds = $loader->loadFile($potPath);

      $translated = 0;
      $notTranslated = 0;
      $missing = 0;
      foreach ($potIds->getTranslations() as $key => $msg) {
        if (isset($poTranslations[$key])) {
          if ($poTranslations[$key]->isTranslated()) {
            $translated++;
          } else {
            $notTranslated++;
            printf(
              '          <li><i class="fas fa-exclamation-triangle"></i>%s</li>%s',
              $key,
              "\n"
            );
          }
        } else {
          $missing++;
          printf(
            '          <li><i class="fas fa-exclamation"></i> %s</li>%s',
            $key,
            "\n"
          );
        }
      }
    }
    printf(
      '        </ul>
        Status : <br>
        <ul>
          <li>%d <i class="fas fa-check"></i> (translated)</li>
          <li>%d <i class="fas fa-exclamation-triangle"></i> (missing translation)</li>
          <li>%d <i class="fas fa-exclamation"></i> (missing)</li>
        </ul>
        <a href="?action=showInfo&type=po&file=%s"><button type="button" class="btn btn-primary">Back</button></a>
        <br><br>%s',
      $translated,
      $notTranslated,
      $missing,
      urlencode($file),
      "\n"
    );
  }

  public function download($type, $file)
  {
    global $pot_dir;

    $fullPath = $pot_dir . '/' . basename($file);
    $fullPath .= $type == 'pot' ? '' : LC_COMMON;
    if (is_file($fullPath)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($fullPath));
      readfile($fullPath);
      exit;
    }
  }

  public function convertDateTime($dateTime)
  {
    $utch = substr($dateTime, 17, 2);
    $utcm = substr($dateTime, 19, 2);
    return date(
      "Y-m-d H:i \U\T\C",
      mktime(
        substr($dateTime, 11, 2) - $utch,
        substr($dateTime, 14, 2) - $utcm,
        0,
        substr($dateTime, 5, 2),
        substr($dateTime, 8, 2),
        substr($dateTime, 0, 4)
      )
    );
  }
}
