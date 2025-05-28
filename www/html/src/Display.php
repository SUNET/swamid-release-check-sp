<?php
namespace releasecheck;

use PDO;

class Display {
  /**
   * Configuration of application
   */
  protected Configuration $config;

  /**
   * class of testSuite
   */
  private TestSuite $testSuite;

  /**
   * Constant to reuse SQL query
   */
  const SQL_TESTS = 'SELECT * FROM `tests` WHERE `test` = :test AND `testRun_id` = :testRun';

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct() {
    if (isset($config)) {
      $this->config = $config;
    } else {
      $this->config = new Configuration();
    }
    $testClass = $this->config->getExtendedClass('TestSuite');
    $this->testSuite = new $testClass();
  }

  /**
   * Show a list of all recived attributes
   *
   * Shows all valuesattributes that start with saml_.
   *
   * @return void
   */
  public function showAttributeList() {
    printf ('        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>%s', "\n");

    foreach ( $_SERVER as $key => $value ) {
      if ( substr($key,0,5) == 'saml_' ) {
        $nkey=substr($key,5);
        $value = str_replace(';' , '<br>',$value);
        printf ("          <tr><th>%s</th><td>%s</td></tr>\n", $nkey,$value);
      }
    }
  }

  /**
   * Show info about a IdP fethed from Meatadata
   *
   * @return void
   */
  public function showIdpMetadataInfo() {
    printf('        </table>
        <h4>Identity Provider attributes in metadata</h4>
        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>%s', "\n");

    if ( isset($_SERVER['Meta-Assurance-Certification']) ) {
      printf('          <tr><th>Assurance-Certification</th><td>%s</td></tr>%s',
        str_ireplace(";", "<br>", $_SERVER['Meta-Assurance-Certification']), "\n");
    }

    if ( isset($_SERVER['Meta-Entity-Category-Support']) ) {
      printf('          <tr><th>Entity-Category-Support</th><td>%s</td></tr>%s',
        str_ireplace(";", "<br>", $_SERVER['Meta-Entity-Category-Support']), "\n");
    }

    if ( isset($_SERVER['Meta-Entity-Category']) ) {
      printf('          <tr><th>Entity-Category</th><td>%s</td></tr>%s',
        str_ireplace(";", "<br>", $_SERVER['Meta-Entity-Category']), "\n");
    }
    printf('          <tr><th>registrationAuthority</th><td>%s</td></tr>
          <tr><th>errorURL</th><td>%s</td></tr>
          <tr><th>DisplayName</th><td>%s</td></tr>
          <tr><th>InformationURL</th><td>%s</td></tr>
          <tr><th>Logo</th><td>%s</td></tr>
          <tr><th>OrganizationURL</th><td>%s</td></tr>
          <tr><th>ContactPerson (administrative)</th><td>%s</td></tr>
          <tr><th>ContactPerson (support)</th><td>%s</td></tr>
          <tr><th>ContactPerson (technical)</th><td>%s</td></tr>
          <tr><th>ContactPerson (other)</th><td>%s</td></tr>
        </table>',
      isset($_SERVER['Meta-registrationAuthority']) ? $_SERVER['Meta-registrationAuthority'] : '',
      isset($_SERVER['Meta-errorURL']) ? '<a href="' . $_SERVER['Meta-errorURL'] . '" target=”_blank”><span class="d-inline-block text-truncate" style="max-width: 900px;">' . $_SERVER['Meta-errorURL'] . '</span></a>' : '',
      isset($_SERVER['Meta-displayName']) ?  $_SERVER['Meta-displayName'] : '',
      isset($_SERVER['Meta-informationURL']) ? '<a href="' . $_SERVER['Meta-informationURL'] . '" target=”_blank”>' . $_SERVER['Meta-informationURL'] . '</a>' : '',
      isset($_SERVER['Meta-Small-Logo']) ?  '<img src="' . $_SERVER['Meta-Small-Logo'] . '">' : '',
      isset($_SERVER['Meta-organizationURL']) ? '<a href="' . $_SERVER['Meta-organizationURL'] . '" target="_blank">' . $_SERVER['Meta-organizationURL'] . '</a>' : '',
      isset($_SERVER['Meta-Support-Administrative']) ?  $_SERVER['Meta-Support-Administrative'] : '',
      isset($_SERVER['Meta-Support-Contact']) ?  $_SERVER['Meta-Support-Contact'] : '',
      isset($_SERVER['Meta-Support-Technical']) ?  $_SERVER['Meta-Support-Technical'] : '',
      isset($_SERVER['Meta-Other-Contact']) ?  $_SERVER['Meta-Other-Contact'] : '');
  }

  /**
   * Show info about IdP session
   *
   * @return void
   */
  public function showIdpSessionInfo() {
    printf('          <h4>Identity Provider sessions attributes</h4>
        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>%s', "\n");
    foreach (array('Shib-Identity-Provider','Shib-Authentication-Instant','Shib-Authentication-Method','Shib-AuthnContext-Class') as $name) {
      if ( isset ($_SERVER[$name]))
        printf ("          <tr><th>%s</th><td>%s</td></tr>\n", substr($name,5), $_SERVER[$name]);
    }
    print "        </table>\n";
  }

  /**
   * Show results from EC test
   *
   * List all tests in ecTests
   *
   * @param string $idp IdP to show results for
   *
   * @param int $testRunId Id of testrun
   *
   * @return void
   */
  public function showResultsECTests($idp, $testRunId=0){
    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun',$testRunId);
    $testHandler->bindParam('test',$test);

    printf('          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>%s', "\n");
    foreach ( $this->testSuite->getECTests() as $test) {
      $testHandler->execute();
      if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
        $this->printRow($row, $idp, $this->testSuite->getTestName($test));
      } else {
        printf ('            <tr>
              <td>Test not run yet<br>
                <a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">
                  <button type="button" class="btn btn-link">Run test</button>
                </a>
              </td>
              <td><h5>%s</h5></td>
            </tr>%s',
          $test, $this->config->basename(), urlencode($idp), urlencode(sprintf('https://%s.%s/?singleTest',$test, $this->config->basename())),
          $this->testSuite->getTestName($test), "\n");
      }
    }
    print "          </table>\n";
  }

  /**
   * Show result for MFAtest
   *
   * @param string $idp IdP to show results for
   *
   * @param int $testRunId Id of testrun
   *
   * @return void
   */
  function showResultsMFA($idp, $testRunId=0){
    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun',$testRunId);
    $testHandler->bindParam('test',$test);

    printf ('          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>', "\n");
    $test = 'mfa';
    $testHandler->execute();
    if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
      $this->printRow($row, $idp, $this->testSuite->getTestName($test));
    } else {
      printf ("            <tr><td>Test not run yet</td><td><h5>%s</h5></td></tr>\n", $this->testSuite->getTestName($test));
    }
    print "          </table>\n";
  }

