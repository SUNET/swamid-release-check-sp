<?php
const HTML_RESULT_FOR = "        <h3>Result for %s (%s)%s</h3>\n";
if (isset($_SERVER['Shib-Identity-Provider']) ) {
  $result = true;
  $IdP = $_SERVER['Shib-Identity-Provider'];
  $instructionsSelected="false";
  $instructionsShow="";
  //Load composer's autoloader
  require_once '../vendor/autoload.php';
  $displayName = isset($_SERVER["Meta-displayName"]) ? $_SERVER["Meta-displayName"] : "";
} else {
  $result = false;
  $instructionsSelected="true";
  $instructionsShow=" show";
  //Load composer's autoloader
  require_once 'vendor/autoload.php';
}

$config = new \releasecheck\Configuration();

$testClass = $config->getExtendedClass('TestSuite');
$htmlClass = $config->getExtendedClass('HTML');

$testSuite = new $testClass();
$html = new $htmlClass();
$html->showHeaders();
$display = new \releasecheck\Display();

# Default values
$attributesActive="";
$attributesSelected="false";
$attributesShow="";
#
$entityCategoryActive="";
$entityCategorySelected="false";
$entityCategoryShow="";
#
$mfaActive="";
$mfaSelected="false";
$mfaShow="";
#
$esiActive="";
$esiSelected="false";
$esiShow="";

if (isset($_GET["tab"])) {
  switch ($_GET["tab"]) {
    case 'entityCategory' :
      $entityCategoryActive=" active";
      $entityCategorySelected="true";
      $entityCategoryShow=" show";
      $tab= "entityCategory";
      break;
    case 'esi' :
      $esiActive=" active";
      $esiSelected="true";
      $esiShow=" show";
      $tab= "esi";
      break;
    case 'mfa' :
      $mfaActive=" active";
      $mfaSelected="true";
      $mfaShow=" show";
      $tab='mfa';
      break;
    default:
      $attributesActive=" active";
      $attributesSelected="true";
      $attributesShow=" show";
      $tab= "attributes";
  }
} else {
  $attributesActive=" active";
  $attributesSelected="true";
  $attributesShow=" show";
  $tab= "attributes";
}
printf('    <div class="row">
      <div class="col">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link%s" id="attributes-tab" data-toggle="tab" href="#attributes"
              role="tab" aria-controls="attributes" aria-selected="%s">Attributes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link%s" id="entityCategory-tab" data-toggle="tab"
              href="#entityCategory" role="tab" aria-controls="entityCategory"
              aria-selected="%s">Entity category</a>
          </li>
          <li class="nav-item">
            <a class="nav-link%s" id="mfa-check-tab" data-toggle="tab" href="#mfa-check"
              role="tab" aria-controls="mfa-check" aria-selected="%s">MFA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link%s" id="esi-tab" data-toggle="tab" href="#esi"
              role="tab" aria-controls="esi" aria-selected="%s">ESI</a>
          </li>
        </ul>
      </div>
      <div class="col-4 text-right">%s',
  $attributesActive, $attributesSelected, $entityCategoryActive, $entityCategorySelected,
  $mfaActive, $mfaSelected, $esiActive, $esiSelected, "\n");
