<?php
/*
 * changelog:
 * 2020-02-28 Created file
 *
 * 2020-11-04 Added checks för LADOK
 *
 * 2021-05-19 Added checks for RAF
 */

class IdPCheck {
  /*
   * String Test
   */
  private $test;
  /*
   * String Testname
   */
  private $testname;
  /*
   * array of expected attributes including description
   */
  private $expected;
  /*
   * array of attributes that we show not warn about
   */
  private $nowarn;
  /*
   * String EntityId for IdP
   */
  private $idp;
  /*
   * Boolean to signal if Idp belongs to SWAMID
   */
  private $swamidIdp;

  private $metadatatool;
  private $toListStr;

  public function __construct() {
    include "../html/config.php"; # NOSONAR

    $a = func_get_args();
    $i = func_num_args();
    $this->basename = array_shift($a);
    if (method_exists($this,$f='__construct'.$i)) {
      call_user_func_array(array($this,$f),$a);
    }

    if (isset($_SERVER['Meta-registrationAuthority']) &&
      $_SERVER['Meta-registrationAuthority'] == 'http://www.swamid.se/') { # NOSONAR Should be http://
      $this->swamidIdp = true;
      if ($Mode == 'QA') {
        $this->metadatatool = "<a href='https://metadata.qa.swamid.se'>metadata.qa.swamid.se</a>";
      } else {
        $this->metadatatool = "<a href='https://metadata.swamid.se'>metadata.swamid.se</a>";
      }
      $this->toListStr = 'to the list of supported ECs at ';
    } else {
      $this->swamidIdp = false;
      $this->toListStr = '';
      $this->metadatatool = '';
    }
  }

  private function __construct6($test, $testname, $testtab, $expected, $nowarn) {
    $this->test = $test;
    $this->testname = $testname;
    $this->testtab = $testtab;
    $this->expected = $expected;
    $this->nowarn = $nowarn;
    $this->idp=$_SERVER["Shib-Identity-Provider"];
  }

