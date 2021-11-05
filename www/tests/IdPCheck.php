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

	function __construct() {
		$a = func_get_args();
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}

	function __construct1($idp) {
		$this->idp=$idp;
	}

	function __construct2($test, $testname) {
		$this->test = $test;
		$this->testname = $testname;
		$this->idp=$_SERVER["Shib-Identity-Provider"];
	}

	function __construct5($test, $testname, $testtab, $expected, $nowarn) {
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
	function showTestHeaders($lasttest,$nexttest) { ?>
		<table class="table table-striped table-bordered">
			<tr><th>Test</th><td><?=$this->testname?></td></tr>
			<tr><th>Tested IdP</th><td><?=$this->idp?></td></tr>
		</table>
		<h4><?php
		if ($lasttest == "") 
			print '<button type="button" class="btn btn-outline-primary">No previous test</button> | ';
		else
			printf ('<a href="https://%s.release-check.swamid.se/Shibboleth.sso/Login?entityID=%s"><button type="button" class="btn btn-outline-primary">Previous test</button></a> | ',$lasttest, $this->idp);

		if ($nexttest == "result") 
			printf ('<a href="https://release-check.swamid.se/Shibboleth.sso/Login?target=https://release-check.swamid.se/result/?tab=%s&entityID=%s"><button type="button" class="btn btn-success">Show the results</button></a>',$this->testtab,$this->idp,$this->testtab);
		else 
			printf ('<a href="https://%s.release-check.swamid.se/Shibboleth.sso/Login?entityID=%s"><button type="button" class="btn btn-success">Next test</button></a>', $nexttest, $this->idp);

		print "</h4>\n";
	}

	###
	# Testa vilka attribut som skickas/saknas. Jämför detta mot vad som krävs för resp EC
	###
	function testAttributes( $subtest ){
		$samlValues = array();
		$extraValues = array();
		$okValues = array();
		$missingValues = array();
		$response = "";
		$missing = False;
		$status = array(
			"ok"	=> "",
			"warning"	=> "", 
			"error"	=> "",
			"testResult" => ""
		);

		list ($AC,$ECS,$EC) = $this->getMetaInfo();

		# Går igenom alla mottagna attribut och varna om visa vilka extra vi får.
		foreach ( $_SERVER as $key => $value ) {
			if ( substr($key,0,5) == "saml_" ) { 
				$nkey=substr($key,5);
				$samlValues[$nkey] = $value;
				if (! isset($this->expected[$nkey]) ) {
					$extraValues[$nkey] = $value;
					if ( isset( $this->nowarn[$nkey] ) ) 
						$status["warning"] = "The IDP has sent too many attributes.<br>";
					else
						$status["error"] = "The IDP has sent too many attributes.<br>";
				}
			}
		}


		foreach ( $this->expected as $key => $value ) {
			if ( isset ($samlValues[$key] ) ) {
				$okValues[$key] = $samlValues[$key];
			} else {
				$missingValues[$key] = $value;
				$missing = true;
			}
		}
			
		if ( $missing ) 
			$status["warning"] .= "The IDP has not sent all the expected attributes. See the comments below.<br>";

		if ( $subtest == "R&S" )
			$status =  $this->checkRandS($okValues, $ECS, $status );

		if ( $subtest == "CoCo" ) 
			$status = $this->checkCoCo($ECS, $status );

		if ( $subtest == "Ladok" ) 
			$status = $this->checkLadok($okValues, $ECS, $status );

		if ( $subtest == "ESI" ) 
			$status = $this->checkESI($okValues, $status );

		if ( $subtest == "RAF" ) 
			$status = $this->checkRAF($okValues, $AC, $status );

		# If we have no warnings or error then we are OK
		if ( $status["ok"] == "" and $status["warning"] == "" and $status["error"] == "" ) {
			$status["ok"] .= "Did not send any attributes that were not requested.<br>";
			if ( $status["testResult"] == "" ) 
				$status["testResult"] = "Did not send any attributes that were not requested.";
		}

		$this->saveToSQL($status,$okValues,$missingValues,$extraValues);
		if ( $subtest == "ESI" ) {
			$stud = false;
			if (isset($okValues['eduPersonAffiliation']) && (!(strpos($okValues['eduPersonAffiliation'], 'student') === false))) {
				$stud = true;
			} elseif (isset($okValues['eduPersonScopedAffiliation']) && (!(strpos($okValues['eduPersonScopedAffiliation'], 'student@') === false))) {
				$stud = true;
			}
			if ($stud) {
				print "\t\t<h5>Checking as Stud-account, saving <b>two</b> results</h5>\n";
				$this->test = 'esi-stud';
				$this->saveToSQL($status,$okValues,$missingValues,$extraValues);
			} else {
				print "\t\t<h5>Checking as none Stud-account, saving <b>one</b> result</h5>\n";
			}
		}
		
		$this->showStatus($status);

		print "\t\t<h3>Received attributes</h3>\n\t\t<table class=\"table table-striped table-bordered\">\n";
		foreach ( $okValues as $key => $value ) {
			$value = str_replace(";" , "<br>",$value);
			print "\t\t\t<tr><th>$key</th><td>$value</td></tr>\n";
		}
		print "\t\t</table>\n";
		

		if (count ($missingValues) ) {
			print "\t\t<h3>Missing attributes (might be OK, see comments below)</h3>\n\t\t<table class=\"table table-striped table-bordered\">\n";
			foreach ( $missingValues as $key => $value )
				print "\t\t\t<tr><th>$key</th><td>$value</td></tr>\n";
			print "\t\t</table>\n";
		
		}

		if (count ($extraValues) ) {
			print "\t\t<h3>Attributes that were not requested/expected</h3>\n\t\t<table class=\"table table-striped table-bordered text-truncate\">\n";
			foreach ( $extraValues as $key => $value )
				print "\t\t\t<tr><th>$key</th><td>$value</td></tr>\n";
			print "\t\t</table>\n";
		
		}
		if (isset($status["infoText"]))
			print $status["infoText"];
	}

	###
	# Sparar ner i SQL
	###
	function saveToSQL($status,$okValues,$missingValues,$extraValues) {
		$dbFile = "/var/www/tests/log/idpStatus";
		if (! file_exists($dbFile) )  {
			$db = new SQLite3("/var/www/tests/log/idpStatus");
			$db->query("CREATE TABLE idpStatus (Idp STRING, Time STRING, Test STRING, Attr_OK STRING, Attr_Missing STRING, Attr_Extra STRING, Status_OK STRING, Status_WARNING STRING, Status_ERROR STRING, TestResult STRING);");
		} else 
			$db = new SQLite3("/var/www/tests/log/idpStatus");
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
		$updateRow = $db->prepare("UPDATE idpStatus SET Time = :time, Attr_OK = :attr_ok, Attr_Missing = :attr_missing, Attr_Extra = :attr_extra, Status_OK = :status_ok, Status_WARNING = :status_warning, Status_ERROR = :status_error, TestResult = :testresultat WHERE Idp = :idp AND Test = :test;");
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
		$updateRow->execute();
	}

	function listKeys($array) {
		$output = "";
		$comma = "";
		foreach( $array as $key=>$data ) {
			$output .= $comma . $key;
			$comma = ',';
		}
		return $output;
	}

	function listKeysWithValues($array) {
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
	function checkRandS( $Attributes, $ECS, $status ) {
		$RandSisOK = False;
		# displayName och/eller (givenName och sn) måste vara med för att R&S
		if ( isset($Attributes["displayName"]) ) 
			$RandSisOK = True;
		if ( isset($Attributes["givenName"]) ) 
			if ( isset($Attributes["sn"]) ) 
				$RandSisOK = True;
		if ( ! $RandSisOK )
			$status["warning"] .= "R&S requires displayName or givenName + sn.<br>";

		# både mail och eduPersonPrincipalName måste vara med !
		if (! isset($Attributes["mail"]) ) {
			$RandSisOK = False;
			$status["warning"] .= "R&S requires mail.<br>";
		}
		if (! isset($Attributes["eduPersonPrincipalName"]) ) {
			$RandSisOK = False;
			$status["warning"] .= "R&S requires eduPersonPrincipalName.<br>";
		}
		if ( $RandSisOK ) {
			$status["ok"] .= "All the attributes required to fulfil R&S were sent<br>";
			if ( isset($ECS["http://refeds.org/category/research-and-scholarship"]) )
				$status["testResult"] = "R&S attributes OK, Entity Category Support OK";
			else {
				$status["testResult"] = "R&S attributes OK, Entity Category Support missing";
				$status["warning"] .= "The IdP supports R&S but doesn't announce it in its metadata.<br>Inform operations@swamid.se that your IdP supports http://refeds.org/category/research-and-scholarship<br>";
			}
		} else {
			if ( isset($ECS["http://refeds.org/category/research-and-scholarship"]) ) {
				$status["testResult"] = "R&S attributes missing, BUT Entity Category Support claimed";
				$status["error"] .= "The IdP does NOT support R&S but it claims that it does in its metadata!!<br>";
			} else 
				$status["testResult"] = "R&S attribute missing, Entity Category Support missing";
		}
		return $status;
	}
	
	###
	# Kollar att inga extra attribut skickas med och jämför med vad IdP:n utger sig supporta ang CoCo
	###
	function checkCoCo( $ECS, $status ) {
		# Om status[error] innehåller något värde i detta läg så stödjer INTE IdP:n CoCo
		if ( $status["error"] == "" ) {
			$status["ok"] .= "Fulfils Code of Conduct<br>";
			if (isset($ECS["http://www.geant.net/uri/dataprotection-code-of-conduct/v1"] ) ) 
				$status["testResult"] = "CoCo OK, Entity Category Support OK";
			else { 
				$status["testResult"] = "CoCo OK, Entity Category Support missing";
				$status["warning"] .= "The IdP supports CoCo but doesn't announce it in its metadata.<br>Inform operations@swamid.se that your IdP supports http://www.geant.net/uri/dataprotection-code-of-conduct/v1<br>";
			}
		} else {
			if ( isset($ECS["http://www.geant.net/uri/dataprotection-code-of-conduct/v1"]) )  {
				$status["testResult"] = "CoCo is not supported, BUT Entity Category Support is claimed";
				$status["error"] .= "The IdP does NOT support CoCo but it claims that it does in its metadata!!<br>";
			} else
				$status["testResult"] = "Support for CoCo missing, Entity Category Support missing";
		}
		return $status;
	}

	###
	# Kontroll av attribut som krävs av ESI
	###
	function checkESI( $Attributes, $status ) {
		if ( isset($Attributes["schacPersonalUniqueCode"])) {
			$rows=0;
			foreach (explode(";",$Attributes["schacPersonalUniqueCode"]) as $row) {
				if (strtolower(substr($row,0,37)) != 'urn:schac:personaluniquecode:int:esi:') {
					$status['error'] .= 'schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:<br>';
					$status['testResult'] = 'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
				}
				$rows++;
				if (strtolower(substr($row,0,40)) == 'urn:schac:personaluniquecode:int:esi:se:') {
					$status['error'] .= 'schacPersonalUniqueCode should not announce SE. Use ladok.se / eduid.se or &lt;sHO&gt;.se<br>';
					$status['testResult'] = 'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
				}
			}
			if ($rows > 1) {
				$status['warning'] .= 'schacPersonalUniqueCode should only contain <b>one</b> value.<br>';
				if ($status['testResult'] == '' ) 
					$status['testResult'] = 'More than one schacPersonalUniqueCode';
			}
			if ($status['testResult'] == '' ) {
				$status['testResult'] = 'schacPersonalUniqueCode OK';
			}
		} else 
			$status['testResult'] = 'Missing schacPersonalUniqueCode';
		return $status;
	}

	###
	# Kontroll av attribut som krävs av Ladok
	###
	function checkLadok( $Attributes, $ECS, $status ) {
		$LadokStaffOK = False;
		$LadokStudOK = False;

		# givenName och sn måste vara med för att Ladok skall fungera för personal
		if ( isset($Attributes["givenName"]) && isset($Attributes["sn"]) ) 
			$LadokStaffOK = True;
		else
			$status["warning"] .= "The attributes <b>givenName</b> and <b>sn</b> are required by <b>Ladok for employees</b>.<br>";

		# norEduPersonNIN måste vara med för att Ladok skall fungera för studenter
		if ( isset($Attributes["norEduPersonNIN"]) ) 
			$LadokStudOK = True;
		else
			$status["warning"] .= "The attribute <b>norEduPersonNIN</b> is required by <b>Ladok for students</b>.<br>";

		# både eduPersonScopedAffiliation och eduPersonPrincipalName måste vara med !
		if (! isset($Attributes["eduPersonScopedAffiliation"]) ) {
			$LadokStaffOK = False;
			$LadokStudOK = False;
			$status["warning"] .= "The attribute <b>eduPersonScopedAffiliation</b> is required by Ladok.<br>";
		}
		if (! isset($Attributes["eduPersonPrincipalName"]) ) {
			$LadokStaffOK = False;
			$LadokStudOK = False;
			$status["warning"] .= "The attribute <b>eduPersonPrincipalName</b> is required by Ladok.<br>";
		}


		if ( $LadokStaffOK ) {
			if ( $LadokStudOK ) {
				$status["ok"] .= "All the attributes required by both <b>Ladok for students</b> and <b>Ladok for employees</b> were sent<br>";
				$status["testResult"] = "OK: Staff & Student";
			} else {
				$status["ok"] .= "All the attributes required by <b>Ladok for employees</b> were sent<br>";
				$status["testResult"] = "OK: Staff";
			}
		} else {
			if ( $LadokStudOK ) {
				$status["ok"] .= "All the attributes required by <b>Ladok for students</b> were sent<br>";
				$status["testResult"] = "OK: Student";
			} else {
				$status["error"] .= "The IdP will not work for either <b>Ladok for employees</b> or <b>Ladok for students</b>!<br>";
				$status["testResult"] = "FAIL";
			}
			
		}
		return $status;
	}
	
	###
	# Kontroll RAF
	###
	function checkRAF( $Attributes, $AC, $status ) {
		$IdPAL=0;
		$UserAL=0;
		$missing = false;
		$notAllowed = false;
		$IdPApproved="";
		$RAFAttribues = array(
			"http://www.swamid.se/policy/assurance/al1" 	=> array ("level" => 1, "status" => "NotExpected"),
			"http://www.swamid.se/policy/assurance/al2" 	=> array ("level" => 2, "status" => "NotExpected"),
			"http://www.swamid.se/policy/assurance/al3" 	=> array ("level" => 3, "status" => "NotExpected"),
		
			"https://refeds.org/assurance" 					=> array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/profile/cappuccino" => array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/profile/espresso" => array ("level" => 3, "status" => "NotExpected"),
			"https://refeds.org/assurance/ID/unique" 		=> array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/ID/eppn-unique-no-reassign" => array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/IAP/low" 			=> array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/IAP/medium" 		=> array ("level" => 2, "status" => "NotExpected"),
			"https://refeds.org/assurance/IAP/high" 		=> array ("level" => 3, "status" => "NotExpected"),
			"https://refeds.org/assurance/IAP/local-enterprise" => array ("level" => 1, "status" => "NotExpected"),
			"https://refeds.org/assurance/ATP/ePA-1m" 		=> array ("level" => 1, "status" => "NotExpected")
		);

		# Plocka fram IdP:ns MAX tillåtna AL Nivå
		foreach ($AC as $ACLevel) {
			switch ($ACLevel) {
				case 'http://www.swamid.se/policy/assurance/al1': 
					if ($IdPAL < 1) $IdPAL = 1;
					$IdPApproved="AL1";
					$RAFAttribues["http://www.swamid.se/policy/assurance/al1"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/profile/cappuccino"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/ID/unique"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/ID/eppn-unique-no-reassign"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/IAP/low"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/IAP/local-enterprise"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/ATP/ePA-1m"]["status"] = "Missing";
					break;
				case 'http://www.swamid.se/policy/assurance/al2': 
					if ($IdPAL < 2) $IdPAL = 2;
					$IdPApproved="AL1,AL2";
					$RAFAttribues["http://www.swamid.se/policy/assurance/al2"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/IAP/medium"]["status"] = "Missing";
					break;
				case 'http://www.swamid.se/policy/assurance/al3': 
					if ($IdPAL < 3) $IdPAL = 3;
					$IdPApproved="AL1,AL2,AL3";
					$RAFAttribues["http://www.swamid.se/policy/assurance/al3"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/profile/espresso"]["status"] = "Missing";
					$RAFAttribues["https://refeds.org/assurance/IAP/high"]["status"] = "Missing";
					break;
				default:
			}
		}

		# Plocka fram inloggad användares AL nivå
		if (isset($Attributes["eduPersonAssurance"])) {
			foreach (explode(";",$Attributes["eduPersonAssurance"]) as $ALevel) {
				switch ($ALevel) {
					case 'http://www.swamid.se/policy/assurance/al1': 
						if ($UserAL < 1) $UserAL = 1;
						break;
					case 'http://www.swamid.se/policy/assurance/al2': 
						if ($UserAL < 2) $UserAL = 2;
						break;
					case 'http://www.swamid.se/policy/assurance/al3': 
						if ($UserAL < 3) $UserAL = 3;
						break;
					default:
				}
			}
		}
		
		
		if (isset($Attributes["eduPersonAssurance"])) {
			foreach (explode(";",$Attributes["eduPersonAssurance"]) as $value) {
				if (isset($RAFAttribues[$value])) {
					if ($RAFAttribues[$value]["level"] > $UserAL && $RAFAttribues[$value]["level"] > $IdPAL) {
						$RAFAttribues[$value]["status"] = "Not Allowed";
						$notAllowed = true;
					} else {
						$RAFAttribues[$value]["status"] = "OK";
					}
				}
			}
		}

		if ($IdPAL == 0) {
			if ($notAllowed) {
				$status["error"] .= "Identity Provider is not approved for any SWAMID Identity Assurance Profiles but sends Assurance information!.<br>";
				$status["testResult"] = "Assurance Profile missing. Sends Assurance information!";
			} else {
				$status["error"] .= "Identity Provider is not approved for any SWAMID Identity Assurance Profiles.<br>";
				$status["testResult"] = "Assurance Profile missing.";
			}
			$status["infoText"] = "";
		} else {
			$status["infoText"] = "		<h3>Assurance Levels</h3>
		<table class=\"table table-striped table-bordered\">
			<tr><th>IdP approved Assurance Level</th><td>$IdPApproved</td></tr>
			<tr><th>Assurance Level of user</th><td>AL$UserAL</td></tr>
		</table>
		<h3>Received Assurance Values</h3>
		<table class=\"table table-striped table-bordered\">\n";

			foreach ($RAFAttribues as $key => $data) {
				switch ($data["status"]) {
					case 'Missing' : 
						if ($data["level"] <= $UserAL ) {
							$missing=true;
							$status["infoText"] .="		<tr><th>$key</th><td>Missing</td></tr>\n";
						}
						break;
					case 'NotExpected':
						# OK do nothing
						break;
					case 'Not Allowed':
					case 'OK': 
						#Print Info from status
						$status["infoText"] .="		<tr><th>$key</th><td>".$data["status"]."</td></tr>\n";
						break;
						
				}
			}
			$status["infoText"] .="		</table>\n";

			if ($notAllowed) {
				$status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
				$status["error"] .= "Identity Provider is sending invalid Assurance information.<br>";
				$status["testResult"] = "Have Assurance Profile. Sends invalid Assurance information.";
			} elseif ($missing) {
				$status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
				$status["warning"] .= "Missing some Assurance information.<br>";
				$status["testResult"] = "Have Assurance Profile. Missing some Assurance information.";
			} else {
				$status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles and attribute release for current user follows SWAMID's recomendations.<br>";
				$status["testResult"] = "Have Assurance Profile. Sends recomended Assurance information.";
			}
		}
		return $status;
	}
	
	###
	# Visar info om IdP:ns Metadata
	###
	function showIdpInfo() {
		$status = array(
			"ok"	=> "",
			"warning"	=> "", 
			"error"	=> ""
		);

		list ($AC,$ECS,$EC) = $this->getMetaInfo();

		#Kolla om vi stödjer Siftfi. Om ej varna
		if ( isset($AC["https://refeds.org/sirtfi"]) ) {
 			$status["testResult"] = "The IdP supports Sirtfi.";
			$status["ok"] = 'The IdP supports Sirtfi.<br>';
		} else {
 			$status["testResult"] = "The IdP does not supports Sirtfi.";
			$status["warning"] = 'SWAMID recommends that all EntityIDs support Sirtfi. For more information, see <a href="https://refeds.org/sirtfi">https://refeds.org/sirtfi</a><br>';
		}

		$this->saveToSQL($status,[],[],[]);
		$this->showStatus( $status );

		if (count($AC) ) {
			print "\t\t<h3>The IdP has the following Assurance Certification</h3>\n";
			print "\t\t<table class=\"table table-striped table-bordered\">\n";
			foreach ($AC as $value)
				print "\t\t\t<tr><th>$value</th></tr>\n";
			print "\t\t</table>\n";
		}

		if (count($ECS) ) {
			print "\t\t<h3>The IdP has the following Entity Category Support</h3>\n";
			print "\t\t<table class=\"table table-striped table-bordered\">\n";
			foreach ($ECS as $value)
				print "\t\t\t<tr><th>$value</th></tr>\n";
			print "\t\t</table>\n";
		}

		if (count($EC) ) {
			print "\t\t<h3>The IdP has the following Entity Category</h3>\n";
			print "\t\t<table class=\"table table-striped table-bordered\">\n";
			foreach ($EC as $value)
				print "\t\t\t<tr><th>$value</th></tr>\n";
			print "\t\t</table>\n";
		}
	}
 	
	###
	# Skriver ut status info med iconer mm.
	###
	function showStatus( $status ) {
		# If we have any text in OK the show OK image and text
		if ($status["ok"] != "" ) {
			print '		<div><i class="fas fa-check"></i><div>' . $status["ok"] . "</div></div>\n";
		}
		# If we have any text in Warning the show Warning image and text
		if ($status["warning"] != "" ) {
			print '		<i class="fas fa-exclamation-triangle"></i><div>' . $status["warning"] . "</div>\n";
		}
		# If we have any text in Error the show Error image and text
		if ($status["error"] != "" ) {
			print '		<i class="fas fa-exclamation"></i><div>' . $status["error"] . "</div>\n";
		}
	}

	###
	# Hämtar info från Metadatan för IdP:n
	###
	function getMetaInfo() {
		$AC = [];
		$ECS = [];
		$EC = [];

		if ( isset($_SERVER["Meta-Assurance-Certification"]) ) 
			foreach (explode(";", $_SERVER["Meta-Assurance-Certification"]) as $value ) 
				$AC[$value] = $value;

		if ( isset($_SERVER["Meta-Entity-Category-Support"]) ) 
			foreach (explode(";", $_SERVER["Meta-Entity-Category-Support"]) as $value )
				$ECS[$value] = $value;

		if ( isset($_SERVER["Meta-Entity-Category"]) ) 
			foreach (explode(";", $_SERVER["Meta-Entity-Category"]) as $value )
				$EC[$value] = $value;

		return [$AC, $ECS, $EC];
	}

	###
	# Visa resultaten för samtlig tester
	###
	function showResults(){
		$this->showHeaders('Result of SWAMID Best Practice Attribute Release check');
		print "\t\t<h2>Result of SWAMID Best Practice Attribute Release check</h2>\n\t\t<p>Here is a summary of the results of the most recent test of the Identity Provider. If you haven't performed all of the tests, then the results of the previous most recent tests are shown. Each test gives more comprehensive details than this summary.</p>\n";

		$db = new SQLite3("/var/www/tests/log/idpStatus");
		$tests = $db->prepare("SELECT * FROM idpStatus WHERE Idp = :idp ORDER BY Test;");
		$tests->bindValue(":idp",$this->idp);
		$result=$tests->execute();
		printf ("entityID : %s<br>\n<br>\n", $this->idp);
		print "\t\t<table border=\"1\">\n\t\t\t<tr><td>Test</td><td>Result</td></tr>\n";
		while ($row=$result->fetchArray(SQLITE3_ASSOC)) {
			printf ("\t\t\t<tr>\n\t\t\t\t<td>%s<br>%s</td>\n\t\t\t\t<td>", $row["Test"], $row["Time"]);
			if ( $row["Status_OK"] ) 
				printf ("\n\t\t\t\t\t<i class=\"fas fa-check\"></i>\n\t\t\t\t\t<div>%s</div>\n\t\t\t\t\t<div class=\"clear\"></div><br>", $row["Status_OK"]);
			if ( $row["Status_WARNING"] ) 
				printf ("\n\t\t\t\t\t<i class=\"fas fa-exclamation-triangle\"></i>\n\t\t\t\t\t<div>%s</div>\n\t\t\t\t\t<div class=\"clear\"></div><br>", $row["Status_WARNING"]);
			if ( $row["Status_ERROR"] ) 
				printf ("\n\t\t\t\t\t<i class=\"fas fa-exclamation\"></i>\n\t\t\t\t\t<div>%s</div>\n\t\t\t\t\t<div class=\"clear\"></div><br>", $row["Status_ERROR"]);
			if ( $row["Attr_OK"] )
				printf ("\n\t\t\t\t\t<div>Received : \n\t\t\t\t\t\t<ul>\n\t\t\t\t\t\t\t<li>%s</li>\n\t\t\t\t\t\t</ul>\n\t\t\t\t\t</div><br>", str_replace(",","</li>\n\t\t\t\t\t\t\t<li>",$row["Attr_OK"]));
 			if ( $row["Attr_Missing"] ) {
				$temp= str_replace(",","#",$row["Attr_Missing"]);
				$temp= str_replace("# ",",",$temp);
				printf ("\n\t\t\t\t\t<div>Missing : \n\t\t\t\t\t\t<ul>\n\t\t\t\t\t\t\t<li>%s</li>\n\t\t\t\t\t\t</ul>\n\t\t\t\t\t</div><br>", str_replace("#","</li>\n\t\t\t\t\t\t\t<li>",$temp));
			}
 			if ( $row["Attr_Extra"] )
				printf ("\n\t\t\t\t\t<div>Not expected : \n\t\t\t\t\t\t<ul>\n\t\t\t\t\t\t\t<li>%s</li>\n\t\t\t\t\t\t</ul>\n\t\t\t\t\t</div><br>", str_replace(",","</li>\n\t\t\t\t\t\t\t<li>",$row["Attr_Extra"]));
 			if ( $row["TestResult"] )
				printf ("\n\t\t\t\t\t<div>Test result  : %s</div>", $row["TestResult"]);
			print "\n\t\t\t\t</td>\n\t\t\t</tr>\n";
		}
		print "\t\t</table>\n";
	}


	###
	# Print start of webpage
	###
	function showHeaders($title = "") {
		if ( $title == "" )
			$title = $this->testname;
?>
<html>
<head>
	<meta charset="UTF-8">
	<title><?=$title?></title>
	<link href="//release-check.swamid.se/fontawesome/css/fontawesome.min.css" rel="stylesheet">
	<link href="//release-check.swamid.se/fontawesome/css/solid.min.css" rel="stylesheet">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
					<li role="presentation" class="nav-item"><a href="https://www.sunet.se/swamid/" class="nav-link">About SWAMID</a></li>
					<li role="presentation" class="nav-item"><a href="https://www.sunet.se/swamid/kontakt/" class="nav-link">Contact us</a></li>
				</ul>
			</nav>
			<h3 class="text-muted"><a href="/index.php"><img src="https://release-check.swamid.se/swamid-logo-2-100x115.png" width="55"></a> Release-check</h3>
		</div>
<?php	}

	###
	# Print footer of webpage
	###
	function showFooter() {
?>
	</div><!-- End container-->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script>
		$(function () { 
<?php foreach ($collapseIcons as $collapseIcon) { ?>
			$('#<?=$collapseIcon?>').on('show.bs.collapse', function () {
				var tag_id = document.getElementById('<?=$collapseIcon?>-icon');
				tag_id.className = "fas fa-chevron-circle-down";
			})
			$('#<?=$collapseIcon?>').on('hide.bs.collapse', function () {
				var tag_id = document.getElementById('<?=$collapseIcon?>-icon');
				tag_id.className = "fas fa-chevron-circle-right";
			})
<?php } ?>
		})
	</script> 
  </body>
</html>
</body>
</html>
<?php	}
}
