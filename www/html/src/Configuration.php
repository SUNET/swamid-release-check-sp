<?php
namespace releasecheck;

use PDO;
use PDOException;

class Configuration {
  /**
   * Mode
   *
   * Could be Prod, QA or Labb
   *
   */
  private string $mode = '';

  /**
   * Basename of the application
   *
   * Should be the hostname without htts://
   *
   */
  private string $basename = '';

  /**
   * Informatiom about the federation running the application
   *
   */
  private array $federation = array ();

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
  public function __construct($startDB = true) {
    include __DIR__ . '/../config.php'; # NOSONAR

    $reqParams = array('db', 'mode', 'basename', 'federation');
    $reqParamsDB = array('servername', 'username', 'password',
      'name');
    $reqParamsFederation = array('displayName', 'adminUsers');

    foreach ($reqParams as $param) {
      if (! isset(${$param})) {
        printf ('Missing %s in config.php<br>', $param);
        exit;
      }
    }

    $this->checkParams($db, $reqParamsDB, 'db');

    $this->checkParams($federation,$reqParamsFederation, 'federation');
    if (! isset($federation['extend'])) {
      $federation['extend'] = '';
    }
    if (! isset($federation['DS'])) {
      $federation['DS'] = 'service.seamlessaccess.org';
    }
    if (! isset($federation['LoginURL'])) {
      $federation['LoginURL'] = 'Login';
    }
    # Federation params
    $this->federation = $federation;

    $this->mode =  $mode;
    $this->basename = $basename;

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
   * @param array $reqParams Requierd parameters in checkParam
   *
   * @param string Name of array in config
   */
  private function checkParams($checkParam, $reqParams, $nameOfParam) {
    foreach ($reqParams as $param) {
      if (! isset($checkParam[$param])) {
        printf ('Missing $%s[%s] in config.php<br>', $nameOfParam, $param);
        exit;
      }
    }
  }

  /**
   * Start up database connection
   *
   * @param array $db Parametere for the database
   */
  private function startDB($db) {
    $options = array();
    if (isset($db['caPath'])) {
      $options[PDO::MYSQL_ATTR_SSL_CA] = $db['caPath'];
    }
    try {
      $dbDSN = sprintf('mysql:host=%s;dbname=%s', $db['servername'], $db['name']);
      $this->db = new PDO($dbDSN, $db['username'], $db['password'], $options);
      // set the PDO error mode to exception
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
    $this->checkDBVersion();
  }

  /**
   * Checks version of DB and update if needed
   *
   * @return void
   */
  private function checkDBVersion() {
    try {
      $dbVersionHandler = $this->db->query("SELECT `value` FROM `params` WHERE `id` = 'dbVersion';");
      $dbVersionResult = $dbVersionHandler->fetch(PDO::FETCH_ASSOC);
      $dbVersion = $dbVersionResult['value'];
    } catch(PDOException $e) {
      $dbVersion = 0;
    }
    if ($dbVersion < 1) {
      $this->createTables();
    }
  }

  /**
   * Create tables if not already exists
   *
   * Run the first time of use
   *
   * @return void
   */
  private function createTables() {
    $this->db->query(
      'CREATE TABLE `params` (
        `id` varchar(20) DEFAULT NULL,
        `value` text DEFAULT NULL
      );');
    $this->db->query(
      "INSERT INTO `params` (`id`, `value`) VALUES ('dbVersion', '1');");

    $this->db->query(
      'CREATE TABLE `idps` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `entityID` varchar(256) DEFAULT NULL,
        registrationAuthority varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      );');

    $this->db->query(
      'CREATE TABLE `testRuns` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `idp_id` int(10) unsigned NOT NULL,
        `session` varchar(27) DEFAULT NULL,
        `time` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idp_id` (`idp_id`),
        CONSTRAINT `testRuns_ibfk_1` FOREIGN KEY (`idp_id`) REFERENCES `idps` (`id`) ON DELETE CASCADE
      );');

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
      );');
  }

  /**
   * Return basename
   *
   * Return the basename for the service
   *
   * @return string
   */
  public function basename() {
    return $this->basename;
  }

  /**
   * Return database
   *
   * Return a database pointer
   *
   * @return PDO
   */
  public function getDb() {
    return $this->db;
  }

  /**
   * Return federation
   *
   * Return an array with the federation configuration
   *
   * @return array
   */
  public function getFederation() {
    return $this->federation;
  }

  /**
   * Return mode
   *
   * Return the mode of the service
   *
   * @return string
   */
  public function getMode() {
    return $this->mode;
  }

  /**
   * Get classname, Checks if extended class exists
   *
   * @param string $className name of baseClass
   *
   * @return string name of baseClass or if exists extended class
   */
  public function getExtendedClass($className) {
    if (class_exists(__NAMESPACE__.'\\'.$className)) {
      return class_exists(__NAMESPACE__.'\\'.$className.$this->federation['extend']) ?
        __NAMESPACE__.'\\'.$className.$this->federation['extend'] :
        __NAMESPACE__.'\\'.$className;
    }
  }
}
