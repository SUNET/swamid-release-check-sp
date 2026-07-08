<?php

namespace releasecheck;

use Throwable;

class HTML
{
  /**
   * Configuration of application
   */
  protected Configuration $config;

  /**
   * List of tables to sort on page
   */
  private array $tableToSort = array();

  protected array $federation = array();

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    global $config;
    if (isset($config)) {
      $this->config = $config;
    } else {
      $this->config = new Configuration();
    }
    $this->federation = $this->config->getFederation();
  }

  /**
   * Print start of webpage
   *
   * @param string $title String to be added in title
   *
   * @return void
   */
  public function showHTMLHead($title = "")
  {
    if ($title == "") {
      $title = _('Release check for') . ' ' . $this->config->getFederation()['displayName'];
    }
    $bgColor = 'background-color: ' . ($this->config->getFederation()['backgroundColor'] ?? "unset");
    printf(
      '<!DOCTYPE html>%s<html lang="en" xml:lang="en">%s  <head>
    <meta charset="UTF-8">
    <title>%s</title>
    <link href="//%s/assets/fontawesome/css/fontawesome.min.css" rel="stylesheet">
    <link href="//%s/assets/fontawesome/css/solid.min.css" rel="stylesheet">
    <link href="//%s/assets/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
      integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/images/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/images/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    %s  </head>%s<body style="%s">%s  <div class="container">%s',
      "\n",
      "\n",
      $title,
      $this->config->basename(),
      $this->config->basename(),
      $this->config->basename(),
      "\n",
      "\n",
      $bgColor,
      "\n",
      "\n"
    );
  }

  public function showContentHeader($queryParams = '')
  {
    $localize = new \releasecheck\Localize();
    $flag = $this->config->getLanguages()[$localize->getLang()]['flag'];
    $header = '    <div class="header">';
    $defaultHeader = '<nav>
        <ul class="nav nav-pills float-right">
          <li role="presentation" class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"' .
      ' aria-expanded="false">
              <img src="https://flagcdn.com/h20/' . $flag . '.png"
                srcset="https://flagcdn.com/h40/' . $flag . '.png 2x, https://flagcdn.com/h60/' . $flag . '.png 3x"
                height="20"
                alt="' . $localize->getLang() . '"> ' . _('Language') . '</a>
            <div class="dropdown-menu">' . "\n";
    if ($queryParams == '') {
      $queryString = '?lang=';
    } else {
      $queryString = '?' . $queryParams . 'lang=';
    }
    foreach ($this->config->getLanguages() as $lang => $info) {
      $defaultHeader .= sprintf(
        '              <a class="dropdown-item" href="%s%s">
                <img src="https://flagcdn.com/h20/%s.png"
                  srcset="https://flagcdn.com/h40/%s.png 2x, https://flagcdn.com/h60/%s.png 3x"
                  height="20"
                  alt="%s"> %s (%s)
              </a>%s',
        $queryString,
        $lang,
        $info['flag'],
        $info['flag'],
        $info['flag'],
        $info['name'],
        $info['name'],
        $lang,
        "\n"
      );
    }
    $defaultHeader .= sprintf(
      '            </div>
          </li>
          <li role="presentation" class="nav-item">
            <a href="%s" class="nav-link">' . _('About %s') . '</a>
          </li>
          <li role="presentation" class="nav-item">
            <a href="%s" class="nav-link">%s</a>
          </li>
        </ul>
      </nav>
      <h3 class="text-muted">
        <a href="https://%s">
          <img alt = "%s Logo" src="%s" width="%d" height="%d">
        </a> Release-check
      </h3>%s',
      $this->federation['aboutURL'],
      $this->federation['displayName'],
      $this->federation['contactURL'],
      _('Contact us'),
      $this->config->basename(),
      $this->federation['displayName'],
      $this->federation['logoURL'],
      $this->federation['logoWidth'],
      $this->federation['logoHeight'],
      "\n"
    );
    $customHeader = $this->getPageContent("header");
    if (false === $customHeader) {
      $header .= $defaultHeader;
    } else {
      $header .= $customHeader;
    }
    echo $header . "    </div>\n";
  }

  /**
   * Print footer
   *
   * @return void
   */
  public function showContentFooter()
  {
    $footer = sprintf('    <div class="footer"></div>%s', "\n");
    $customFooter = $this->getPageContent("footer");
    if (false !== $customFooter) {
      $footer = $customFooter;
    }
    echo $footer;
  }

  /**
   * Parse custom content for given location
   *
   * @return string|false
   */
  public function getPageContent($location)
  {
    $content = false;
    if (!empty($this->config->getTemplate()[$location])) {
      switch ($this->config->getTemplate()[$location]["src"] ?? "") {
        case 'config':
          $content = ($this->config->getTemplate()[$location]["template"] ?? "");
          break;
        case 'file':
          $path = __DIR__  . "/../templates/{$location}.php";
          if (file_exists($path)) {
            try {
              ob_start();
              include $path;
              $content = ob_get_clean();
            } catch (Throwable $e) {
              ob_end_clean();
              $content = "<script>console.warn('{$e->getMessage()}')</script>";
            }
          }
          break;
        default:
          $content = false;
          break;
      }
    }
    return $content;
  }

  /**
   * Print footer of webpage
   *
   * @return void
   */
  public function showScripts($collapseIcons = array())
  {
    printf('  </div><!-- End container-->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
    integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
  </script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
  </script>%s', "\n");
    if (isset($this->tableToSort[0]) || isset($collapseIcons[0])) {
      if (isset($this->tableToSort[0])) {
        # Add JS script to be able to use later
        printf('  <script type="text/javascript" charset="utf8"
      src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>%s', "\n");
      }
      print "  <script>\n";
      if (isset($collapseIcons[0])) {
        printf('    $(function () {%s', "\n");
        foreach ($collapseIcons as $collapseIcon) {
          printf("      $('#%s').on('show.bs.collapse', function () {
        var tag_id = document.getElementById('%s-icon');
        tag_id.className = \"fas fa-chevron-circle-down\";
      })
      $('#%s').on('hide.bs.collapse', function () {
        var tag_id = document.getElementById('%s-icon');
        tag_id.className = \"fas fa-chevron-circle-right\";
      })\n", $collapseIcon, $collapseIcon, $collapseIcon, $collapseIcon);
        }
        printf('    })%s', "\n");
      }

      # Add function to sort if needed
      if (isset($this->tableToSort[0])) {
        print "    $(document).ready(function () {\n";
        foreach ($this->tableToSort as $table) {
          printf("      $('#%s').DataTable( {paging: false});\n", $table);
        }
        print "    });\n";
      }
      print "  </script>\n";
    }
    printf('  </body>%s</html>', "\n");
  }

  /**
   * Add table that should be sorted
   *
   * Added as script/DataTable when footer is generated.
   *
   * @return void
   */
  public function addTableSort($tableId)
  {
    $this->tableToSort[] = $tableId;
  }
}
