<?php
###
# Used by other functions that need access to the Database
###
function setupDB() {
	global $db,$idp;
	$db = new SQLite3("/var/www/tests/log/idpStatus");
	$idp=$_SERVER["Shib-Identity-Provider"];
}

###
# Show result for testsuite 1
###
function showResultsSuite1($idp){
	global $db;
	$testDesc = array(
		"noec" => "No EC (shall not send any attributes!)",
		"rands" => "REFEDS R&S",
		"personalized" => "REFEDS Personalized",
		"cocov1-1" => "GÉANT CoCo (v1) part 1, from SWAMID",
		"cocov1-2" => "GÉANT CoCo (v1) part 2, from SWAMID",
		"cocov1-3" => "GÉANT CoCo (v1), from outside SWAMID",
		"cocov2-1" => "GÉANT CoCo (v2) part 1, from SWAMID",
		"cocov2-2" => "GÉANT CoCo (v2) part 2, from SWAMID",
		"cocov2-3" => "GÉANT CoCo (v2), from outside SWAMID",

	);

	$tests = $db->prepare("SELECT * FROM idpStatus WHERE Idp = :idp AND Test = :test;");
	$tests->bindValue(":idp",$idp);
	$tests->bindParam(":test",$test);

	print '          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>' . "\n";
	foreach (array('noec', 'rands', 'cocov1-1', 'cocov1-2', 'cocov1-3') as $test) {
		$result=$tests->execute();
		if ($row=$result->fetchArray(SQLITE3_ASSOC)) {
			printRow($row,$testDesc[$test]);
		} else
			printf ("            <tr><td>Test not run yet</td><td><h5>%s</h5></td></tr>\n", $testDesc[$test]);
	}
	foreach (array('personalized', 'cocov2-1', 'cocov2-2', 'cocov2-3') as $test) {
		$result=$tests->execute();
		if ($row=$result->fetchArray(SQLITE3_ASSOC)) {
			printRow($row,$testDesc[$test]);
		}
	}
	print "          </table>\n";
}

###
# Show result for ESItest
###
function showResultsESI($idp){
	global $db;

	$testDesc = array(
		"esi" => "European Student Identifier (any account)",
		"esi-stud" => "European Student Identifier (student account)",
	);

	$tests = $db->prepare("SELECT * FROM idpStatus WHERE Idp = :idp AND Test = :test;");
	$tests->bindValue(":idp",$idp);
	$tests->bindParam(":test",$test);
	
	print '          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>' . "\n";
	foreach (array('esi-stud', 'esi') as $test) {
		$result=$tests->execute();
		if ($row=$result->fetchArray(SQLITE3_ASSOC)) {
			printRow($row,$testDesc[$test]);
		} else
			printf ("            <tr><td>Test not run yet</td><td><h5>%s</h5></td></tr>\n", $testDesc[$test]);
	}
	print "          </table>\n";
}

###
# Show result for Ladoktest
###
function showResultsLadok($idp){
	global $db;

	$tests = $db->prepare("SELECT * FROM idpStatus WHERE Idp = :idp AND Test = 'ladok';");
	$tests->bindValue(":idp",$idp);

echo <<<EOF
          <table class="table table-striped table-bordered">
            <tr><th>Test</th><th>Result</th></tr>
EOF;
	$result=$tests->execute();
	if ($row=$result->fetchArray(SQLITE3_ASSOC)) {
		printRow($row);
	} else
		print "            <tr><td>ladok</td><td>Test not run yet</td></tr>\n";
	print "          </table>\n";
}

function printRow($row, $desc="") {
	if ($desc == '') {
		printf ("            <tr>\n              <td>%s<br>%s</td>\n              <td>", $row["Test"], $row["Time"]);
	} else {
		printf ("            <tr>\n              <td>%s</td>\n              <td><h5>%s</h5>", $row["Time"], $desc);
	}
	if ( $row["Status_OK"] ) 
		printf ("\n                <i class=\"fas fa-check\"></i>\n                <div>%s</div>\n                <div class=\"clear\"></div><br>", $row["Status_OK"]);
	if ( $row["Status_WARNING"] ) 
		printf ("\n                <i class=\"fas fa-exclamation-triangle\"></i>\n                <div>%s</div>\n                <div class=\"clear\"></div><br>", $row["Status_WARNING"]);
	if ( $row["Status_ERROR"] ) 
		printf ("\n                <i class=\"fas fa-exclamation\"></i>\n          <div>%s</div>\n          <div class=\"clear\"></div><br>", $row["Status_ERROR"]);
	if ( $row["Attr_OK"] )
		printf ("\n                <div>Received : \n                  <ul>\n                    <li>%s</li>\n                  </ul>\n                </div><br>", str_replace(",","</li>\n                    <li>",$row["Attr_OK"]));
	if ( $row["Attr_Missing"] ) {
		$temp= str_replace(",","#",$row["Attr_Missing"]);
		$temp= str_replace("# ",",",$temp);
		printf ("\n                <div>Missing : \n                  <ul>\n                    <li>%s</li>\n                  </ul>\n                </div><br>", str_replace("#","</li>\n                    <li>",$temp));
	}
	if ( $row["Attr_Extra"] )
		printf ("\n                <div>Not expected : \n                  <ul>\n                    <li>%s</li>\n                  </ul>\n                </div><br>", str_replace(",","</li>\n                    <li>",$row["Attr_Extra"]));
	if ( $row["TestResult"] )
		printf ("\n                <div>Test result  : %s</div>", $row["TestResult"]);
	print "\n              </td>\n            </tr>\n";
}

