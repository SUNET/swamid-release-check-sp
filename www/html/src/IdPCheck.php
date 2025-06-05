<?php
namespace releasecheck;
use PDO;

/*
 * changelog:
 * 2020-02-28 Created file
 *
 * 2020-11-04 Added checks för LADOK
 *
 * 2021-05-19 Added checks for RAF
 *
 * 2025-05-21 Moved file into namespace releasecheck
 */

class IdPCheck {
  /**
   * Configuration of application
   */
  protected Configuration $config;

  /**
   * Test
   */
  protected string $test = '';

  /**
   * Testname
   */
  protected string $testname = '';

  /**
   * Tab to redirect to in resut-page
   */
  protected string $testtab = '';

  /**
   * array of expected attributes including description
   */
  protected array $expected = array();

  /**
   * array of attributes that we show not warn about
   */
  protected array $nowarn = array();

  /**
   * String EntityId for IdP
   */
  protected string $idp = '';

  /**
   * String registrationAuthority of IdP
   */
  protected string $registrationAuthority = '';

  /**
   * String registrationAuthority of IdP
   */
  protected string $sessionID = '';

  /**
   * Status of tests
   */
  protected $status = array('ok' => '', 'warning' => '', 'error' => '', 'testResult' => '');

  /**
   * Request adding ECS to Metadata
   */
  protected $toListStr = 'to the list of supported ECs in Metadata';

  /**
   * List of allow/expected values in eduPersonAssuance
   */
  protected array $rafAttributes;

  /**
   * Assurance level of user
   */
  protected string $userAL = '';

  /**
   * If IdP send an attribut that is not allowed
   */
  protected bool $notAllowed = false;

  protected const RAF_BASE = 'https://refeds.org/assurance';
  protected const RAF_LOW = self::RAF_BASE . '/IAP/low';
  protected const RAF_MEDIUM = self::RAF_BASE . '/IAP/medium';
  protected const RAF_HIGH = self::RAF_BASE . '/IAP/high';

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct() {
    session_start();
    if (isset($config)) {
      $this->config = $config;
    } else {
      $this->config = new Configuration();
    }

    $a = func_get_args();
    $i = func_num_args();
    if (method_exists($this,$f='__construct'.$i)) {
      call_user_func_array(array($this,$f),$a);
    }

    $this->registrationAuthority = isset($_SERVER['Meta-registrationAuthority']) ? $_SERVER['Meta-registrationAuthority'] : '';
    $this->sessionID = isset($_GET['session']) ? $_GET['session'] : session_id();
  }

  /**
   * Constructor for tests
   */
  protected function __construct5($test, $testname, $testtab, $expected, $nowarn) { # NOSONAR
    $this->test = $test;
    $this->testname = $testname;
    $this->testtab = $testtab;
    $this->expected = $expected;
    $this->nowarn = $nowarn;
    $this->idp=$_SERVER['Shib-Identity-Provider'];
  }