  ###
  # show headers for test
  ###
  public function showTestHeaders($lasttest, $nexttest, $singleTest=false, $forceAuthn = false) { ?>
    <table class="table table-striped table-bordered">
      <caption>Test info</caption>
      <tr><th>Test</th><td><?=$this->testname?></td></tr>
      <tr><th>Tested IdP</th><td><?=$this->idp?></td></tr>
    </table>
    <h4>
      <?php
    if ($lasttest == "" || $singleTest) {
      print '<button type="button" class="btn btn-outline-primary">No previous test</button> | ';
    } else {
      printf ('<a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s">
        <button type="button" class="btn btn-outline-primary">Previous test</button>
      </a> | ',$lasttest, $this->basename, $this->idp);
    }

    if ($nexttest == "result" || $singleTest) {
      printf ('<a href="https://%s/Shibboleth.sso/Login?target=https://%s/result/?tab=%s&entityID=%s">
        <button type="button" class="btn btn-success">Show the results</button>
      </a>', $this->basename, $this->basename, $this->testtab,$this->idp,$this->testtab);
    } elseif ($forceAuthn) {
      printf (
        '<a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s&forceAuthn=true&target=https://%s.%s/?forceAuthn">
        <button type="button" class="btn btn-success">Next test</button>
      </a>', $nexttest, $this->basename, $this->idp, $nexttest, $this->basename);
    } else {
      printf ('<a href="https://%s.%s/Shibboleth.sso/Login?entityID=%s">
        <button type="button" class="btn btn-success">Next test</button>
      </a>', $nexttest, $this->basename, $this->idp);
    }

    print "\n    </h4>\n";
  }

  ###
  # Testa vilka attribut som skickas/saknas. Jämför detta mot vad som krävs för resp EC
  ###
  public function testAttributes( $subtest, $quickTest = false ){
    $samlValues = array();
    $extraValues = array();
    $okValues = array();
    $missingValues = array();
    $missing = false;
    $status = array(
      "ok" => "",
      "warning" => "",
      "error" => "",
      "testResult" => ""
    );
    $singleValueAttributes = array(
      'pairwise-id' => true,
      'subject-id' => true,
      'eduPersonPrincipalName' => true
    );

    list ($ac,$ecs,$ec) = $this->getMetaInfo();

    # Går igenom alla mottagna attribut och varna om vilka extra vi får.
    foreach ( $_SERVER as $key => $value ) {
      if ( substr($key,0,5) == "saml_" ) {
        $nkey=substr($key,5);
        $samlValues[$nkey] = $value;
        if (! isset($this->expected[$nkey]) ) {
          $extraValues[$nkey] = $value;
          if ( isset( $this->nowarn[$nkey] ) ) {
            $status["warning"] = "The IDP has sent too many attributes.<br>";
          } else {
            $status["error"] = "The IDP has sent too many attributes.<br>";
          }
        }
      }
    }


    foreach ( $this->expected as $key => $value ) {
      if ( isset ($samlValues[$key] ) ) {
        $okValues[$key] = $samlValues[$key];
        if (strpos($samlValues[$key], ';') && isset($singleValueAttributes[$key])) {
          $status["error"] .= sprintf('Received multi-value for %s, should be single-value!<br>', $key);
        }
      } else {
        $missingValues[$key] = $value;
        $missing = true;
      }
    }

    $status["warning"] .= $missing ?
      'The IDP has not sent all the expected attributes. See the comments below.<br>' : '';
    if ( $subtest == "R&S" ) { $status =  $this->checkRandS($okValues, $ecs, $status ); }
    if ( $subtest == 'anonymous' ) { $status =  $this->checkAnonymous($okValues, $ecs, $status ); }
    if ( $subtest == 'pseudonymous' ) { $status =  $this->checkPseudonymous($okValues, $ecs, $status ); }
    if ( $subtest == 'personalized' ) { $status =  $this->checkPersonalized($okValues, $ecs, $status ); }
    if ( $subtest == "CoCov1" ) { $status = $this->checkCoCo($ecs, $status,
      'http://www.geant.net/uri/dataprotection-code-of-conduct/v1'); # NOSONAR Should be http://
    }
    if ( $subtest == "CoCov2" ) { $status = $this->checkCoCo($ecs, $status,
      'https://refeds.org/category/code-of-conduct/v2');
    }
    if ( $subtest == "Ladok" ) { $status = $this->checkLadok($okValues, $ecs, $status ); }
    if ( $subtest == "ESI" ) { $status = $this->checkESI($okValues, $status ); }
    if ( $subtest == "RAF" ) { $this->checkRAF($okValues, $ac, $status ); }
    if ( $subtest == "MFA" ) { $this->checkMFA($okValues, $ac, $status ); }

    # If we have no warnings or error then we are OK
    if ( $status["ok"] == "" && $status["warning"] == "" && $status["error"] == "" ) {
      $status["ok"] .= "Did not send any attributes that were not requested.<br>";
      if ( $status["testResult"] == "" ) {
        $status["testResult"] = "Did not send any attributes that were not requested.";
      }
    }

    if ( $subtest == "MFA" ) {
      if(isset($_GET['forceAuthn'])) {
        # Save after step 2
        $this->saveToSQL($status,$okValues,$missingValues,$extraValues);
      }
      # Skip save if on step 1
    } else {
      $this->saveToSQL($status,$okValues,$missingValues,$extraValues);
    }
    if ( $subtest == "ESI" ) {
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
        $this->saveToSQL($status,$okValues,$missingValues,$extraValues);
      } else {
        print "    <h5>Checking as none Stud-account, saving <b>one</b> result</h5>\n";
      }
    }
    if ($quickTest) {
      sleep(5);
      if ($quickTest == 'result') {
        header(sprintf ('Location: https://%s/Shibboleth.sso/Login?entityID=%s&target=%s',
          $this->basename, $this->idp,urlencode("https://$this->basename/result/?tab=$this->testtab")), true, 302);
      } else {
        header(sprintf ('Location: https://%s.%s/Shibboleth.sso/Login?entityID=%s&target=%s',
          $quickTest, $this->basename, $this->idp,
          urlencode("https://$quickTest.$this->basename/?quickTest")), true, 302);
      }
    } else {
      $this->showStatus($status);

      if (isset($status["infoText"])) {
        print $status["infoText"];
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

  private function showAttributeTable($title, $attributeArray) {
    printf ('    <h3>%s</h3>
    <table class="table table-striped table-bordered">
      <tr><th>Attribute</th><th>Value</th></tr>%s', $title, "\n");
    foreach ( $attributeArray as $key => $value ) {
      $value = str_replace(";" , "<br>",$value);
      printf('      <tr><th>%s</th><td>%s</td></tr>%s', $key, $value, "\n");
    }
    print "    </table>\n";

  }

  ###
  # Sparar ner i SQL
  ###
  public function saveToSQL($status,$okValues,$missingValues,$extraValues) {
    $dbFile = "/var/www/tests/log/idpStatus";
    if (! file_exists($dbFile) )  {
      $db = new SQLite3($dbFile);
      $db->query(
        "CREATE TABLE idpStatus (
          Idp STRING,
          SwamidIdp INTEGER,
          Time STRING,
          Test STRING,
          Attr_OK STRING,
          Attr_Missing STRING,
          Attr_Extra STRING,
          Status_OK STRING,
          Status_WARNING STRING,
          Status_ERROR STRING,
          TestResult STRING);
        ");
    } else {
      $db = new SQLite3($dbFile);
    }
    $ifExist = $db->prepare("SELECT * FROM idpStatus WHERE Idp = :idp AND Test = :test;");
    $ifExist->bindValue(":idp",$this->idp);
    $ifExist->bindValue(":test",$this->test);
    $result=$ifExist->execute();
    if (! $result->fetchArray()) {
      # Skapar upp raden så att Update i nästa stycke fungerar
      $addRow = $db->prepare("INSERT INTO idpStatus (Idp, Test) VALUES (:idp, :test);");
      $addRow->bindValue(":idp",$this->idp);
      $addRow->bindValue(":test",$this->test);
      $addRow->execute();
    }
    $updateRow = $db->prepare(
      "UPDATE idpStatus
      SET Time = :time,
        Attr_OK = :attr_ok,
        Attr_Missing = :attr_missing,
        Attr_Extra = :attr_extra,
        Status_OK = :status_ok,
        Status_WARNING = :status_warning,
        Status_ERROR = :status_error,
        TestResult = :testresultat,
        SwamidIdp = :swamidIdp
      WHERE Idp = :idp AND Test = :test;");
    $updateRow->bindValue(":idp",$this->idp);
    $updateRow->bindValue(":test",$this->test);
    $updateRow->bindValue(":time", date("Y-m-d H:i:s"));
    $updateRow->bindValue(":attr_ok", $this->listKeys($okValues));
    $updateRow->bindValue(":attr_missing", $this->listKeysWithValues($missingValues));
    $updateRow->bindValue(":attr_extra", $this->listKeys($extraValues));
    $updateRow->bindValue(":status_ok", $status["ok"]);
    $updateRow->bindValue(":status_warning", $status["warning"]);
    $updateRow->bindValue(":status_error", $status["error"]);
    $updateRow->bindValue(":testresultat", $status["testResult"]);
    $updateRow->bindValue(":swamidIdp", $this->swamidIdp ? 1 : 0);
    $updateRow->execute();
  }

  private function listKeys($array) {
    $output = "";
    $comma = "";
    foreach( $array as $key=>$data ) {
      $output .= $comma . $key;
      $comma = ',';
    }
    return $output;
  }

  private function listKeysWithValues($array) {
    $output = "";
    $comma = "";
    foreach( $array as $key=>$data ) {
      $output .= $comma . $key . " - " . $data;
      $comma = ',';
    }
    return $output;
  }

  ###
  # Kollar om alla attribut som krävs för R&S är med och jämför med vad IdP:n utger sig supporta
  ###
  private function checkRandS( $attributes, $ecs, $status ) {
    $randSisOK = false;
    # displayName och/eller (givenName och sn) måste vara med för att R&S
    if ( isset($attributes["displayName"]) ) {
      $randSisOK = true;
    }
    if ( isset($attributes["givenName"]) && isset($attributes["sn"]) ) {
      $randSisOK = true;
    }
    if ( ! $randSisOK ) {
      $status["warning"] .= "R&S requires displayName or givenName + sn.<br>";
    }

    # både mail och eduPersonPrincipalName måste vara med !
    if (! isset($attributes["mail"]) ) {
      $randSisOK = false;
      $status["warning"] .= "R&S requires mail.<br>";
    }
    if (! isset($attributes["eduPersonPrincipalName"]) ) {
      $randSisOK = false;
      $status["warning"] .= "R&S requires eduPersonPrincipalName.<br>";
    }
    if ( $randSisOK ) {
      $status["ok"] .= "All the attributes required to fulfil R&S were sent<br>";
      if ( isset($ecs["http://refeds.org/category/research-and-scholarship"]) ) { # NOSONAR Should be http://
        $status["testResult"] = "R&S attributes OK, Entity Category Support OK";
      } else {
        $status["testResult"] = "R&S attributes OK, Entity Category Support missing";
        $part1 = "The IdP supports R&S but doesn't announce it in its metadata.";
        $part2 = $this->swamidIdp ? "Please add 'http://refeds.org/category/research-and-scholarship' " : ''; # NOSONAR Should be http://
        $part3 = $this->toListStr . $this->metadatatool;
        $status["warning"] .= $part1 . "<br>" . $part2 . $part3 . "<br>";
      }
    } else {
      if ( isset($ecs["http://refeds.org/category/research-and-scholarship"]) ) { # NOSONAR Should be http://
        $status["testResult"] = "R&S attributes missing, BUT Entity Category Support claimed";
        $status["error"] .= "The IdP does NOT support R&S but it claims that it does in its metadata!!<br>";
      } else {
        $status["testResult"] = "R&S attribute missing, Entity Category Support missing";
      }
    }
    return $status;
  }

  ###
  # Kollar om alla attribut som krävs för Pseudonymous är med och jämför med vad IdP:n utger sig supporta
  ###
  private function checkAnonymous( $attributes, $ecs, $status ) {
    $checkIsOK = true;
    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Anonymous requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Anonymous requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $status['ok'] .= 'All the attributes required to fulfil Anonymous were sent<br>';
      if ( isset($ecs['https://refeds.org/category/anonymous']) ) {
        $status['testResult'] = 'Anonymous attributes OK, Entity Category Support OK';
      } else {
        $status['testResult'] = 'Anonymous attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Anonymous but doesn't announce it in its metadata";
        $part2 =  $this->swamidIdp ? "Please add 'https://refeds.org/category/anonymous' " : '';
        $part3 =  $this->toListStr . $this->metadatatool;
        $status["warning"] .= $part1 . "<br>" . $part2 . $part3 ."<br>";
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/anonymous']) ) {
        $status['testResult'] = 'Anonymous attributes missing, BUT Entity Category Support claimed';
        $status['error'] .= 'The IdP does NOT support Anonymous but it claims that it does in its metadata!!<br>';
      } else {
        $status['testResult'] = 'Anonymous attribute missing, Entity Category Support missing';
      }
    }
    return $status;
  }

  ###
  # Kollar om alla attribut som krävs för Pseudonymous är med och jämför med vad IdP:n utger sig supporta
  ###
  private function checkPseudonymous( $attributes, $ecs, $status ) {
    $checkIsOK = false;
    if (! isset($attributes['eduPersonAssurance']) ) {
      $status['warning'] .= 'Pseudonymous requires eduPersonAssurance.<br>';
    } else {
      $checkArray = array ('IAP/low', 'ID/unique', 'ID/eppn-unique-no-reassign', 'ATP/ePA-1m');
      $checkOKArray = array();

      foreach (explode(";",$attributes["eduPersonAssurance"]) as $row) {
        if (substr($row,0,28) == 'https://refeds.org/assurance') {
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
            $status['warning'] .=
              'SWAMID recommends that eduPersonAssurance contains https://refeds.org/assurance/' . $part . '.<br>';
          }
        }
      } else {
        $status['warning'] .=
          'Pseudonymous requires that eduPersonAssurance at least contains https://refeds.org/assurance .<br>';
      }
    }
    if (! isset($attributes['pairwise-id']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Pseudonymous requires pairwise-id.<br>';
    }

    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Pseudonymous requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Pseudonymous requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $status['ok'] .= 'All the attributes required to fulfil Pseudonymous were sent<br>';
      if ( isset($ecs['https://refeds.org/category/pseudonymous']) ) {
        $status['testResult'] = 'Pseudonymous attributes OK, Entity Category Support OK';
      } else {
        $status['testResult'] = 'Pseudonymous attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Pseudonymous but doesn't announce it in its metadata.";
        $part2 = $this->swamidIdp ?
          "Please add 'https://refeds.org/category/pseudonymous' ". $this->toListStr . $this->metadatatool : '';
        $status["warning"] .= $part1 . "<br>" . $part2 .  "<br>";
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/pseudonymous']) ) {
        $status['testResult'] = 'Pseudonymous attributes missing, BUT Entity Category Support claimed';
        $status['error'] .= 'The IdP does NOT support Pseudonymous but it claims that it does in its metadata!!<br>';
      } else {
        $status['testResult'] = 'Pseudonymous attribute missing, Entity Category Support missing';
      }
    }
    return $status;
  }

  ###
  # Kollar om alla attribut som krävs för Personalized är med och jämför med vad IdP:n utger sig supporta
  ###
  private function checkPersonalized( $attributes, $ecs, $status ) {
    $checkIsOK = false;
    if (! isset($attributes['eduPersonAssurance']) ) {
      $status['warning'] .= 'Personalized requires eduPersonAssurance.<br>';
    } else {
      $checkArray = array ('IAP/low', 'ID/unique', 'ID/eppn-unique-no-reassign', 'ATP/ePA-1m');
      $checkOKArray = array();

      foreach (explode(";",$attributes["eduPersonAssurance"]) as $row) {
        if (substr($row,0,28) == 'https://refeds.org/assurance') {
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
            $status['warning'] .=
              'SWAMID recommends that eduPersonAssurance contains https://refeds.org/assurance/' . $part . '.<br>';
          }
        }
      } else {
        $status['warning'] .=
          'Personalized requires that eduPersonAssurance at least contains https://refeds.org/assurance .<br>';
      }
    }
    # displayName, givenName och sn) måste vara med för Personalized (lite hårdare i SWAMID än i specen)
    if ( !(isset($attributes['displayName']) && isset($attributes['givenName']) && isset($attributes['sn'])) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Personalized requires displayName, givenName and sn.<br>';
    }
    # både mail och eduPersonPrincipalName måste vara med !
    if (! isset($attributes['mail']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Personalized requires mail.<br>';
    }

    if (! isset($attributes['subject-id']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Personalized requires subject-id.<br>';
    }

    if (! isset($attributes['schacHomeOrganization']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Personalized requires schacHomeOrganization.<br>';
    }

    if (! isset($attributes['eduPersonScopedAffiliation']) ) {
      $checkIsOK = false;
      $status['warning'] .= 'Personalized requires eduPersonScopedAffiliation.<br>';
    }

    if ( $checkIsOK ) {
      $status['ok'] .= 'All the attributes required to fulfil Personalized were sent<br>';
      if ( isset($ecs['https://refeds.org/category/personalized']) ) {
        $status['testResult'] = 'Personalized attributes OK, Entity Category Support OK';
      } else {
        $status['testResult'] = 'Personalized attributes OK, Entity Category Support missing';
        $part1 = "The IdP supports Personalized but doesn't announce it in its metadata.";
        $part2 = $this->swamidIdp ? "Please add 'https://refeds.org/category/personalized' " : '';
        $part3 = $this->toListStr . $this->metadatatool;
        $status["warning"] .= $part1 . "<br>" . $part2 . $part3 . "<br>";
      }
    } else {
      if ( isset($ecs['https://refeds.org/category/personalized']) ) {
        $status['testResult'] = 'Personalized attributes missing, BUT Entity Category Support claimed';
        $status['error'] .= 'The IdP does NOT support Personalized but it claims that it does in its metadata!!<br>';
      } else {
        $status['testResult'] = 'Personalized attribute missing, Entity Category Support missing';
      }
    }
    return $status;
  }

  ###
  # Kollar att inga extra attribut skickas med och jämför med vad IdP:n utger sig supporta ang CoCo
  ###
  private function checkCoCo( $ecs, $status, $ecsValue = '' ) {
    # Om status[error] innehåller något värde i detta läg så stödjer INTE IdP:n CoCo
    if ( $status["error"] == "" ) {
      $status["ok"] .= "Fulfils Code of Conduct<br>";
      if (isset($ecs[$ecsValue] ) ) {
        $status["testResult"] = "CoCo OK, Entity Category Support OK";
      } else {
        $status["testResult"] = "CoCo OK, Entity Category Support missing";
        $part1 = "The IdP supports CoCo but doesn't announce it in its metadata.";
        $part2 = $this->swamidIdp ? "Please add '" .$ecsValue. "' " . $this->toListStr . $this->metadatatool : '';
        $status["warning"] .= $part1 . "<br>" . $part2 . "<br>";
      }
    } else {
      if ( isset($ecs[$ecsValue]) )  {
        $status["testResult"] = "CoCo is not supported, BUT Entity Category Support is claimed";
        $status["error"] .= "The IdP does NOT support CoCo but it claims that it does in its metadata!!<br>";
      } else {
        $status["testResult"] = "Support for CoCo missing, Entity Category Support missing";
      }
    }
    return $status;
  }

  ###
  # Kontroll av attribut som krävs av ESI
  ###
  private function checkESI( $attributes, $status ) {
    if ( isset($attributes["schacPersonalUniqueCode"])) {
      $rows=0;
      foreach (explode(";",$attributes["schacPersonalUniqueCode"]) as $row) {
        if (strtolower(substr($row,0,37)) == 'urn:schac:personaluniquecode:int:esi:') {
          if (strtolower(substr($row,0,40)) == 'urn:schac:personaluniquecode:int:esi:se:') {
            $status['error'] .=
              'schacPersonalUniqueCode should not announce SE. Use ladok.se / eduid.se or &lt;sHO&gt;.se<br>';
            $status['testResult'] = 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
          } elseif (substr($row,0,37) == 'urn:schac:personalUniqueCode:int:esi:') {
            $status['testResult'] = 'schacPersonalUniqueCode OK';
          } else {
            # Some chars not in correct case
            $status['warning'] .=
              'schacPersonalUniqueCode in wrong case. Not urn:schac:personalUniqueCode:int:esi.';
            $status['warning'] .= ' Might create problem in some SP:s<br>';
            $status['testResult'] = 'schacPersonalUniqueCode OK. BUT wrong case';
          }
        } else {
          $status['error'] .= 'schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:<br>';
          $status['testResult'] = 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
        }
        $rows++;
      }
      if ($rows > 1) {
        $status['warning'] .= 'schacPersonalUniqueCode should only contain <b>one</b> value.<br>';
        if ($status['testResult'] == '' ) {
          $status['testResult'] = 'More than one schacPersonalUniqueCode';
        }
      }
      if ($status['testResult'] == '' ) {
        $status['testResult'] = 'schacPersonalUniqueCode OK';
      }
    } else {
      $status['testResult'] = 'Missing schacPersonalUniqueCode';
    }
    return $status;
  }

  ###
  # Kontroll av attribut som krävs av Ladok
  ###
  private function checkLadok( $attributes, $ecs, $status ) {
    $ladokStaffOK = false;
    $ladokStudOK = false;

    # givenName och sn måste vara med för att Ladok skall fungera för personal
    if ( isset($attributes["givenName"]) && isset($attributes["sn"]) ) {
      $ladokStaffOK = true;
    } else {
      $status["warning"] .=
        'The attributes <b>givenName</b> and <b>sn</b> are required by <b>Ladok for employees</b>.<br>';
    }

    # norEduPersonNIN måste vara med för att Ladok skall fungera för studenter
    if ( isset($attributes["norEduPersonNIN"]) ) {
      $ladokStudOK = true;
    } else {
      $status["warning"] .= "The attribute <b>norEduPersonNIN</b> is required by <b>Ladok for students</b>.<br>";
    }

    # både eduPersonScopedAffiliation och eduPersonPrincipalName måste vara med !
    if (! isset($attributes["eduPersonScopedAffiliation"]) ) {
      $ladokStaffOK = false;
      $ladokStudOK = false;
      $status["warning"] .= "The attribute <b>eduPersonScopedAffiliation</b> is required by Ladok.<br>";
    }
    if (! isset($attributes["eduPersonPrincipalName"]) ) {
      $ladokStaffOK = false;
      $ladokStudOK = false;
      $status["warning"] .= "The attribute <b>eduPersonPrincipalName</b> is required by Ladok.<br>";
    }


    if ( $ladokStaffOK ) {
      if ( $ladokStudOK ) {
        $status["ok"] .=
          'All the attributes required by both <b>Ladok for students</b> and <b>Ladok for employees</b> were sent<br>';
        $status["testResult"] = "OK: Staff & Student";
      } else {
        $status["ok"] .= "All the attributes required by <b>Ladok for employees</b> were sent<br>";
        $status["testResult"] = "OK: Staff";
      }
    } else {
      if ( $ladokStudOK ) {
        $status["ok"] .= "All the attributes required by <b>Ladok for students</b> were sent<br>";
        $status["testResult"] = "OK: Student";
      } else {
        $status["error"] .=
          "The IdP will not work for either <b>Ladok for employees</b> or <b>Ladok for students</b>!<br>";
        $status["testResult"] = "FAIL";
      }
    }
    return $status;
  }

  ###
  # Setup RAF/MFA
  # Used by checkRAF and checkMFA
  ###
  private function setupAssurance(array &$attributes, array &$ac) {
    $this->RAFAttribues = array(
      "http://www.swamid.se/policy/assurance/al1"  => array ("level" => 1, "status" => "NotExpected"), # NOSONAR Should be http://
      "http://www.swamid.se/policy/assurance/al2"  => array ("level" => 2, "status" => "NotExpected"), # NOSONAR Should be http://
      "http://www.swamid.se/policy/assurance/al3"  => array ("level" => 3, "status" => "NotExpected"), # NOSONAR Should be http://

      "https://refeds.org/assurance"      => array ("level" => 1, "status" => "NotExpected"),
      "https://refeds.org/assurance/profile/cappuccino" => array ("level" => 2, "status" => "NotExpected"),
      "https://refeds.org/assurance/profile/espresso" => array ("level" => 3, "status" => "NotExpected"),
      "https://refeds.org/assurance/ID/unique"   => array ("level" => 1, "status" => "NotExpected"),
      "https://refeds.org/assurance/ID/eppn-unique-no-reassign" => array ("level" => 1, "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/low"    => array ("level" => 1, "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/medium"   => array ("level" => 2, "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/high"   => array ("level" => 3, "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/local-enterprise" => array ("level" => 2, "status" => "NotExpected"),
      "https://refeds.org/assurance/ATP/ePA-1m"   => array ("level" => 1, "status" => "NotExpected")
    );
    $this->IdPAL=0;
    $this->UserAL=0;
    $this->IdPApproved="None";
    $this->notAllowed = false;

    # Plocka fram IdP:ns MAX tillåtna AL Nivå
    foreach ($ac as $acLevel) {
      switch ($acLevel) {
        case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
          if ($this->IdPAL < 1) { $this->IdPAL = 1; }
          $this->IdPApproved="AL1";
          $this->RAFAttribues["http://www.swamid.se/policy/assurance/al1"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttribues["https://refeds.org/assurance"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/ID/unique"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/ID/eppn-unique-no-reassign"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/IAP/low"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/ATP/ePA-1m"]["status"] = "Missing";
          break;
        case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
          if ($this->IdPAL < 2) { $this->IdPAL = 2; }
          $this->IdPApproved="AL1,AL2";
          $this->RAFAttribues["http://www.swamid.se/policy/assurance/al2"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttribues["https://refeds.org/assurance/profile/cappuccino"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/IAP/medium"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/IAP/local-enterprise"]["status"] = "Missing";
          break;
        case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
          if ($this->IdPAL < 3) { $this->IdPAL = 3; }
          $this->IdPApproved="AL1,AL2,AL3";
          $this->RAFAttribues["http://www.swamid.se/policy/assurance/al3"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttribues["https://refeds.org/assurance/profile/espresso"]["status"] = "Missing";
          $this->RAFAttribues["https://refeds.org/assurance/IAP/high"]["status"] = "Missing";
          break;
        default:
      }
    }
    # Plocka fram inloggad användares AL nivå
    if (isset($attributes["eduPersonAssurance"])) {
      foreach (explode(";",$attributes["eduPersonAssurance"]) as $ALevel) {
        switch ($ALevel) {
          case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
            if ($this->UserAL < 1) { $this->UserAL = 1; }
            break;
          case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
            if ($this->UserAL < 2) { $this->UserAL = 2; }
            break;
          case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
            if ($this->UserAL < 3  &&
              $_SERVER['Shib-AuthnContext-Class'] == "https://refeds.org/profile/mfa") {
              $this->UserAL = 3;
            }
            break;
          default:
        }
      }

      foreach (explode(";",$attributes["eduPersonAssurance"]) as $value) {
        if (isset($this->RAFAttribues[$value])) {
          if ($this->RAFAttribues[$value]["level"] > $this->UserAL ||
            $this->RAFAttribues[$value]["level"] > $this->IdPAL) {
            $this->RAFAttribues[$value]["status"] = "Not Allowed";
            $this->notAllowed = true;
          } else {
            $this->RAFAttribues[$value]["status"] = "OK";
          }
        }
      }
    }
  }

  ###
  # Kontroll RAF
  ###
  private function checkRAF(array &$attributes, array &$ac, array &$status) {
    $missing = false;
    $this->setupAssurance($attributes, $ac);

    if ($this->IdPAL == 0) {
      if ($this->notAllowed) {
        $status["error"] .=
          "Identity Provider is not approved for any SWAMID Identity Assurance Profiles but sends Assurance information!.<br>";
        $status["testResult"] = "Assurance Profile missing. Sends Assurance information!";
      } else {
        $status["error"] .= "Identity Provider is not approved for any SWAMID Identity Assurance Profiles.<br>";
        $status["testResult"] = "Assurance Profile missing.";
      }
      $status["infoText"] = "";
    } else {
      $status["infoText"] = sprintf('    <h3>Assurance Levels</h3>
    <table class="table table-striped table-bordered">
      <tr><th>IdP approved Assurance Level</th><td>%s</td></tr>
      <tr><th>Assurance Level of user</th><td>%s</td></tr>
    </table>
    <h3>Received Assurance Values</h3>
    <table class="table table-striped table-bordered">%s',
        $this->IdPApproved, $this->UserAL == 0 ? 'None' : 'AL'.$this->UserAL, "\n");
      foreach ($this->RAFAttribues as $key => $data) {
        switch ($data["status"]) {
          case 'Missing' :
            if ($data["level"] <= $this->UserAL ) {
              $missing=true;
              $status["infoText"] .= "    <tr><th>$key</th><td>Missing</td></tr>\n";
            }
            break;
          case 'NotExpected' :
            # OK do nothing
            break;
          case 'Not Allowed' :
          case 'OK' :
            #Print Info from status
            $status["infoText"] .="    <tr><th>$key</th><td>".$data["status"]."</td></tr>\n";
            break;
          default :
        }
      }
      if ($this->UserAL == 0) {
        $status["infoText"] .= "    <tr><th>No Assurance information recived</th></tr>\n";
      }
      $status["infoText"] .="    </table>\n";

      if ($this->notAllowed) {
        $status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $status["error"] .= "Identity Provider is sending invalid Assurance information.<br>";
        $status["testResult"] = "Have Assurance Profile. Sends invalid Assurance information.";
      } elseif ($this->UserAL == 0) {
        $status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $status["error"] .= "Missing Assurance information. Expected at least http://www.swamid.se/policy/assurance/al1<br>"; # NOSONAR Should be http://
        $status["testResult"] = "Have Assurance Profile. Missing http://www.swamid.se/policy/assurance/al1 for user."; # NOSONAR Should be http://
      } elseif ($missing) {
        $status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $status["warning"] .= "Missing some Assurance information.<br>";
        $status["testResult"] = "Have Assurance Profile. Missing some Assurance information.";
      } else {
        $status["ok"] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles';
        $status["ok"] .= "and attribute release for current user follows SWAMID's recommendations.<br>";
        $status["testResult"] = "Have Assurance Profile. Sends recommended Assurance information.";
      }
    }
  }

  ###
  # Kontrollera MFA
  ###
  private function checkMFA(array &$attributes, array &$ac, array &$status) {
    $this->setupAssurance($attributes, $ac);
    $mfaDone = $_SERVER['Shib-AuthnContext-Class'] == "https://refeds.org/profile/mfa";
    session_start();
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
          $status["error"] .= "Authentication-instant hasn't updated after forceAuthn was requested.<br>";
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

    $status["infoText"] = sprintf('    <h3>Test results</h3>%s    <table class="table table-striped table-bordered">%s',
      "\n", "\n");
    $status["infoText"] .= sprintf('      <tr><th>MFA status</th><td>%s</td></tr>%s', $mfaDone ? "OK" : "Error", "\n");
    $status["infoText"] .= sprintf('      <tr><th>ForceAuthn status</th><td>%s</td></tr>%s', $forceAuthnResult, "\n");

    $this->showAttribute('AL1 status','http://www.swamid.se/policy/assurance/al1', $status); # NOSONAR Should be http://
    $this->showAttribute('AL2 status','http://www.swamid.se/policy/assurance/al2', $status); # NOSONAR Should be http://
    $this->showAttribute('AL3 status','http://www.swamid.se/policy/assurance/al3', $status); # NOSONAR Should be http://
    $this->showAttribute('RAF Low status', 'https://refeds.org/assurance/IAP/low', $status);
    $this->showAttribute('RAF Medium status', 'https://refeds.org/assurance/IAP/medium', $status);
    $this->showAttribute('RAF High status', 'https://refeds.org/assurance/IAP/high', $status);

    $status["infoText"] .= sprintf('    </table>%s', "\n");

    $status["infoText"] .= '
    <h3>Identity Provider sessions attributes</h3>
    <table class="table table-striped table-bordered">
      <tr><th>Attribute</th><th>Value</th></tr>' . "\n";
    foreach (array('Shib-AuthnContext-Class', 'Shib-Authentication-Instant') as $name) {
      if ( isset ($_SERVER[$name])) {
        $status["infoText"] .= sprintf ("      <tr><th>%s</th><td>%s</td></tr>\n", substr($name,5), $_SERVER[$name]);
      }
    }
    $status["infoText"] .= "    </table>\n";

    $status["infoText"] .= '
    <h3>Identity Provider approved Assurance Levels</h3>
    <table class="table table-striped table-bordered">' . "\n";
    if (isset($_SERVER['Meta-Assurance-Certification'])) {
      $value = str_replace(';' , '<br>',$_SERVER['Meta-Assurance-Certification']);
      $status["infoText"] .= sprintf ("          <tr><th>Assurance-Certification</th><td>%s</td></tr>\n", $value);
    }
    $status["infoText"] .= "    </table>\n";

    if ($mfaDone) {
      if ($forceAuthnSuccess) {
        $status["ok"] .= "Identity Provider supports REFEDS MFA and ForceAuthn.<br>";
        $status["testResult"] = "Supports REFEDS MFA and ForceAuthn.";
      } else {
        if ($step2) {
          $status["error"] .= "Identity Provider supports REFEDS MFA but not ForceAuthn.<br>";
          $status["testResult"] = "Supports REFEDS MFA but not ForceAuthn.";
        } else {
          $status["ok"] .= "Identity Provider supports REFEDS MFA.<br>";
        }
      }
    } else {
      if ($forceAuthnSuccess) {
        $status["error"] .= "Identity Provider does support ForceAuthn but not REFEDS MFA.<br>";
        $status["testResult"] = "Does support ForceAuthn but not REFEDS MFA.";
      } else {
        if ($step2) {
          $status["error"] .= "Identity Provider does neither support REFEDS MFA or ForceAuthn.<br>";
          $status["testResult"] = "Does neither support REFEDS MFA or ForceAuthn.";
        } else {
          $status["error"] .= "Identity Provider does not support REFEDS MFA.<br>";
        }
      }
    }
    if ($this->IdPAL > $this->UserAL) {
      $status["warning"] .= "Please rerun test with user at AL$this->IdPAL for a more accurate result.<br>";
    }
  }

  private function showAttribute($text, $attributeValue, array &$status) {
    if ($this->RAFAttribues[$attributeValue]['status'] <> 'NotExpected') {
      $status["infoText"] .= sprintf('      <tr><th>%s</th><td>%s</td></tr>%s',
        $text, $this->RAFAttribues[$attributeValue]['status'], "\n");
    }
  }

  ###
  # Skriver ut status info med iconer mm.
  ###
  private function showStatus( $status ) {
    # If we have any text in OK the show OK image and text
    if ($status["ok"] != "" ) {
      print '    <i class="fas fa-check"></i><div>' . $status["ok"] . "</div>\n";
    }
    # If we have any text in Warning the show Warning image and text
    if ($status["warning"] != "" ) {
      print '    <i class="fas fa-exclamation-triangle"></i><div>' . $status["warning"] . "</div>\n";
    }
    # If we have any text in Error the show Error image and text
    if ($status["error"] != "" ) {
      print '    <i class="fas fa-exclamation"></i><div>' . $status["error"] . "</div>\n";
    }
  }

  ###
  # Hämtar info från Metadatan för IdP:n
  ###
  private function getMetaInfo() {
    $ac = [];
    $ecs = [];
    $ec = [];

    if ( isset($_SERVER["Meta-Assurance-Certification"]) ) {
      foreach (explode(";", $_SERVER["Meta-Assurance-Certification"]) as $value ) {
        $ac[$value] = $value;
      }
    }

    if ( isset($_SERVER["Meta-Entity-Category-Support"]) ) {
      foreach (explode(";", $_SERVER["Meta-Entity-Category-Support"]) as $value ) {
        $ecs[$value] = $value;
      }
    }

    if ( isset($_SERVER["Meta-Entity-Category"]) ) {
      foreach (explode(";", $_SERVER["Meta-Entity-Category"]) as $value ) {
        $ec[$value] = $value;
      }
    }

    return [$ac, $ecs, $ec];
  }

  ###
  # Print start of webpage
  ###
  public function showHeaders($title = "") {
    if ( $title == "" )
      $title = $this->testname;
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
  <meta charset="UTF-8">
  <title><?=$title?></title>
  <link href="//<?=$this->basename?>/fontawesome/css/fontawesome.min.css" rel="stylesheet">
  <link href="//<?=$this->basename?>/fontawesome/css/solid.min.css" rel="stylesheet">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
  <link rel="manifest" href="/images/site.webmanifest">
  <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="/images/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="/images/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
  <style>
/* Space out content a bit */
body {
 padding-top: 20px;
 padding-bottom: 20px;
}

/* Everything gets side spacing for mobile first views */
.header {
 padding-right: 15px;
 padding-left: 15px;
}

/* Custom page header */
.header {
 border-bottom: 1px solid #e5e5e5;
}
/* Make the masthead heading the same height as the navigation */
.header h3 {
 padding-bottom: 19px;
 margin-top: 0;
 margin-bottom: 0;
 line-height: 40px;
}
.left {
 float:left;
}
.clear {
 clear: both
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    max-width: 100%;
}

/* color for fontawesome icons */
.fa-check {
 color: green;
}

.fa-exclamation-triangle {
 color: orange;
}

.fa-exclamation {
 color: red;
}

/* Customize container */
@media (min-width: 768px) {
.container {
 max-width: 1230px;
}
}
.container-narrow > hr {
 margin: 30px 0;
}

/* Responsive: Portrait tablets and up */
@media screen and (min-width: 768px) {
/* Remove the padding we set earlier */
.header {
 padding-right: 0;
 padding-left: 0;
}
/* Space out the masthead */
.header {
 margin-bottom: 30px;
}
}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <nav>
        <ul class="nav nav-pills float-right">
          <li role="presentation" class="nav-item">
            <a href="https://www.sunet.se/swamid/" class="nav-link">About SWAMID</a>
          </li>
          <li role="presentation" class="nav-item">
            <a href="https://www.sunet.se/swamid/kontakt/" class="nav-link">Contact us</a>
          </li>
        </ul>
      </nav>
      <h3 class="text-muted">
        <a href="https://<?=$this->basename?>">
          <img alt = "Logo" src="https://<?=$this->basename?>/swamid-logo-2-100x115.png" width="55">
        </a> Release-check
      </h3>
    </div>
<?php }

  ###
  # Print footer of webpage
  ###
  public function showFooter() {
?>
  </div><!-- End container-->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
    integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
  </script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
  </script>
  </body>
</html>
<?php }
}