function showAttributeList() {
echo <<<EOF
        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>

EOF;
	foreach ( $_SERVER as $key => $value ) {
		if ( substr($key,0,5) == "saml_" ) { 
			$nkey=substr($key,5);
			$value = str_replace(";" , "<br>",$value);
			printf ("          <tr><th>%s</th><td>%s</td></tr>\n", $nkey,$value);
		}
	}
	

echo <<<EOF
        </table>
        <h4>Identity Provider attributes in metadata</h4>
        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>

EOF;
	if ( isset($_SERVER["Meta-Assurance-Certification"]) ) {
		print "          <tr><th>Assurance-Certification</th><td>";
		foreach (explode(";", $_SERVER["Meta-Assurance-Certification"]) as $value ) 
			printf ("%s<br>",$value);
		print "</td></tr>\n";
	}

	if ( isset($_SERVER["Meta-Entity-Category-Support"]) ) {
		print "          <tr><th>Entity-Category-Support</th><td>";
		foreach (explode(";", $_SERVER["Meta-Entity-Category-Support"]) as $value )
			printf ("%s<br>",$value);
		print "</td></tr>\n";
	}

	if ( isset($_SERVER["Meta-Entity-Category"]) ) {
		print "          <tr><th>Entity-Category</th><td>";
		foreach (explode(";", $_SERVER["Meta-Entity-Category"]) as $value )
			printf ("%s<br>",$value);
		print "</td></tr>\n";
	}?>
          <tr><th>errorURL</th><td><?=isset($_SERVER["Meta-errorURL"]) ? "<a href=\"" . $_SERVER["Meta-errorURL"] . "\" target=\”_blank\”><span class=\"d-inline-block text-truncate\" style=\"max-width: 900px;\">" . $_SERVER["Meta-errorURL"] . "</span></a>" : "" ?></td></tr>
          <tr><th>DisplayName</th><td><?=isset($_SERVER["Meta-displayName"]) ?  $_SERVER["Meta-displayName"] : "" ?></td></tr>
          <tr><th>InformationURL</th><td><?=isset($_SERVER["Meta-informationURL"]) ? "<a href=\"" . $_SERVER["Meta-informationURL"] . "\" target=\”_blank\”>" . $_SERVER["Meta-informationURL"] . "</a>" : "" ?></td></tr>
          <tr><th>Logo</th><td><?=isset($_SERVER["Meta-Small-Logo"]) ?  "<img src=\"" . $_SERVER["Meta-Small-Logo"] . "\">" : "" ?></td></tr>
          <tr><th>OrganizationURL</th><td><?=isset($_SERVER["Meta-organizationURL"]) ? "<a href=\"" . $_SERVER["Meta-organizationURL"] . "\" target=\”_blank\”>" . $_SERVER["Meta-organizationURL"] . "</a>" : "" ?></td></tr>
          <tr><th>ContactPerson (administrative)</th><td><?=isset($_SERVER["Meta-Support-Administrative"]) ?  $_SERVER["Meta-Support-Administrative"] : "" ?></td></tr>
          <tr><th>ContactPerson (support)</th><td><?=isset($_SERVER["Meta-Support-Contact"]) ?  $_SERVER["Meta-Support-Contact"] : "" ?></td></tr>
          <tr><th>ContactPerson (technical)</th><td><?=isset($_SERVER["Meta-Support-Technical"]) ?  $_SERVER["Meta-Support-Technical"] : "" ?></td></tr>
          <tr><th>ContactPerson (other)</th><td><?=isset($_SERVER["Meta-Other-Contact"]) ?  $_SERVER["Meta-Other-Contact"] : "" ?></td></tr>
        </table>
        <h4>Identity Provider sessions attributes</h4>
        <table class="table table-striped table-bordered">
          <tr><th>Attribute</th><th>Value</th></tr>
<?php
	foreach (array('Shib-Identity-Provider','Shib-Authentication-Instant','Shib-Authentication-Method','Shib-AuthnContext-Class') as $name) {
		if ( isset ($_SERVER[$name])) 
			printf ("          <tr><th>%s</th><td>%s</td></tr>\n", substr($name,5), $_SERVER[$name]);
	}

	print "        </table>\n";
}