  /**
   * Show headers for test
   *
   * * Show info about this test
   * * buttons for next and last test
   *
   * @param string $lasttest name of last test
   *
   * @param string $nexttest name of next test
   *
   * @param bool|string $singleTest if singeltest. Then return directly to result page.
   *
   * @param bool $forceAuthn If we should force forceAuthn in request
   *
   * @return void
   */
  public function showTestHeaders($lasttest, $nexttest, $singleTest=false, $forceAuthn = false) {
    printf('%s    <table class="table table-striped table-bordered">
      <caption>Test info</caption>
      <tr><th>Test</th><td>%s</td></tr>
      <tr><th>Tested IdP</th><td>%s</td></tr>
    </table>
    <h4>%s', "\n", $this->testname, $this->idp, "\n");
    if ($lasttest == '' || $singleTest) {
      print '      <button type="button" class="btn btn-outline-primary">No previous test</button> | ';
    } else {
      printf ('      <a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">%s</a> | %s',
        $lasttest, $this->config->basename(), $this->idp,
        urlencode(sprintf('https://%s.%s/?session=%s', $lasttest, $this->config->basename(), $this->sessionID)),
        '<button type="button" class="btn btn-outline-primary">Previous test</button>', "\n");
    }

    if ($nexttest == 'result' || $singleTest) {
      printf ('      <a href="https://%s/Shibboleth.sso/Login?target=https://%s/result/?tab=%s&entityID=%s">%s</a>',
      $this->config->basename(), $this->config->basename(), $this->testtab, $this->idp,
    '<button type="button" class="btn btn-success">Show the results</button>');
    } elseif ($forceAuthn) {
      printf (
        '      <a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&forceAuthn=true&target=%s">%s</a>',
        $nexttest, $this->config->basename(), $this->idp,
        urlencode(sprintf('https://%s.%s/?forceAuthn&session=%s', $nexttest, $this->config->basename(), $this->sessionID)),
        '<button type="button" class="btn btn-success">Next test</button>');
    } else {
      printf ('      <a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s">%s</a>',
        $nexttest, $this->config->basename(), $this->idp,
        urlencode(sprintf('https://%s.%s/?session=%s', $nexttest, $this->config->basename(), $this->sessionID)),
        '<button type="button" class="btn btn-success">Next test</button>');
    }

    print "\n    </h4>\n";
  }

  /**
   * Test what attributes that are send and what is missing. Also checks subtest/EC
   *
   * @param string $subtest Subtest to run to validate EC
   *
   * @param bool $quickTest If true redirects to next test after a short delay
   *
   * @return void
   */
  public function testAttributes( $subtest, $quickTest = false ){
    $samlValues = array();
    $extraValues = array();
    $okValues = array();
    $missingValues = array();
    $missing = false;
    $singleValueAttributes = array(
      'pairwise-id' => true,
      'subject-id' => true,
      'eduPersonPrincipalName' => true
    );

    list ($ac,$ecs,$ec) = $this->getMetaInfo();

    # Goes thru all recived attribues and warn for extra attributes
    foreach ( $_SERVER as $key => $value ) {
      if ( substr($key,0,5) == 'saml_' ) {
        $nkey=substr($key,5);
        $samlValues[$nkey] = $value;
        if (! isset($this->expected[$nkey]) ) {
          $extraValues[$nkey] = $value;
          if ( isset( $this->nowarn[$nkey] ) ) {
            $this->status['warning'] = 'The IDP has sent too many attributes.<br>';
          } else {
            $this->status['error'] = 'The IDP has sent too many attributes.<br>';
          }
        }
      }
    }

    /**
     *  Checks all expected and warn if multipla values are sent for an single-value attribute. Warn if missing attributes
     */
    foreach ( $this->expected as $key => $value ) {
      if ( isset ($samlValues[$key] ) ) {
        $okValues[$key] = $samlValues[$key];
        if (strpos($samlValues[$key], ';') && isset($singleValueAttributes[$key])) {
          $this->status['error'] .= sprintf('Received multi-value for %s, should be single-value!<br>', $key);
        }
      } else {
        $missingValues[$key] = $value;
        $missing = true;
      }
    }

    $this->status['warning'] .= $missing ?
      'The IDP has not sent all the expected attributes. See the comments below.<br>' : '';
    if ( $subtest == 'anonymous' ) { $this->checkAnonymous($okValues, $ecs); }
    if ( $subtest == 'CoCov1' ) { $this->checkCoCo($ecs,
      'http://www.geant.net/uri/dataprotection-code-of-conduct/v1'); # NOSONAR Should be http://
    }
    if ( $subtest == 'CoCov2' ) { $this->checkCoCo($ecs,
      'https://refeds.org/category/code-of-conduct/v2');
    }
    if ( $subtest == 'ESI' ) { $this->checkESI($okValues); }
    if ( $subtest == 'MFA' ) { $this->checkMFA($okValues, $ac); }
    if ( $subtest == 'personalized' ) { $this->checkPersonalized($okValues, $ecs); }
    if ( $subtest == 'pseudonymous' ) { $this->checkPseudonymous($okValues, $ecs); }
    if ( $subtest == 'R&S' ) { $this->checkRandS($okValues, $ecs); }
    if ( $subtest == 'RAF' ) { $this->checkRAF($okValues, $ac); }

    # If we have no warnings or error then we are OK
    if ( $this->status['ok'] == '' && $this->status['warning'] == '' && $this->status['error'] == '' ) {
      $this->status['ok'] .= 'Did not send any attributes that were not requested.<br>';
      if ( $this->status['testResult'] == '' ) {
        $this->status['testResult'] = 'Did not send any attributes that were not requested.';
      }
    }

    if ( $subtest == 'MFA' ) {
      if(isset($_GET['forceAuthn'])) {
        # Save after step 2
        $this->saveToSQL($this->status,$okValues,$missingValues,$extraValues);
      }
      # Skip save if on step 1
    } else {
      $this->saveToSQL($this->status,$okValues,$missingValues,$extraValues);
    }
    if ( $subtest == 'ESI' ) {
      $stud = false;
      if (
        (isset($okValues['eduPersonAffiliation']) &&
          (strpos($okValues['eduPersonAffiliation'], 'student') !== false)) ||
        (isset($okValues['eduPersonScopedAffiliation']) &&
          (strpos($okValues['eduPersonScopedAffiliation'], 'student@') !== false))) {
        $stud = true;
      }
      if ($stud) {
        print "    <h5>Checking as Stud-account, saving <b>two</b> results</h5>\n";
        $this->test = 'esi-stud';
        $this->saveToSQL($this->status,$okValues,$missingValues,$extraValues);
      } else {
        print "    <h5>Checking as none Stud-account, saving <b>one</b> result</h5>\n";
      }
    }
    if ($quickTest) {
      sleep(5);
      if ($quickTest == 'result') {
        header(sprintf ('Location: https://%s/Shibboleth.sso/Login?entityID=%s&target=%s',
          $this->config->basename(), $this->idp,
          urlencode(sprintf('https://%s/result/?tab=%s&session=%s',
            $this->config->basename(), $this->testtab, $this->sessionID)
          )), true, 302);
      } else {
        header(sprintf ('Location: https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s',
          $quickTest, $this->config->basename(), $this->idp,
          urlencode(sprintf('https://%s.%s/?quickTest&session=%s',
            $quickTest, $this->config->basename(), $this->sessionID)
          )), true, 302);
      }
    } else {
      $this->showStatus($this->status);

      if (isset($this->status['infoText'])) {
        print $this->status['infoText'];
      }

      $this->showAttributeTable('Received attributes', $okValues);

      if (count ($missingValues) ) {
        $this->showAttributeTable('Missing attributes (might be OK, see comments below)', $missingValues);
      }

      if (count ($extraValues) ) {
        $this->showAttributeTable('Attributes that were not requested/expected', $extraValues);
      }
    }
  }

  /**
   * Prints out a table based on values in attributeArray
   *
   * @param $title Title of table
   *
   * @param $attributeArray an array with keys and values
   *
   * @return void
   */
  protected function showAttributeTable($title, $attributeArray) {
    printf ('    <h3>%s</h3>
    <table class="table table-striped table-bordered">
      <tr><th>Attribute</th><th>Value</th></tr>%s', $title, "\n");
    foreach ( $attributeArray as $key => $value ) {
      $value = str_replace(";" , "<br>",$value);
      printf('      <tr><th>%s</th><td>%s</td></tr>%s', $key, $value, "\n");
    }
    print "    </table>\n";
  }

  /**
   * Save into SQL
   *
   * @param array $okValues List of recived expected attributes
   *
   * @param array $missingValues List of missing attributes
   *
   * @param array $extraValues List of attributes recived that was NOT expected
   *
   * @return void
   */
  protected function saveToSQL($okValues, $missingValues, $extraValues) {
    $getIdpHandler = $this->config->getDb()->prepare('SELECT `id` FROM `idps` WHERE `entityID` = :idp;');
    $getIdpHandler->execute(array('idp' => $this->idp));
    if ($idp = $getIdpHandler->fetch(PDO::FETCH_ASSOC)) {
      $idp_id = $idp['id'];
      $updateIdpHandler = $this->config->getDb()->prepare('UPDATE `idps` SET `registrationAuthority` = :regAuth WHERE `id` = :idp;');
      $updateIdpHandler->execute(array('idp' => $idp_id, 'regAuth' => $this->registrationAuthority));
    } else {
      $addIdpHandler = $this->config->getDb()->prepare('INSERT INTO `idps` (`entityID`, `registrationAuthority`) VALUES (:idp, :regAuth);');
      $addIdpHandler->execute(array('idp' => $this->idp, 'regAuth' => $this->registrationAuthority));
      $idp_id = $this->config->getDb()->lastInsertId();
    }

    $getTestRunHandler = $this->config->getDb()->prepare('SELECT `id` FROM `testRuns` WHERE `idp_id` = :idp AND `session`= :session ;');
    $getTestRunHandler->execute(array('idp' => $idp_id, 'session' => $this->sessionID));
    if ($testRun = $getTestRunHandler->fetch(PDO::FETCH_ASSOC)) {
      $testRun_id = $testRun['id'];
      $updateTestRunHandler = $this->config->getDb()->prepare('UPDATE `testRuns` SET `time` = NOW() WHERE `id` = :id;');
      $updateTestRunHandler->execute(array('id' => $testRun_id));
    } else {
      $addTestRunHandler = $this->config->getDb()->prepare('INSERT INTO `testRuns` (`idp_id`, `session`, `time`) VALUES (:idp, :session, NOW());');
      $addTestRunHandler->execute(array('idp' => $idp_id, 'session' => $this->sessionID));
      $testRun_id = $this->config->getDb()->lastInsertId();
    }

    $getTestHandler = $this->config->getDb()->prepare('SELECT `time` FROM `tests` WHERE testRun_id = :testRun AND `test`= :test ;');
    $getTestHandler->execute(array('testRun' => $testRun_id, 'test' => $this->test));
    if ($getTestHandler->fetch(PDO::FETCH_ASSOC)) {
      $updateTestHandler = $this->config->getDb()->prepare(
        'UPDATE `tests`
        SET `time` = :time,
          `attr_OK` = :attr_ok,
          `attr_Missing` = :attr_missing,
          `attr_Extra` = :attr_extra,
          `status_OK` = :status_ok,
          `status_WARNING` = :status_warning,
          `status_ERROR` = :status_error,
          `testResult` = :testresultat
        WHERE testRun_id = :testRun AND `test`= :test ;');
    } else {
      $updateTestHandler = $this->config->getDb()->prepare(
        'INSERT INTO `tests`
            ( testRun_id, `test`, `time`,
            `attr_OK`, `attr_Missing`, `attr_Extra`,
            `status_OK`, `status_WARNING`, `status_ERROR`, `testResult`)
          VALUES
            ( :testRun, :test, :time,
            :attr_ok, :attr_missing, :attr_extra,
            :status_ok, :status_warning, :status_error,:testresultat) ;');
    }
    $updateTestHandler->execute(array('testRun' => $testRun_id, 'test' => $this->test,
      'time' => date("Y-m-d H:i:s"), 'attr_ok' =>  $this->listKeys($okValues),
      'attr_missing' =>  $this->listKeysWithValues($missingValues), 'attr_extra' =>  $this->listKeys($extraValues),
      'status_ok' =>  $this->status['ok'], 'status_warning' =>  $this->status['warning'], 'status_error' =>  $this->status['error'],
      'testresultat' =>  $this->status['testResult']));
  }

  /**
   * Create a , separated list of all keys from an array
   *
   * @param array $array List of keys
   *
   * @return string
   */
  protected function listKeys($array) {
    $output = '';
    $comma = '';
    foreach( $array as $key=>$data ) {
      $output .= $comma . $key;
      $comma = ',';
    }
    return $output;
  }

  /**
   * Create a , separated list of all keys and values from an array
   *
   * @param array $array List of keys with values
   *
   * @return string
   */
  protected function listKeysWithValues($array) {
    $output = '';
    $comma = '';
    foreach( $array as $key=>$data ) {
      $output .= $comma . $key . ' - ' . $data;
      $comma = ',';
    }
    return $output;
  }

  /**
   * Check http://refeds.org/category/research-and-scholarship
   *
   * * Check  if all attributes that are requied are sent
   * * Verify that announced support for EC is correct
   *
   * @param array $attributes Attributes released
   *
   * @param array $ecs EC:s the IdP claims to support
   */
  protected function checkRandS( $attributes, $ecs) {
    $randSisOK = false;
    # displayName and/or (givenName och sn) must exist for R&S
    if ( isset($attributes['displayName']) ) {
      $randSisOK = true;
    }
    if ( isset($attributes['givenName']) && isset($attributes['sn']) ) {
      $randSisOK = true;
    }
    if ( ! $randSisOK ) {
      $this->status['warning'] .= 'R&S requires displayName or givenName + sn.<br>';
    }

    # both mail and eduPersonPrincipalName must exist !
    if (! isset($attributes['mail']) ) {
      $randSisOK = false;
      $this->status['warning'] .= 'R&S requires mail.<br>';
    }
    if (! isset($attributes['eduPersonPrincipalName']) ) {
      $randSisOK = false;
      $this->status['warning'] .= 'R&S requires eduPersonPrincipalName.<br>';
    }
    if ( $randSisOK ) {
      $this->status['ok'] .= 'All the attributes required to fulfil R&S were sent<br>';
      if ( isset($ecs['http://refeds.org/category/research-and-scholarship']) ) { # NOSONAR Should be http://
        $this->status['testResult'] = 'R&S attributes OK, Entity Category Support OK';
      } else {
        $this->status['testResult'] = 'R&S attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports R&S but doesn't announce it in its metadata.";
        $part2 = "Please add 'http://refeds.org/category/research-and-scholarship' "; # NOSONAR Should be http://
        $part3 = $this->toListStr;
        $this->status['warning'] .= $part1 . '<br>' . $part2 . $part3 . '<br>';
      }
    } else {
      if ( isset($ecs['http://refeds.org/category/research-and-scholarship']) ) { # NOSONAR Should be http://
        $this->status['testResult'] = 'R&S attributes missing, BUT Entity Category Support claimed';
        $this->status['error'] .= 'The IdP does NOT support R&S but it claims that it does in its metadata!!<br>';
      } else {
        $this->status['testResult'] = 'R&S attribute missing, Entity Category Support missing';
      }
    }
  }

  /**
   * Check https://refeds.org/category/anonymous
   *
   * * Check  if all attributes that are requied are sent
   * * Verify that announced support for EC is correct
   *
   * @param array $attributes Attributes released
   *
   * @param array $ecs EC:s the IdP claims to support
   */
  protected function checkAnonymous( $attributes, $ecs) {
    $checkIsOK = true;
    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Anonymous requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Anonymous requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $this->status['ok'] .= 'All the attributes required to fulfil Anonymous were sent<br>';
      if ( isset($ecs['https://refeds.org/category/anonymous']) ) {
        $this->status['testResult'] = 'Anonymous attributes OK, Entity Category Support OK';
      } else {
        $this->status['testResult'] = 'Anonymous attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Anonymous but doesn't announce it in its metadata";
        $part2 =  "Please add 'https://refeds.org/category/anonymous' ";
        $part3 =  $this->toListStr;
        $this->status['warning'] .= $part1 . '<br>' . $part2 . $part3 .'<br>';
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/anonymous']) ) {
        $this->status['testResult'] = 'Anonymous attributes missing, BUT Entity Category Support claimed';
        $this->status['error'] .= 'The IdP does NOT support Anonymous but it claims that it does in its metadata!!<br>';
      } else {
        $this->status['testResult'] = 'Anonymous attribute missing, Entity Category Support missing';
      }
    }
  }

  /**
   * Check https://refeds.org/category/pseudonymous
   *
   * * Check  if all attributes that are requied are sent
   * * Verify that announced support for EC is correct
   *
   * @param array $attributes Attributes released
   *
   * @param array $ecs EC:s the IdP claims to support
   */
  protected function checkPseudonymous( $attributes, $ecs) {
    $checkIsOK = false;
    if (! isset($attributes['eduPersonAssurance']) ) {
      $this->status['warning'] .= 'Pseudonymous requires eduPersonAssurance.<br>';
    } else {
      $checkArray = array ('IAP/low', 'ID/unique', 'ID/eppn-unique-no-reassign', 'ATP/ePA-1m');
      $checkOKArray = array();

      foreach (explode(';',$attributes['eduPersonAssurance']) as $row) {
        if (substr($row,0,28) == self::RAF_BASE) {
          $checkIsOK = true;
          $part = substr($row,29);
          if ($part != '') {
            $checkOKArray[$part] = true;
          }
        }
      }

      if ($checkIsOK) {
        foreach ($checkArray as $part) {
          if (! isset($checkOKArray[$part])) {
            $this->status['warning'] .=
              'SWAMID recommends that eduPersonAssurance contains ' . self::RAF_BASE . '/' . $part . '.<br>';
          }
        }
      } else {
        $this->status['warning'] .=
          'Pseudonymous requires that eduPersonAssurance at least contains ' . self::RAF_BASE . ' .<br>';
      }
    }
    if (! isset($attributes['pairwise-id']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Pseudonymous requires pairwise-id.<br>';
    }

    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Pseudonymous requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Pseudonymous requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $this->status['ok'] .= 'All the attributes required to fulfil Pseudonymous were sent<br>';
      if ( isset($ecs['https://refeds.org/category/pseudonymous']) ) {
        $this->status['testResult'] = 'Pseudonymous attributes OK, Entity Category Support OK';
      } else {
        $this->status['testResult'] = 'Pseudonymous attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Pseudonymous but doesn't announce it in its metadata.";
        $part2 = "Please add 'https://refeds.org/category/pseudonymous' ". $this->toListStr;
        $this->status['warning'] .= $part1 . '<br>' . $part2 .  '<br>';
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/pseudonymous']) ) {
        $this->status['testResult'] = 'Pseudonymous attributes missing, BUT Entity Category Support claimed';
        $this->status['error'] .= 'The IdP does NOT support Pseudonymous but it claims that it does in its metadata!!<br>';
      } else {
        $this->status['testResult'] = 'Pseudonymous attribute missing, Entity Category Support missing';
      }
    }
  }

  /**
   * Check https://refeds.org/category/personalized
   *
   * * Check  if all attributes that are requied are sent
   * * Verify that announced support for EC is correct
   *
   * @param array $attributes Attributes released
   *
   * @param array $ecs EC:s the IdP claims to support
   */
  protected function checkPersonalized( $attributes, $ecs) {
    $checkIsOK = false;
    if (! isset($attributes['eduPersonAssurance']) ) {
      $this->status['warning'] .= 'Personalized requires eduPersonAssurance.<br>';
    } else {
      $checkArray = array ('IAP/low', 'ID/unique', 'ID/eppn-unique-no-reassign', 'ATP/ePA-1m');
      $checkOKArray = array();

      foreach (explode(';',$attributes['eduPersonAssurance']) as $row) {
        if (substr($row,0,28) == self::RAF_BASE) {
          $checkIsOK = true;
          $part = substr($row,29);
          if ($part != '') {
            $checkOKArray[$part] = true;
          }
        }
      }

      if ($checkIsOK) {
        foreach ($checkArray as $part) {
          if (! isset($checkOKArray[$part])) {
            $this->status['warning'] .=
              'SWAMID recommends that eduPersonAssurance contains ' . self::RAF_BASE . '/' . $part . '.<br>';
          }
        }
      } else {
        $this->status['warning'] .=
          'Personalized requires that eduPersonAssurance at least contains ' . self::RAF_BASE . ' .<br>';
      }
    }
    # displayName, givenName and sn must exist for Personalized
    if ( !(isset($attributes['displayName']) && isset($attributes['givenName']) && isset($attributes['sn'])) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Personalized requires displayName, givenName and sn.<br>';
    }
    # both mail must exist
    if (! isset($attributes['mail']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Personalized requires mail.<br>';
    }

    if (! isset($attributes['subject-id']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Personalized requires subject-id.<br>';
    }

    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Personalized requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $this->status['warning'] .= 'Personalized requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $this->status['ok'] .= 'All the attributes required to fulfil Personalized were sent<br>';
      if ( isset($ecs['https://refeds.org/category/personalized']) ) {
        $this->status['testResult'] = 'Personalized attributes OK, Entity Category Support OK';
      } else {
        $this->status['testResult'] = 'Personalized attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Personalized but doesn't announce it in its metadata.";
        $part2 = "Please add 'https://refeds.org/category/personalized' " . $this->toListStr;
        $this->status['warning'] .= $part1 . '<br>' . $part2 . '<br>';
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/personalized']) ) {
        $this->status['testResult'] = 'Personalized attributes missing, BUT Entity Category Support claimed';
        $this->status['error'] .= 'The IdP does NOT support Personalized but it claims that it does in its metadata!!<br>';
      } else {
        $this->status['testResult'] = 'Personalized attribute missing, Entity Category Support missing';
      }
    }
  }

  /**
   * Check Code Of Conduct
   *
   * * Check  if all attributes that are requied are sent
   * * Verify that announced support for EC is correct
   *
   * @param array $ecs EC:s the IdP claims to support
   *
   * @param string $ecsValue EC to check
   */
  protected function checkCoCo( $ecs, $ecsValue = '' ) {
    # If status[error] contains any value at this point, then the IdP doesn't support CoCo
    if ( $this->status['error'] == '' ) {
      $this->status['ok'] .= 'Fulfils Code of Conduct<br>';
      if (isset($ecs[$ecsValue] ) ) {
        $this->status['testResult'] = 'CoCo OK, Entity Category Support OK';
      } else {
        $this->status['testResult'] = 'CoCo OK, Entity Category Support missing';
        $part1 = "The IdP supports CoCo but doesn't announce it in its metadata.";
        $part2 = "Please add '" .$ecsValue. "' " . $this->toListStr;
        $this->status['warning'] .= $part1 . '<br>' . $part2 . '<br>';
      }
    } else {
      if ( isset($ecs[$ecsValue]) )  {
        $this->status['testResult'] = 'CoCo is not supported, BUT Entity Category Support is claimed';
        $this->status['error'] .= 'The IdP does NOT support CoCo but it claims that it does in its metadata!!<br>';
      } else {
        $this->status['testResult'] = 'Support for CoCo missing, Entity Category Support missing';
      }
    }
  }

  /**
   * Check https://myacademicid.org/entity-categories/esi
   *
   * * Check  if all attributes that are requied are sent and in correct format
   *
   * @param array $attributes Attributes released
   */
  protected function checkESI( $attributes) {
    if ( isset($attributes['schacPersonalUniqueCode'])) {
      $rows=0;
      foreach (explode(';',$attributes['schacPersonalUniqueCode']) as $row) {
        if (strtolower(substr($row,0,37)) == 'urn:schac:personaluniquecode:int:esi:') {
          if (strtolower(substr($row,0,40)) == 'urn:schac:personaluniquecode:int:esi:se:') {
            $this->status['error'] .=
              'schacPersonalUniqueCode should not announce SE. Use ladok.se / eduid.se or &lt;sHO&gt;.se<br>';
            $this->status['testResult'] = 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
          } elseif (substr($row,0,37) == 'urn:schac:personalUniqueCode:int:esi:') {
            $this->status['testResult'] = 'schacPersonalUniqueCode OK';
          } else {
            # Some chars not in correct case
            $this->status['warning'] .=
              'schacPersonalUniqueCode in wrong case. Not urn:schac:personalUniqueCode:int:esi.';
            $this->status['warning'] .= ' Might create problem in some SP:s<br>';
            $this->status['testResult'] = 'schacPersonalUniqueCode OK. BUT wrong case';
          }
        } else {
          $this->status['error'] .= 'schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:<br>';
          $this->status['testResult'] = 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
        }
        $rows++;
      }
      if ($rows > 1) {
        $this->status['warning'] .= 'schacPersonalUniqueCode should only contain <b>one</b> value.<br>';
        if ($this->status['testResult'] == '' ) {
          $this->status['testResult'] = 'More than one schacPersonalUniqueCode';
        }
      }
      if ($this->status['testResult'] == '' ) {
        $this->status['testResult'] = 'schacPersonalUniqueCode OK';
      }
    } else {
      $this->status['testResult'] = 'Missing schacPersonalUniqueCode';
    }
  }

  /**
   * Setup RAF/MFA
   *
   * Used by checkRAF and checkMFA
   *
   * @param array $attributes
   *
   * @param array $ac List of values from Assurance-Certification
   *
   * @return void
   */
  protected function setupAssurance(array &$attributes, array &$ac) {
    $this->rafAttributes = array(
      self::RAF_BASE                   => array ('level' => 'AL1', 'status' => 'Missing'),
      self::RAF_BASE . '/profile/cappuccino' => array ('level' => 'AL2', 'status' => 'NotExpected'),
      self::RAF_BASE . '/profile/espresso' => array ('level' => 'AL3', 'status' => 'NotExpected'),
      self::RAF_BASE . '/ID/unique'    => array ('level' => 'AL1', 'status' => 'NotExpected'),
      self::RAF_BASE . '/ID/eppn-unique-no-reassign' => array ('level' => 'AL1', 'status' => 'NotExpected'),
      self::RAF_LOW      => array ('level' => 'AL1', 'status' => 'NotExpected'),
      self::RAF_MEDIUM   => array ('level' => 'AL2', 'status' => 'NotExpected'),
      self::RAF_HIGH     => array ('level' => 'AL3', 'status' => 'NotExpected'),
      self::RAF_BASE . '/IAP/local-enterprise' => array ('level' => 'AL2', 'status' => 'NotExpected'),
      self::RAF_BASE . '/ATP/ePA-1m'   => array ('level' => 'AL1', 'status' => 'NotExpected')
    );

    # Fetch user AL level
    if (isset($attributes['eduPersonAssurance'])) {
      foreach (explode(';',$attributes['eduPersonAssurance']) as $ALevel) {
        switch ($ALevel) {
          case self::RAF_LOW : # NOSONAR Should be http://
            if ($this->userAL < 'AL1') { $this->userAL = 'AL1'; }
            $this->rafAttributes[self::RAF_BASE . '/ID/unique']['status'] = 'Missing';
            $this->rafAttributes[self::RAF_BASE . '/ID/eppn-unique-no-reassign']['status'] = 'Missing';
            $this->rafAttributes[self::RAF_LOW]['status'] = 'Missing';
            $this->rafAttributes[self::RAF_BASE . '/ATP/ePA-1m']['status'] = 'Missing';
            break;
          case self::RAF_MEDIUM : # NOSONAR Should be http://
            if ($this->userAL < 'AL2') { $this->userAL = 'AL2'; }
            $this->rafAttributes[self::RAF_BASE . '/profile/cappuccino']['status'] = 'Missing';
            $this->rafAttributes[self::RAF_MEDIUM]['status'] = 'Missing';
            $this->rafAttributes[self::RAF_BASE . '/IAP/local-enterprise']['status'] = 'Missing';
            break;
          case self::RAF_HIGH : # NOSONAR Should be http://
            if ($this->userAL < 'AL3') { $this->userAL = 'AL3'; }
            $this->rafAttributes[self::RAF_BASE . '/profile/espresso']['status'] = 'Missing';
            $this->rafAttributes[self::RAF_HIGH]['status'] = 'Missing';
            break;
          default:
        }
      }

      foreach (explode(';',$attributes['eduPersonAssurance']) as $value) {
        if (isset($this->rafAttributes[$value])) {
          if ($this->rafAttributes[$value]['level'] > $this->userAL) {
            $this->rafAttributes[$value]['status'] = 'Not Allowed';
            $this->notAllowed = true;
          } else {
            $this->rafAttributes[$value]['status'] = 'OK';
          }
        }
      }
    }
  }

  /**
   * Checks values in eduPersonAssurance
   *
   * @param array $attributes
   *
   * @param array $ac List of values from Assurance-Certification
   *
   * @return void
   */
  protected function checkRAF(array &$attributes, array &$ac) {
    $missing = false;
    $this->setupAssurance($attributes, $ac);

    $this->status['infoText'] = sprintf('    <h3>Assurance Levels</h3>
    <table class="table table-striped table-bordered">
      <tr><th>Assurance Level of user</th><td>%s</td></tr>
    </table>
    <h3>Received Assurance Values</h3>
    <table class="table table-striped table-bordered">%s',
      $this->userAL == '' ? 'None' : $this->userAL, "\n");
    foreach ($this->rafAttributes as $key => $data) {
      switch ($data['status']) {
        case 'Missing' :
          if ($data['level'] <= $this->userAL ) {
            $missing=true;
            $this->status['infoText'] .= "    <tr><th>$key</th><td>Missing</td></tr>\n";
          }
          break;
        case 'NotExpected' :
          # OK do nothing
          break;
        case 'Not Allowed' :
        case 'OK' :
          #Print Info from status
          $this->status['infoText'] .="    <tr><th>$key</th><td>".$data['status']."</td></tr>\n";
          break;
        default :
      }
    }
    if ($this->userAL == '') {
      $this->status['infoText'] .= "    <tr><th>No Assurance information recived</th></tr>\n";
    }
    $this->status['infoText'] .="    </table>\n";

    if ($this->notAllowed) {
      $this->status['error'] .= 'Identity Provider is sending invalid Assurance information.<br>';
      $this->status['testResult'] = 'Sends invalid Assurance information.';
    } elseif ($this->userAL == '') {
      $this->status['error'] .= 'Missing Assurance information. Expected at least ' . self::RAF_BASE . '<br>';
      $this->status['testResult'] = 'Missing ' . self::RAF_BASE . ' for user.';
    } elseif ($missing) {
      $this->status['warning'] .= 'Missing some Assurance information.<br>';
      $this->status['testResult'] = 'Missing some Assurance information.';
    } else {
      $this->status['ok'] .= "Assurance attribute release for current user follows REFED's recommendations.<br>";
      $this->status['testResult'] = 'Sends recommended Assurance information.';
    }
  }

  /**
   * Checks MFA response
   *
   * @param array $attributes
   *
   * @param array $ac List of values from Assurance-Certification
   *
   * @return void
   */
  protected function checkMFA(array &$attributes, array &$ac) {
    $this->setupAssurance($attributes, $ac);
    $mfaDone = $_SERVER['Shib-AuthnContext-Class'] == 'https://refeds.org/profile/mfa';
    $forceAuthnSuccess = false;
    $step2 = false;
    if (isset($_GET['forceAuthn'])) {
      # Step2
      $step2 = true;
      if (isset($_SESSION['ts'])) {
        $forceAuthnTime = strtotime($_SERVER['Shib-Authentication-Instant']) - $_SESSION['ts'];
        if ($_SESSION['ts'] <> $_SERVER['Shib-Authentication-Instant']) {
          $forceAuthnSuccess = true;
          $forceAuthnResult = $forceAuthnTime < 600 ? 'OK' : 'Not done within 10 minutes' . $forceAuthnTime;
        } else {
          $forceAuthnSuccess = false;
          $this->status['error'] .= "Authentication-instant hasn't updated after forceAuthn was requested.<br>";
          $forceAuthnResult = 'Error';
        }
      } else {
        print '<div>Please restart mfa-test. Click on "Previous test"</div>' . "\n";
      }
      unset ($_SESSION['ts']);
    } else {
      # Step1
      $_SESSION['ts'] = time();
      $forceAuthnResult = 'Not tested';
    }

    $this->status['infoText'] = sprintf('    <h3>Test results</h3>%s    <table class="table table-striped table-bordered">%s',
      "\n", "\n");
    $this->status['infoText'] .= sprintf('      <tr><th>MFA status</th><td>%s</td></tr>%s', $mfaDone ? "OK" : "Error", "\n");
    $this->status['infoText'] .= sprintf('      <tr><th>ForceAuthn status</th><td>%s</td></tr>%s', $forceAuthnResult, "\n");

    $this->showRAFAttributeStatus('AL1 status','http://www.swamid.se/policy/assurance/al1'); # NOSONAR Should be http://
    $this->showRAFAttributeStatus('AL2 status','http://www.swamid.se/policy/assurance/al2'); # NOSONAR Should be http://
    $this->showRAFAttributeStatus('AL3 status','http://www.swamid.se/policy/assurance/al3'); # NOSONAR Should be http://
    $this->showRAFAttributeStatus('RAF Low status', self::RAF_LOW);
    $this->showRAFAttributeStatus('RAF Medium status', self::RAF_MEDIUM);
    $this->showRAFAttributeStatus('RAF High status', self::RAF_HIGH);

    $this->status['infoText'] .= sprintf('    </table>%s', "\n");

    $this->status['infoText'] .= '
    <h3>Identity Provider sessions attributes</h3>
    <table class="table table-striped table-bordered">
      <tr><th>Attribute</th><th>Value</th></tr>' . "\n";
    foreach (array('Shib-AuthnContext-Class', 'Shib-Authentication-Instant') as $name) {
      if ( isset ($_SERVER[$name])) {
        $this->status['infoText'] .= sprintf ("      <tr><th>%s</th><td>%s</td></tr>\n", substr($name,5), $_SERVER[$name]);
      }
    }
    $this->status['infoText'] .= "    </table>\n";

    $this->status['infoText'] .= '
    <h3>Identity Provider approved Assurance Levels</h3>
    <table class="table table-striped table-bordered">' . "\n";
    if (isset($_SERVER['Meta-Assurance-Certification'])) {
      $value = str_replace(';' , '<br>',$_SERVER['Meta-Assurance-Certification']);
      $this->status['infoText'] .= sprintf ("          <tr><th>Assurance-Certification</th><td>%s</td></tr>\n", $value);
    }
    $this->status['infoText'] .= "    </table>\n";

    if ($mfaDone) {
      if ($forceAuthnSuccess) {
        $this->status['ok'] .= 'Identity Provider supports REFEDS MFA and ForceAuthn.<br>';
        $this->status['testResult'] = 'Supports REFEDS MFA and ForceAuthn.';
      } else {
        if ($step2) {
          $this->status['error'] .= 'Identity Provider supports REFEDS MFA but not ForceAuthn.<br>';
          $this->status['testResult'] = 'Supports REFEDS MFA but not ForceAuthn.';
        } else {
          $this->status['ok'] .= 'Identity Provider supports REFEDS MFA.<br>';
        }
      }
    } else {
      if ($forceAuthnSuccess) {
        $this->status['error'] .= 'Identity Provider does support ForceAuthn but not REFEDS MFA.<br>';
        $this->status['testResult'] = 'Does support ForceAuthn but not REFEDS MFA.';
      } else {
        if ($step2) {
          $this->status['error'] .= 'Identity Provider does neither support REFEDS MFA or ForceAuthn.<br>';
          $this->status['testResult'] = 'Does neither support REFEDS MFA or ForceAuthn.';
        } else {
          $this->status['error'] .= 'Identity Provider does not support REFEDS MFA.<br>';
        }
      }
    }
  }

  /**
   * Checks/prints status of rafAttributes
   *
   * @param string $text Desciption of attributeValue
   *
   * @param string $attributeValue attributeValue to check
   *
   * @return void
   */
  protected function showRAFAttributeStatus($text, $attributeValue) {
    if (isset($this->rafAttributes[$attributeValue]) && $this->rafAttributes[$attributeValue]['status'] <> 'NotExpected') {
      $this->status['infoText'] .= sprintf('      <tr><th>%s</th><td>%s</td></tr>%s',
        $text, $this->rafAttributes[$attributeValue]['status'], "\n");
    }
  }

  /**
   * Show status icons
   */
  protected function showStatus() {
    # If we have any text in OK the show OK image and text
    if ($this->status['ok'] != '' ) {
      print '    <i class="fas fa-check"></i><div>' . $this->status['ok'] . "</div>\n";
    }
    # If we have any text in Warning the show Warning image and text
    if ($this->status['warning'] != '' ) {
      print '    <i class="fas fa-exclamation-triangle"></i><div>' . $this->status['warning'] . "</div>\n";
    }
    # If we have any text in Error the show Error image and text
    if ($this->status['error'] != '' ) {
      print '    <i class="fas fa-exclamation"></i><div>' . $this->status['error'] . "</div>\n";
    }
  }

  /**
   * Fetch info about the IdP from Metadata
   *
   * @return array
   */
  protected function getMetaInfo() {
    $ac = [];
    $ecs = [];
    $ec = [];

    if ( isset($_SERVER['Meta-Assurance-Certification']) ) {
      foreach (explode(";", $_SERVER['Meta-Assurance-Certification']) as $value ) {
        $ac[$value] = $value;
      }
    }

    if ( isset($_SERVER['Meta-Entity-Category-Support']) ) {
      foreach (explode(";", $_SERVER['Meta-Entity-Category-Support']) as $value ) {
        $ecs[$value] = $value;
      }
    }

    if ( isset($_SERVER['Meta-Entity-Category']) ) {
      foreach (explode(";", $_SERVER['Meta-Entity-Category']) as $value ) {
        $ec[$value] = $value;
      }
    }

    return [$ac, $ecs, $ec];
  }
}