  /**
   * Show result for ESItest
   *
   * @param string $idp IdP to show results for
   *
   * @param int $testRunId Id of testrun
   *
   * @return void
   */
  function showResultsESI($idp, $testRunId=0){
    $tests = array(
      'esi-stud' => 'European Student Identifier (student account)',
      'esi' => 'European Student Identifier (any account)',
    );

    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun',$testRunId);
    $testHandler->bindParam('test',$test);

    printf ('          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>', "\n");
    foreach ($tests as $test => $name) {
      $testHandler->execute();
      if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
        $this->printRow($row, $idp, $name);
      } else {
        printf ("            <tr><td>Test not run yet</td><td><h5>%s</h5></td></tr>\n", $name);
      }
    }
    print "          </table>\n";
  }

  /**
   * Print info row for one test
   *
   * @param array $row Testresult
   *
   * @param string $idp IdP used for test
   *
   * @param string $desc Description of test
   *
   * @return void
   */
  private function printRow($row, $idp, $desc='') {
    $baseTest = $row['test'] == 'esi-stud' ? 'esi' : $row['test'];
    $button = sprintf('<a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">
                  <button type="button" class="btn btn-link">Rerun test</button>
                </a>',
      $baseTest, $this->config->basename(), $idp,
      urlencode(sprintf('https://%s.%s/%s', $baseTest, $this->config->basename(), $baseTest == 'mfa' ? '' : '?singleTest')));
    if ($desc == '') {
      printf ("            <tr>
              <td>%s<br>
                %s<br>
                %s
              </td>
              <td>", $row['test'], $row['time'], $button);
    } else {
      printf ('            <tr>
              <td>%s<br>
                %s
              </td>
              <td><h5 id="%s">%s</h5>', $row['time'], $button, $row['test'], $desc);
    }
    if ( $row['status_OK'] ) {
      printf ("
                <i class=\"fas fa-check\"></i>
                <div>%s</div>
                <div class=\"clear\"></div><br>", $row['status_OK']);
    }
    if ( $row['status_WARNING'] ) {
      printf ("
                <i class=\"fas fa-exclamation-triangle\"></i>
                <div>%s</div>
                <div class=\"clear\"></div><br>", $row['status_WARNING']);
    }
    if ( $row['status_ERROR'] ) {
      printf ("
                <i class=\"fas fa-exclamation\"></i>
          <div>%s</div>
          <div class=\"clear\"></div><br>", $row['status_ERROR']);
    }
    if ( $row['attr_OK'] ) {
      printf ("
                <div>Received :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>", str_replace(',',"</li>\n                    <li>",$row['attr_OK']));
    }
    if ( $row['attr_Missing'] ) {
      $temp= str_replace(',','#',$row['attr_Missing']);
      $temp= str_replace('# ',',',$temp);
      printf ("
                <div>Missing :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>", str_replace('#',"</li>\n                    <li>",$temp));
    }
    if ( $row['attr_Extra'] )  {
      printf ("
                <div>Not expected :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>", str_replace(',',"</li>\n                    <li>",$row['attr_Extra']));
    }
    if ( $row['testResult'] ) {
      printf ("
                <div>Test result  : %s</div>", $row['testResult']);
    }
    print "
              </td>
            </tr>\n";
  }

  /**
   * Get a list of testruns
   *
   * @param string $idp Idp iused for tests
   *
   * @param string $tab Tab to get tests for
   *
   * @param int limit numer of testruns to return
   *
   * @return array
   */
  public function getTestruns($idp, $tab, $limit = 10) {
    switch ($tab) {
      case 'entityCategory' :
        $tests = implode("','", $this->testSuite->getECTests());
        break;
      case 'esi' :
        $tests = "esi','esi-stud";
        break;
      case 'mfa' :
        $tests = "mfa";
        break;
      default:
        printf ('unknown tab : %s',  $tab);
        exit;
    }
    $testRunHandler = $this->config->getDb()->prepare(
      "SELECT DISTINCT `testRuns`.`id`, `testRuns`.`time`
      FROM `testRuns`, `idps`, `tests`
      WHERE `idp_id` = `idps`.`id`
        AND `idps`.`entityID` = :EntityId
        AND `tests`.`testRun_id` = `testRuns`.`id`
        AND `test` IN ('$tests')
      ORDER BY `time` DESC
      LIMIT " . $limit);
    $testRunHandler->execute(array('EntityId' => $idp));
    return $testRunHandler->fetchAll(PDO::FETCH_ASSOC);
  }
}