if ($result) {
        printf ("        <p><span style=\"white-space: nowrwap\"><b>%s</b><br>%s</span></p>\n",$displayName,$IdP);
}
printf ('        <a data-toggle="collapse" href="#selectIdP" aria-expanded="false" aria-controls="selectIdP">
          <button type="button" class="btn btn-outline-primary">%s IdP</button>
        </a>
      </div>
    </div>
    <br>


    <div class="collapse multi-collapse" id="selectIdP">
      <h2>Select IdP</h2>
      <br>
      <div class="row">
        <div class="col">
          <div id="DS-Thiss"></div>
        </div>
      </div>
    </div><!-- end collapse selectIdP -->

    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade%s%s" id="attributes"
        role="tabpanel" aria-labelledby="attributes-tab">
        <h2>Released attributes from IdP</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://%s/result">
              <button type="button" class="btn btn-success">%s and show attributes</button>
            </a>
          </div>
        </div>
        <h3>
          <i id="attributes-instructions-icon" class="fas fa-chevron-circle-%s"></i>
          <a data-toggle="collapse" href="#attributes-instructions" aria-expanded="%s" aria-controls="attributes-instructions">Instructions</a>
        </h3>
        <div class="collapse%s multi-collapse" id="attributes-instructions">
          <p>Click on the green button to see what attributes your Identity Provider releases.</p>
          <p>Description of all test avaiable in the SWAMID identity federation test suite:
            <ul>
              <li>The Attributes tab shows all attributes the service release to the entityId https://%s/shibboleth. The entityId uses the entity categories:<ul>
                <li>REFEDS Personalized Access Entity Category,</li>
                <li>REFEDS Research and Scholarship Entity Category, and</li>
                <li>REFEDS Data Protection Code of Conduct ver 2.0 Entity Category including all
                  <a href="https://wiki.sunet.se/display/SWAMID/Entity+Category+attribute+release+in+SWAMID">
                  SWAMID Best Practice attributes</a>.
                </li>
              </ul></li>
              <li>The Entity category tab does an exetensive testing of that an Identity Provider follows
                SWAMID Best Practice attribute release via entity categories.</li>
              <li>The MFA tab checks if an Identity Provider is correctly configured for handling request
                for multi-factor login as expected by SWAMID.</li>
              <li>The ESI tab verifies if the Identity Provider release the right attributes for the
                European Digital Student Service Infrastructure.</li>
            </ul>
          </p>
        </div><!-- end collapse -->%s',
  $result ? "Change" : "Select", $attributesShow, $attributesActive,
  $config->basename(), $result ? "Refresh" : "Login" , $result ? "right" : "down",
  $instructionsSelected, $instructionsShow, $config->basename(), "\n");

  $collapseIcons[] = "attributes-instructions";

  if ($result) {
    printf (HTML_RESULT_FOR, $displayName,$IdP, '');
    $display->showAttributeList();
    $display->showIdpMetadataInfo();
    $display->showIdpSessionInfo();
  }
  printf('      </div><!-- End tab-pane attributes -->
      <div class="tab-pane fade %s%s" id="entityCategory"
        role="tabpanel" aria-labelledby="entityCategory-tab">
        <h2>SWAMID Best Practice Attribute Release check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://assurance.%s/%s"><button type="button" class="btn btn-success">Run all tests automatically</button></a>
          </div>
          <div class="col">
            <a href="https://assurance.%s/%s"><button type="button" class="btn btn-success">Run tests manually</button></a>
          </div>%s',
    $entityCategoryShow, $entityCategoryActive,
    $config->basename(),
    $result ?
      sprintf('Shibboleth.sso/Login?entityID=%s&target=%s', $IdP,
        urlencode(sprintf('https://assurance.%s/?quickTest', $config->basename()))
      ) : "?quickTest",
    $config->basename(),
    $result ? "Shibboleth.sso/Login?entityID=$IdP" : "",
    "\n");
  if (! $result ) {
    # Show button to display result after test-buttons
    printf('          <div class="col">
            <a href="https://%s/result/?tab=entityCategory">
              <button type="button" class="btn btn-success">Show results</button>
            </a>
          </div>%s', $config->basename(), "\n");
  }
  printf('        </div>
        <h3>
          <i id="entityCategory-instructions-icon" class="fas fa-chevron-circle-%s"></i>
          <a data-toggle="collapse" href="#entityCategory-instructions" aria-expanded="%s" aria-controls="entityCategory-instructions">Instructions</a>
        </h3>
        <div class="collapse%s multi-collapse" id="entityCategory-instructions">
          <p>In order for SWAMID to work as effectively as possible for students and employees as well as for
            service providers and identity providers, SWAMID recommends that service providers use
            entity categories to get the attributes that they require.</p>
          <p>In order for services within the SWAMID federation to work as effectively as possible, SWAMID recommends
            the use of entity categories. Entity categories benefits not only students and employees but also
            administrators of relying and identity providers by providing a
            stable framework for the release of attributes.</p>
          <p>During autumn 2019, SWAMID has updated its entity category recommendations and these will be implemented
            in our production environment during 2020 and 2021.</p>
          <p>This service is designed to help administrators of identity providers verify that their
            IdP follows the new recommendations.</p>
          <p>SWAMID’s current recommendations for attribute release are available at
            <a href="https://wiki.sunet.se/display/SWAMID/SAML+WebSSO+Service+Provider+Best+Current+Practice">
              https://wiki.sunet.se/display/SWAMID/SAML+WebSSO+Service+Provider+Best+Current+Practice
            </a>.
          </p>
          <p>Example configuration for Shibboleth can be found in the section entitled “Example of metadata
            configuration, attribute resolvers and attribute filters” on the following wiki page
            <a href="https://wiki.sunet.se/display/SWAMID/SAML+WebSSO+Identity+Provider+Best+Current+Practice">
              https://wiki.sunet.se/display/SWAMID/SAML+WebSSO+Identity+Provider+Best+Current+Practice
            </a>.
          </p>
          <p>The SWAMID best practice attribute release check consists of the following tests:</p>
          <ul style="list-style-type:none">%s',
    $result ? "right" : "down", $instructionsSelected, $instructionsShow, "\n");
  foreach ($testSuite->getECTests() as $test) {
    printf ('            <li>
              <a href="https://%s.%s/Shibboleth.sso/Login?target=%s">%s</a> - %s
            </li>%s', $test, $config->basename(), urlencode(sprintf('https://%s.%s/?singleTest', $test, $config->basename())), $test,
          $testSuite->getTestName($test), "\n");
  }
  printf ('          </ul>
          <p>Multiple Code of Conduct test require different attributes which the IdP either SHOULD or SHOULD NOT
            release in accordance REFEDS/GÉANT Code of Conduct.</p>
          <p>For further information on how personal data is processed in SWAMID Best Practice Attribute Release
            check see
            <a href="https://wiki.sunet.se/display/SWAMID/SWAMID+Entity+Category+Release+Check+-+Privacy+Policy">
              https://wiki.sunet.se/display/SWAMID/SWAMID+Entity+Category+Release+Check+-+Privacy+Policy
            </a>
          </p>
        </div><!-- end collapse -->%s', "\n");
  $collapseIcons[] = "entityCategory-instructions";
  if ($result) {
    if ($testruns = $display->getTestruns($IdP, 'entityCategory')) {
      $testrun = $testruns[0];
      if (count($testruns) > 1) {
        print "          <h4>Other results</h4>
          <ul>\n";
        foreach($testruns as $run) {
          printf('            <li><a href="./?tab=entityCategory&id=%d">%s</a></li>%s', $run['id'], $run['time'], "\n");
          # Check if thus run is requested run. In that vase save this run
          if (isset($_GET['id']) && $_GET['id'] == $run['id']) {
            $testrun = $run;
          }
        }
        print "          </ul>\n";
      }
    } else {
      $testrun = array ('id' => 0, 'time' => 'no run');
    }
    printf (HTML_RESULT_FOR, $displayName,$IdP, $testrun['time'] == 'no run' ? '' : ' ('.$testrun['time'].')');
    $display->showResultsECTests($IdP, $testrun['id']);
  }
?>
      </div><!-- End tab-pane entityCategory -->
      <div class="tab-pane fade<?=$mfaShow?><?=$mfaActive?>" id="mfa-check"
        role="tabpanel" aria-labelledby="mfa-check-tab">
        <h2>SWAMID Best Practice MFA check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://mfa.<?=$config->basename()?>/<?=$result ? "Shibboleth.sso/Login?entityID=".$IdP : ""?>">
              <button type="button" class="btn btn-success">Run tests</button>
            </a>
          </div>
<?php if (! $result ) { ?>
          <div class="col">
            <a href="https://<?=$config->basename()?>/result/?tab=mfa">
              <button type="button" class="btn btn-success">Show results</button>
            </a>
          </div><?php } ?>
        </div>
        <h3>
          <i id="mfa-instructions-icon" class="fas fa-chevron-circle-<?=$result ? "right" : "down"?>"></i>
          <a data-toggle="collapse" href="#mfa-instructions" aria-expanded="<?=$instructionsSelected?>"
            aria-controls="mfa-instructions">
            Instructions
          </a>
        </h3>
        <div class="collapse<?=$instructionsShow?> multi-collapse" id="mfa-instructions">
          <p>SWAMID MFA test. This is a two part test<ol>
            <li>REFEDS MFA without forceAuthn</li>
            <li>REFEDS MFA with forceAuthn</li>
          </ol></p>
        </div><!-- end collapse -->
<?php
  $collapseIcons[] = "mfa-instructions";
  if ($result) {
    if ($testruns = $display->getTestruns($IdP, 'mfa')) {
      $testrun = $testruns[0];
      if (count($testruns) > 1) {
        print "          <h4>Other results</h4>
              <ul>\n";
        foreach($testruns as $run) {
          printf('            <li><a href="./?tab=mfa&id=%d">%s</a></li>%s', $run['id'], $run['time'], "\n");
          # Check if thus run is requested run. In that vase save this run
          if (isset($_GET['id']) && $_GET['id'] == $run['id']) {
            $testrun = $run;
          }
        }
        print "          </ul>\n";
      }
    } else {
      $testrun = array ('id' => 0, 'time' => 'no run');
    }
    printf (HTML_RESULT_FOR, $displayName,$IdP, $testrun['time'] == 'no run' ? '' : ' ('.$testrun['time'].')');
    $display->showResultsMFA($IdP, $testrun['id']);
  }
?>
      </div><!-- End tab-pane mfa-check -->
      <div class="tab-pane fade<?=$esiShow?><?=$esiActive?>" id="esi" role="tabpanel" aria-labelledby="esi-tab">
        <h2>SWAMID Best Practice Attribute Release check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://esi.<?=$config->basename()?>/<?=$result ? "Shibboleth.sso/Login?entityID=".$IdP : ""?>">
              <button type="button" class="btn btn-success">Run tests</button>
            </a>
          </div>
<?php if (! $result ) { ?>
          <div class="col">
            <a href="https://<?=$config->basename()?>/result/?tab=esi">
              <button type="button" class="btn btn-success">Show results</button>
            </a>
          </div><?php } ?>
        </div>
        <h3>
          <i id="esi-instructions-icon" class="fas fa-chevron-circle-<?=$result ? "right" : "down"?>"></i>
          <a data-toggle="collapse" href="#esi-instructions" aria-expanded="<?=$instructionsSelected?>"
            aria-controls="esi-instructions">
            Instructions
          </a>
        </h3>
        <div class="collapse<?=$instructionsShow?> multi-collapse" id="esi-instructions">
          <p>European Student Identifier uses the entity category https://myacademicid.org/entity-categories/esi
            for release of attributes from the user's identity provider. This test verifies that all required
            attributes are released during login.</p>
        </div><!-- end collapse -->
<?php
  $collapseIcons[] = "esi-instructions";
  if ($result) {
    if ($testruns = $display->getTestruns($IdP, 'esi')) {
      $testrun = $testruns[0];
      if (count($testruns) > 1) {
        print "          <h4>Other results</h4>
          <ul>\n";
        foreach($testruns as $run) {
          printf('            <li><a href="./?tab=esi&id=%d">%s</a></li>%s', $run['id'], $run['time'], "\n");
          # Check if thus run is requested run. In that vase save this run
          if (isset($_GET['id']) && $_GET['id'] == $run['id']) {
            $testrun = $run;
          }
        }
        print "          </ul>\n";
      }
    } else {
      $testrun = array ('id' => 0, 'time' => 'no run');
    }
    printf (HTML_RESULT_FOR, $displayName,$IdP, $testrun['time'] == 'no run' ? '' : ' ('.$testrun['time'].')');
    $display->showResultsESI($IdP, $testrun['id']);
  }
  printf("      </div><!-- End tab-pane esi -->
      <!-- Include the Seamless Access Sign in Button & Discovery Service -->
      <script src=\"//%s/thiss.js\"></script>
      <script>
        window.onload = function() {
          // Render the Seamless Access button
          thiss.DiscoveryComponent({
            loginInitiatorURL: 'https://%s/Shibboleth.sso/%s?target=https://%s/result',
          }).render('#DS-Thiss');
        };
      </script>\n", $config->getFederation()['DS'], $config->basename(), $config->getFederation()['LoginURL'], $config->basename());
$html->showFooter($collapseIcons);
