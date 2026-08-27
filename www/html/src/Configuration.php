<?php

namespace releasecheck;

use PDO;
use PDOException;

class Configuration
{
  /**
   * Basename of the application
   *
   * Should be the hostname without htt(s)://
   *
   */
  private string $basename = '';

  /**
   * Array of languages to offer translation for
   * key = lang (sv for sweden NOT se)
   * array of
   * * name (Name of language in local spelling)
   * * flag (flag could be sv)
   */
  private array $languages = array();

  /**
   * Informatiom about the federation running the application
   */
  private array $federation = array();

  /**
   * UI template configuration
   *
   */
  private array $template = array ();

  /**
   * The database connection
   */
  private PDO $db;

  /**
   * Setup the class
   *
   * @param bool $startDB If we should start the database connection or not.
   *
   * @return void
   */
  public function __construct($startDB = true)
  {
    $localize = new \releasecheck\Localize();
    $localize->startTranslate();
    include __DIR__ . '/../config.php'; # NOSONAR

    $reqParams = array('db', 'basename', 'federation', 'languages');
    $reqParamsDB = array('servername', 'username', 'password',
      'name');
    $reqParamsFederation = array(
      'displayName', 'adminUsers',
      'aboutURL', 'contactURL',
      'logoURL', 'logoWidth', 'logoHeight'
      );

    foreach ($reqParams as $param) {
      if (! isset(${$param})) {
        printf('Missing %s in config.php<br>', $param);
        exit;
      }
    }

    /* we have checked above that the variable exists
    @phpstan-ignore variable.undefined */
    $this->basename = $basename;

    # Language array
    /* we have checked above that the variable exists
    @phpstan-ignore variable.undefined */
    $this->languages = $languages;

    if (! isset($federation['hideTest'])) {
      $federation['hideTest'] = ['rands' => false,
        'anonymous' => false,
        'pseudonymous' => false,
        'personalized' => false,
        'cocov2' => false,
        'esi' => false];
    } else {
      foreach (['rands', 'anonymous', 'pseudonymous', 'personalized', 'cocov2', 'esi'] as $test) {
        if (! isset($federation['hideTest'][$test])) {
          $federation['hideTest'][$test] = false;
        }
      }
    }

    $defaultValuesFederation = array(
      'extend' => '',
      'DSType' => 'thiss.io',
      'DS' => 'service.seamlessaccess.org',
      'LoginURL' => 'Login',
      'instructionsEntityCategory' => '<p>' .
        sprintf(_('In order for %s to work as effectively as possible for students and employees as well as for' .
          ' service providers and identity providers, %s recommends that service providers use entity categories' .
          ' to get the attributes that they require.'), $federation['displayName'], $federation['displayName']) . '</p>
          <p>' .
        sprintf(
          _('In order for services within the %s federation to work as effectively as possible, %s recommends' .
          ' the use of entity categories. Entity categories benefits not only students and employees but also' .
          ' administrators of relying and identity providers by providing a stable framework for the release' .
          ' of attributes.'),
          $federation['displayName'],
          $federation['displayName']
        ) . '</p>
          <p>' .
        sprintf(
          _('The %s best practice attribute release check consists of the following tests'),
          $federation['displayName']
        ) . ':</p>',
      'instructionsEntityCategoryEnd' => '<p>' .
        sprintf(
          _('Multiple Code of Conduct test require different attributes which the IdP either SHOULD or SHOULD NOT ' .
          'release in accordance REFEDS/%s Code of Conduct.'),
          'GÉANT'
        ) . '</p>',
    );
    $defaultValuesFederation['instructionsAttributes'] = '<p>' .
        _('Click on the green button to see what attributes your Identity Provider releases.') . '</p>
          <p>' . sprintf(_('Description of all test available in the %s test suite'), $federation['displayName']) . ':
            <ul>
              <li>' . _('The Attributes tab shows all attributes the service release to the entityId') .
        sprintf(' https://%s/shibboleth. ', $this->basename) . _('The entityId uses the entity categories') . ':<ul>
                <li>' . _('REFEDS Personalized Access Entity Category') . ',</li>
                <li>' . _('REFEDS Research and Scholarship Entity Category, and') . '</li>
                <li>' . _('REFEDS Data Protection Code of Conduct ver 2.0 Entity Category') . '.</li>
              </ul></li>
              <li>' . _('The Authentication tab checks if an Identity Provider is correctly configured for' .
        ' handling request for different AuthnContextClassRef requests.') . '</li>
              <li>' . _('The Entity category tab does an extensive testing of that an Identity Provider follows' .
        ' Best Practice for attribute release via entity categories.');
    $defaultValuesFederation['instructionsAttributes'] .= $federation['hideTest']['esi'] ? '' : sprintf('</li>
              <li>' . _('The ESI tab verifies if the Identity Provider release the right attributes for the' .
        ' European Digital Student Service Infrastructure.'));
    $defaultValuesFederation['instructionsAttributes'] .= '</li>
            </ul>
          </p>';

    if (! isset($template)) {
      # set default values if not configured
      $template = array(
        'header' => array(
          'src' => 'self',
          'template' => '',
        ),
        'body' => array(),
        'footer' => array(
          'src' => 'self',
          'template' => '',
        )
      );
    }

    $this->checkParams($db, $reqParamsDB, 'db');

    $this->checkParams($federation, $reqParamsFederation, 'federation', $defaultValuesFederation);

    # Federation params
    $this->federation = $federation;

    # Header/Footer content
    $this->template = $template;

    # Database
    if ($startDB) {
      $this->startDB($db);
    }
  }

  /**
   * Check if all requied parameters is present
   *
   * @param array $checkParam Parameter array to check
   *
   * @param array $reqParams Required parameters in checkParam
   *
   * @param string $nameOfParam Name of array in config
   *
   * @return void
   */
  private function checkParams(&$checkParam, $reqParams, $nameOfParam, $defaultValues = array())
  {
    foreach ($defaultValues as $param => $defaultValue) {
      if (! isset($checkParam[$param])) {
        $checkParam[$param] = $defaultValue;
      }
    }
    foreach ($reqParams as $param) {
      if (! isset($checkParam[$param])) {
        printf('Missing $%s[%s] in config.php<br>', $nameOfParam, $param);
        exit;
      }
    }
  }

  /**
   * Start up database connection
   *
   * @param array $db Parametere for the database
   */
  private function startDB($db)
  {
    $options = array();
    if (isset($db['caPath'])) {
      $options[PDO::MYSQL_ATTR_SSL_CA] = $db['caPath'];
    }
    try {
      $dbDSN = sprintf('mysql:host=%s;dbname=%s', $db['servername'], $db['name']);
      $this->db = new PDO($dbDSN, $db['username'], $db['password'], $options);
      // set the PDO error mode to exception
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
    $this->checkDBVersion();
  }

  /**
   * Checks version of DB and update if needed
   *
   * @return void
   */
  private function checkDBVersion()
  {
    try {
      $dbVersionHandler = $this->db->query("SELECT `value` FROM `params` WHERE `id` = 'dbVersion';");
      $dbVersionResult = $dbVersionHandler->fetch(PDO::FETCH_ASSOC);
      $dbVersion = $dbVersionResult['value'];
    } catch (PDOException $e) {
      $dbVersion = 0;
    }
    if ($dbVersion < 1) {
      $this->createTables();
    }
    if ($dbVersion < 2) {
      $this->db->query('ALTER TABLE `testRuns`
          CHANGE `session` `session` varchar(40) DEFAULT NULL');
      $this->db->query("UPDATE params SET value = 1 WHERE `id` = 'dbVersion'");
    }
  }

  /**
   * Create tables if not already exists
   *
   * Run the first time of use
   *
   * @return void
   */
  private function createTables()
  {
    $this->db->query(
      'CREATE TABLE `params` (
        `id` varchar(20) DEFAULT NULL,
        `value` text DEFAULT NULL
      );'
    );
    $this->db->query(
      "INSERT INTO `params` (`id`, `value`) VALUES ('dbVersion', '2');"
    );

    $this->db->query(
      'CREATE TABLE `idps` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `entityID` varchar(256) DEFAULT NULL,
        registrationAuthority varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      );'
    );

    $this->db->query(
      'CREATE TABLE `testRuns` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `idp_id` int(10) unsigned NOT NULL,
        `session` varchar(40) DEFAULT NULL,
        `time` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idp_id` (`idp_id`),
        CONSTRAINT `testRuns_ibfk_1` FOREIGN KEY (`idp_id`) REFERENCES `idps` (`id`) ON DELETE CASCADE
      );'
    );

    $this->db->query(
      'CREATE TABLE `tests` (
        `testRun_id` int(10) unsigned NOT NULL,
        `time` text DEFAULT NULL,
        `test` text DEFAULT NULL,
        `attr_OK` text DEFAULT NULL,
        `attr_Missing` text DEFAULT NULL,
        `attr_Extra` text DEFAULT NULL,
        `status_OK` text DEFAULT NULL,
        `status_WARNING` text DEFAULT NULL,
        `status_ERROR` text DEFAULT NULL,
        `testResult` text DEFAULT NULL,
        KEY `testRun_id` (`testRun_id`),
        CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`testRun_id`) REFERENCES `testRuns` (`id`) ON DELETE CASCADE
      );'
    );
  }

  /**
   * Return basename
   *
   * Return the basename for the service
   *
   * @return string
   */
  public function basename()
  {
    return $this->basename;
  }

  /**
   * Return database
   *
   * Return a database pointer
   *
   * @return PDO
   */
  public function getDb()
  {
    return $this->db;
  }

  /**
   * Return federation
   *
   * Return an array with the federation configuration
   *
   * @return array
   */
  public function getFederation()
  {
    return $this->federation;
  }

  /**
   * Return UI template
   *
   * Return an array with template content
   *
   * @return array
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * Return languages
   *
   * Return an array with languages to translate GUI into
   *
   * @return array
   */
  public function getLanguages()
  {
    return $this->languages;
  }

  /**
   * Get classname, Checks if extended class exists
   *
   * @param string $className name of baseClass
   *
   */
  public function getExtendedClass($className, ...$params)
  {
    $baseClass   = __NAMESPACE__ . '\\' . $className;
    $extendClass = $baseClass . ($this->federation['extend'] ?? '');

    if (!class_exists($baseClass)) {
      return null;
    }

    return new (class_exists($extendClass) ? $extendClass : $baseClass)(...$params);
  }
}
