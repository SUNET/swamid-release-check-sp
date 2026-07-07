<?php

namespace releasecheck;

/**
 * Class to handle Localization of application
 */
class Localize
{
  private string $locale = '';
  private array $locale2Lang = [
    '' => 'en',
    'en_GB' => 'en',
    'fr_FR' => 'fr',
    'it_IT' => 'it',
    'ro_RO' => 'ro',
    'sr_RS' => 'sr',
    'sv_SE' => 'sv',
  ];

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $this->selectTranslation();
  }

  public function getLang()
  {
    return $this->locale2Lang[$this->locale];
  }

  public function startTranslate()
  {
    if ($this->locale != '') {
      $this->setLocale($this->locale);
    }
  }

  private function selectTranslation()
  {
    if (isset($_GET['lang'])) {
      switch ($_GET['lang']) {
        case 'fr':
          $this->locale = 'fr_FR';
          break;
        case 'it':
          $this->locale = 'it_IT';
          break;
        case 'ro':
          $this->locale = 'ro_RO';
          break;
        case 'sr':
          $this->locale = 'sr_RS';
          break;
        case 'sv':
          # Swedish
          $this->locale = 'sv_SE';
          break;
        case 'en':
        default:
          $this->locale = '';
      }
      $_SESSION['locale'] = $this->locale;
    } elseif (isset($_SESSION['locale'])) {
      $this->locale = $_SESSION['locale'];
    } else {
      $langs = array();

      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        preg_match_all(
          '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.\d+))?/i',
          $_SERVER['HTTP_ACCEPT_LANGUAGE'],
          $lang_parse
        );

        if (count($lang_parse[1])) {
          // create a list like "en" => 0.8
          $langs = array_combine($lang_parse[1], $lang_parse[4]);

          // set default to 1 for any without q factor
          foreach ($langs as $lang => $val) {
            $langs[$lang] = $val === '' ? 1 : $val;
          }

          // sort list based on value
          arsort($langs, SORT_NUMERIC);
        }
      }

      // look through sorted list and use first one that matches our languages
      // https://simplelocalize.io/data/locales/ for other codes
      foreach ($langs as $lang => $val) {
        if ($this->locale == '') {
          switch ($lang) {
            case 'en':
            case 'en-GB':
            case 'en-US':
              $this->locale = 'en_GB';
              break;
            case 'fr':
            case 'fr-CA':
            case 'fr-FR':
              $this->locale = 'fr_FR';
              break;
            case 'it':
            case 'it-IT':
              $this->locale = 'it_IT';
              break;
            case 'ro':
            case 'ro_RO':
              $this->locale = 'ro_RO';
              break;
            case 'sr':
            case 'sr-RS':
              $this->locale = 'sr_RS';
              break;
            case 'sv':
            case 'sv-SE':
              $this->locale = 'sv_SE';
              break;
            default:
          }
        }
      }
    }
  }

  /**
   * Set langauage of translation
   *
   * @param string $locale locale to translae into
   * @return void
   */
  private function setLocale($locale)
  {
    setlocale(LC_MESSAGES, $locale); // Linux
    bindtextdomain('Common', __DIR__ . '/../locale');
    textdomain('Common');
  }
}
