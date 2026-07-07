<?php

namespace releasecheck;

use PDO;

class Display
{
  /**
   * Configuration of application
   */
  protected Configuration $config;

  /**
   * Application helper functions
   */
  protected Helper $helper;

  /**
   * class of testSuite
   */
  protected TestSuite $testSuite;

  /**
   * Constant to reuse SQL query
   */
  protected const SQL_TESTS = 'SELECT * FROM `tests` WHERE `test` = :test AND `testRun_id` = :testRun';

  protected const HTML_NO_RUN = 'no run';
  protected const HTML_NOT_RUN_YET = 'Test not run yet';

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct()
  {
    if (isset($config)) {
      $this->config = $config;
    } else {
      $this->config = new Configuration();
    }
    $this->helper = $this->config->getExtendedClass('Helper', $this->config);
    $this->testSuite = $this->config->getExtendedClass('TestSuite');
  }

  /**
   * Show a list of all recived attributes
   *
   * Shows all valuesattributes that start with saml_.
   *
   * @return void
   */
  public function showAttributeList()
  {
    printf('        <table class="table table-striped table-bordered">
          <tr><th>' . _('Attribute') . '</th><th>' . _('Value') . '</th></tr>%s', "\n");

    foreach ($_SERVER as $key => $value) {
      if (substr($key, 0, 5) == 'saml_') {
        $nkey = substr($key, 5);
        $value = str_replace(';', '<br>', $value);
        printf("          <tr><th>%s</th><td>%s</td></tr>\n", $nkey, $value);
      }
    }
  }

  /**
   * Show info about a IdP fethed from Meatadata
   *
   * @return void
   */
  public function showIdpMetadataInfo()
  {
    printf('        </table>
        <h4>' . _('Identity Provider attributes in metadata') . '</h4>
        <table class="table table-striped table-bordered">
          <tr><th>' . _('Attribute') . '</th><th>' . _('Value') . '</th></tr>%s', "\n");

    if (isset($_SERVER['Meta-Assurance-Certification'])) {
      printf(
        '          <tr><th>Assurance-Certification</th><td>%s</td></tr>%s',
        str_ireplace(';', '<br>', $_SERVER['Meta-Assurance-Certification']),
        "\n"
      );
    }

    if (isset($_SERVER['Meta-Entity-Category-Support'])) {
      printf(
        '          <tr><th>Entity-Category-Support</th><td>%s</td></tr>%s',
        str_ireplace(';', '<br>', $_SERVER['Meta-Entity-Category-Support']),
        "\n"
      );
    }

    if (isset($_SERVER['Meta-Entity-Category'])) {
      printf(
        '          <tr><th>Entity-Category</th><td>%s</td></tr>%s',
        str_ireplace(';', '<br>', $_SERVER['Meta-Entity-Category']),
        "\n"
      );
    }
    printf(
      '          <tr><th>registrationAuthority</th><td>%s</td></tr>
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
      isset($_SERVER['Meta-errorURL']) ? '<a href="' . $_SERVER['Meta-errorURL'] .
        '" target=”_blank”><span class="d-inline-block text-truncate" style="max-width: 900px;">' .
        $_SERVER['Meta-errorURL'] . '</span></a>' : '',
      isset($_SERVER['Meta-displayName']) ?  $_SERVER['Meta-displayName'] : '',
      isset($_SERVER['Meta-informationURL']) ? '<a href="' . $_SERVER['Meta-informationURL'] .
        '" target=”_blank”>' . $_SERVER['Meta-informationURL'] . '</a>' : '',
      isset($_SERVER['Meta-Logo']) ?  $_SERVER['Meta-Logo'] : '',
      isset($_SERVER['Meta-organizationURL']) ? '<a href="' . $_SERVER['Meta-organizationURL'] .
        '" target="_blank">' . $_SERVER['Meta-organizationURL'] . '</a>' : '',
      isset($_SERVER['Meta-Support-Administrative']) ?  $_SERVER['Meta-Support-Administrative'] : '',
      isset($_SERVER['Meta-Support-Contact']) ?  $_SERVER['Meta-Support-Contact'] : '',
      isset($_SERVER['Meta-Support-Technical']) ?  $_SERVER['Meta-Support-Technical'] : '',
      isset($_SERVER['Meta-Other-Contact']) ?  $_SERVER['Meta-Other-Contact'] : ''
    );
  }

  /**
   * Show info about IdP session
   *
   * @return void
   */
  public function showIdpSessionInfo()
  {
    printf('          <h4>' . _('Identity Provider sessions attributes') . '</h4>
        <table class="table table-striped table-bordered">
          <tr><th>' . _('Attribute') . '</th><th>' . _('Value') . '</th></tr>%s', "\n");
    foreach (
      array(
        'Shib-Identity-Provider',
        'Shib-Authentication-Instant',
        'Shib-Authentication-Method',
        'Shib-AuthnContext-Class'
      ) as $name
    ) {
      if (isset($_SERVER[$name])) {
        printf("          <tr><th>%s</th><td>%s</td></tr>\n", substr($name, 5), $_SERVER[$name]);
      }
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
   * @param array $testrun testrun to display
   *
   * @return void
   */
  public function showResultsECTests($idp, $testrun)
  {
    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun', $testrun['id']);
    $testHandler->bindParam('test', $test);

    printf('          <table class="table table-striped table-bordered">
            <tr><th>' . _('Test') . '</th><th>' . _('Result') . '</th></tr>%s', "\n");
    foreach ($this->testSuite->getECTests() as $test) {
      $testHandler->execute();
      if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
        $this->printRow($row, $idp, _($this->testSuite->getTestName($test)), $testrun['session']);
      } else {
        printf(
          '            <tr>
              <td>' . _(self::HTML_NOT_RUN_YET) . '<br>
                <a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">
                  <button type="button" class="btn btn-link">' . _('Run test') . '</button>
                </a>
              </td>
              <td><h5>%s</h5></td>
            </tr>%s',
          $test,
          $this->config->basename(),
          urlencode($idp),
          urlencode(
            sprintf(
              'https://%s.%s/?singleTest%s',
              $test,
              $this->config->basename(),
              $testrun['session'] == '' ? '' : sprintf('&session=%s', $testrun['session'])
            )
          ),
          _($this->testSuite->getTestName($test)),
          "\n"
        );
      }
    }
    print "          </table>\n";
  }

  /**
   * Show result for MFAtest
   *
   * @param string $idp IdP to show results for
   *
   * @param array $testrun testrun to display
   *
   * @return void
   */
  public function showResultsMFA($idp, $testrun)
  {
    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun', $testrun['id']);
    $testHandler->bindParam('test', $test);

    printf('          <table class="table table-striped table-bordered">
            <tr><th>' . _('Test') . '</th><th>' . _('Result') . '</th></tr>', "\n");
    $test = 'mfa';
    $testHandler->execute();
    if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
      $this->printRow($row, $idp, _($this->testSuite->getTestName($test)), $testrun['session']);
    } else {
      printf(
        '            <tr><td>' . _(self::HTML_NOT_RUN_YET) . "</td><td><h5>%s</h5></td></tr>\n",
        $this->testSuite->getTestName($test)
      );
    }
    print "          </table>\n";
  }

  /**
   * Show result for ESItest
   *
   * @param string $idp IdP to show results for
   *
   * @param array $testrun testrun to display
   *
   * @return void
   */
  public function showResultsESI($idp, $testrun)
  {
    $tests = array(
      'esi-stud' => _('European Student Identifier (student account)'),
      'esi' => _('European Student Identifier (any account)'),
    );

    $testHandler = $this->config->getDb()->prepare(self::SQL_TESTS);
    $testHandler->bindValue('testRun', $testrun['id']);
    $testHandler->bindParam('test', $test);

    printf('          <table class="table table-striped table-bordered">
            <tr><th>' . _('Test') . '</th><th>' . _('Result') . '</th></tr>', "\n");
    foreach ($tests as $test => $name) {
      $testHandler->execute();
      if ($row = $testHandler->fetch(PDO::FETCH_ASSOC)) {
        $this->printRow($row, $idp, $name, $testrun['session']);
      } else {
        printf('            <tr><td>' . _(self::HTML_NOT_RUN_YET) . "</td><td><h5>%s</h5></td></tr>\n", $name);
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
  private function printRow($row, $idp, $desc = '', $session = '')
  {
    $baseTest = $row['test'] == 'esi-stud' ? 'esi' : $row['test'];
    $singleTest = sprintf('?singleTest%s', $session == '' ? '' : sprintf('&session=%s', $session));
    $target = sprintf(
      'https://%s.%s/%s',
      $baseTest,
      $this->config->basename(),
      $singleTest
    );
    $button = sprintf(
      '<a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">
                  <button type="button" class="btn btn-link">%s</button>
                </a>',
      $baseTest,
      $this->config->basename(),
      urlencode($idp),
      urlencode($target),
      $session == '' ? _('Run NEW test') : _('Rerun test')
    );
    if ($desc == '') {
      printf(
        '            <tr>
              <td>%s<br>
                %s<br>
                %s
              </td>
              <td>',
        $row['test'],
        $row['time'],
        $button
      );
    } else {
      printf(
        '            <tr>
              <td>%s<br>
                %s
              </td>
              <td><h5 id="%s">%s</h5>',
        $row['time'],
        $button,
        $row['test'],
        $desc
      );
    }
    if ($row['status_OK'] != '[]') {
      printf(
        '
                <i class="fas fa-check"></i>
                <div>%s</div>
                <div class="clear"></div><br>',
        $this->helper->getStatusTranslated($row['status_OK'])
      );
    }
    if ($row['status_WARNING'] != '[]') {
      printf(
        '
                <i class="fas fa-exclamation-triangle"></i>
                <div>%s</div>
                <div class="clear"></div><br>',
        $this->helper->getStatusTranslated($row['status_WARNING'])
      );
    }
    if ($row['status_ERROR'] != '[]') {
      printf(
        '
                <i class="fas fa-exclamation"></i>
          <div>%s</div>
          <div class="clear"></div><br>',
        $this->helper->getStatusTranslated($row['status_ERROR'])
      );
    }
    if ($row['attr_OK']) {
      printf(
        '
                <div>' . _('Received') . ' :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>',
        str_replace(
          ',',
          "</li>\n                    <li>",
          $this->helper->getStatusTranslated($row['attr_OK'])
        )
      );
    }
    if ($row['attr_Missing']) {
      $temp = preg_replace('/(\S),(\S)/', '$1#$2', $row['attr_Missing']);
      $temp = $this->helper->getMissingAttributesTranslated($temp);
      printf(
        '
                <div>' . _('Missing') . ' :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>',
        str_replace(
          '#',
          "</li>\n                    <li>",
          $temp
        )
      );
    }
    if ($row['attr_Extra']) {
      printf(
        '
                <div>' . _('Not expected') . ' :
                  <ul>
                    <li>%s</li>
                  </ul>
                </div><br>',
        str_replace(
          ',',
          "</li>\n                    <li>",
          $row['attr_Extra']
        )
      );
    }
    if ($row['testResult']) {
      printf(
        '
                <div>' . _('Test result') . '  : %s</div>',
        _($row['testResult'])
      );
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
  public function getTestruns($idp, $tab, $limit = 10, $base = './')
  {
    switch ($tab) {
      case 'entityCategory':
        $tests = implode("','", $this->testSuite->getECTests());
        break;
      case 'esi':
        $tests = "esi','esi-stud";
        break;
      case 'mfa':
        $tests = 'mfa';
        break;
      default:
        printf(_('unknown tab') . ' : %s', $tab);
        exit;
    }
    $testRunHandler = $this->config->getDb()->prepare(
      "SELECT DISTINCT `testRuns`.`id`, `testRuns`.`time`, `testRuns`.`session`
      FROM `testRuns`, `idps`, `tests`
      WHERE `idp_id` = `idps`.`id`
        AND `idps`.`entityID` = :EntityId
        AND `tests`.`testRun_id` = `testRuns`.`id`
        AND `test` IN ('$tests')
      ORDER BY `time` DESC
      LIMIT " . $limit
    );
    $testRunHandler->execute(array('EntityId' => $idp));
    $testruns = $testRunHandler->fetchAll(PDO::FETCH_ASSOC);
    switch (count($testruns)) {
      case 0:
        $testrun = array ('id' => 0, 'time' => self::HTML_NO_RUN, 'session' => '');
        break;
      case 1:
        $testrun = $testruns[0];
        break;
      default:
        # Default to 1:st testrun
        $lastRunId = $testruns[0]['id'];
        $selectedTab = isset($_GET['tab']) && $_GET['tab'] == $tab;
        $selectedId = isset($_GET['id']) && $selectedTab ? $_GET['id'] : $lastRunId;
        $session = ($selectedId == $lastRunId)  ? $testruns[0]['session'] : '';
        $urlBase = sprintf(
          '%s?tab=%s%s',
          $base,
          $tab,
          $base == './' ? '' : sprintf('&idp=%s', urlencode($idp))
        );
        print '          <h4>' . _('Other results') . "</h4>
            <ul>\n";
        foreach ($testruns as $run) {
          # Check if this run is requested run. In that case save this run
          if ($selectedId == $run['id']) {
            $testrun = $run;
            printf('            <li>%s</li>%s', $run['time'], "\n");
          } else {
            printf(
              '            <li><a href="%s&id=%d">%s</a></li>%s',
              $urlBase,
              $run['id'],
              $run['time'],
              "\n"
            );
          }
        }
        print "          </ul>\n";
        $testrun['session'] = $session;
    }
    return $testrun;
  }
}
