<?php
const HTML_RESULT_FOR = "        <h3>Result for %s (%s)</h3>\n";
if (isset($_SERVER['Shib-Identity-Provider']) ) {
  $result = true;
  $IdP = $_SERVER['Shib-Identity-Provider'];
  $instructionsSelected="false";
  $instructionsShow="";
  include "../config.php";
  include "../include/header.php";
  include "../include/functions.php";
  setupDB();
  $displayName = isset($_SERVER["Meta-displayName"]) ? $_SERVER["Meta-displayName"] : "";
} else {
  $result = false;
  $instructionsSelected="true";
  $instructionsShow=" show";
  include "config.php";
  include "include/header.php";
  include "include/functions.php";
}

$ECtestsDesc = array(
  'assurance' => 'Assurance Attribute test',
  'noec' => 'No EC (shall not send any attributes!)',
  'anonymous' => 'REFEDS Anonymous Access',
  'pseudonymous' => 'REFEDS Pseudonymous Access',
  'personalized' => 'REFEDS Personalized Access',
  'cocov2-1' => 'REFEDS CoCo (v2) part 1, from SWAMID',
  'cocov2-2' => 'REFEDS CoCo (v2) part 2, from SWAMID',
  'cocov2-3' => 'REFEDS CoCo (v2), from outside SWAMID',
  'cocov1-1' => 'GÉANT CoCo (v1) part 1, from SWAMID',
  'cocov1-2' => 'GÉANT CoCo (v1) part 2, from SWAMID',
  'cocov1-3' => 'GÉANT CoCo (v1), from outside SWAMID',
  'rands' => 'REFEDS R&S',
);

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
    case "entityCategory":
    case "test5":
      $entityCategoryActive=" active";
      $entityCategorySelected="true";
      $entityCategoryShow=" show";
      $tab= "entityCategory";
      break;
    case "esi":
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
?>
    <div class="row">
      <div class="col">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link<?=$attributesActive?>" id="attributes-tab" data-toggle="tab" href="#attributes"
              role="tab" aria-controls="attributes" aria-selected="<?=$attributesSelected?>">Attributes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$entityCategoryActive?>" id="entityCategory-tab" data-toggle="tab"
              href="#entityCategory" role="tab" aria-controls="entityCategory"
              aria-selected="<?=$entityCategorySelected?>">Entity category</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$mfaActive?>" id="mfa-check-tab" data-toggle="tab" href="#mfa-check"
              role="tab" aria-controls="mfa-check" aria-selected="<?=$mfaSelected?>">MFA</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?=$esiActive?>" id="esi-tab" data-toggle="tab" href="#esi"
              role="tab" aria-controls="esi" aria-selected="<?=$esiSelected?>">ESI</a>
          </li>
        </ul>
      </div>
      <div class="col-4 text-right">
<?php if ($result) {
        printf ("        <p><span style=\"white-space: nowrwap\"><b>%s</b><br>%s</span></p>\n",$displayName,$IdP);
}?>
        <a data-toggle="collapse" href="#selectIdP" aria-expanded="false" aria-controls="selectIdP">
          <button type="button" class="btn btn-outline-primary"><?=$result ? "Change" : "Select"?> IdP</button>
        </a>
      </div>
    </div>
    <br>


    <div class="collapse multi-collapse" id="selectIdP">
      <h2>Select IdP</h2>
      <br>
      <div class="row">
        <div class="col">
          <div id='DS-Thiss'></div>
        </div><?php if ($Mode == 'Prod') {?>
        <div class="col">
          <a href="https://<?=$basename?>/Shibboleth.sso/DS/swamid-test?target=https://<?=$basename?>/result">
            <button type="button" class="btn btn-primary">SWAMID Testing</button>
          </a>
        </div><?php } ?>
      </div>
    </div><!-- end collapse selectIdP -->

    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade<?=$attributesShow?><?=$attributesActive?>" id="attributes"
        role="tabpanel" aria-labelledby="attributes-tab">
        <h2>Released attributes from IdP</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://<?=$basename?>/result">
              <button type="button" class="btn btn-success"><?=
                $result ? "Refresh" : "Login" ?> and show attributes</button>
            </a>
          </div>
        </div>
        <h3>
          <i id="attributes-instructions-icon" class="fas fa-chevron-circle-<?=$result ? "right" : "down"?>"></i>
          <a data-toggle="collapse" href="#attributes-instructions" aria-expanded="<?=
          $instructionsSelected?>" aria-controls="attributes-instructions">Instructions</a>
        </h3>
        <div class="collapse<?=$instructionsShow?> multi-collapse" id="attributes-instructions">
          <p>Click on the green button to see what attributes your Identity Provider releases.</p>
          <p>Description of all test avaiable in the SWAMID identity federation test suite:
            <ul>
              <li>The Attributes tab shows all attributes the service release to the entityId https://<?=
              $basename?>/shibboleth. The entityId uses the entity categories:<ul>
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
        </div><!-- end collapse -->
<?php
  $collapseIcons[] = "attributes-instructions";
  if ($result) {
    printf (HTML_RESULT_FOR,$displayName,$IdP);
    showAttributeList();
  }
?>
      </div><!-- End tab-pane attributes -->
      <div class="tab-pane fade <?=$entityCategoryShow?><?=$entityCategoryActive?>" id="entityCategory"
        role="tabpanel" aria-labelledby="entityCategory-tab">
        <h2>SWAMID Best Practice Attribute Release check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://assurance.<?=$basename?>/<?=$result ?
              "Shibboleth.sso/Login?entityID=$IdP&target=https%3A%2F%2Fassurance.$basename%2F%3FquickTest" :
              "?quickTest" ?>"><button type="button" class="btn btn-success">Run all tests automatically</button></a>
          </div>
          <div class="col">
            <a href="https://assurance.<?=$basename?>/<?=$result ?
              "Shibboleth.sso/Login?entityID=$IdP" :
              "" ?>"><button type="button" class="btn btn-success">Run tests manually</button></a>
          </div>
<?php if (! $result ) { ?>
          <div class="col">
            <a href="https://<?=$basename?>/result/?tab=entityCategory">
              <button type="button" class="btn btn-success">Show results</button>
            </a>
          </div><?php } ?>
        </div>
        <h3>
          <i id="entityCategory-instructions-icon" class="fas fa-chevron-circle-<?=$result ?
            "right" : "down"?>"></i>
          <a data-toggle="collapse" href="#entityCategory-instructions" aria-expanded="<?=
          $instructionsSelected?>" aria-controls="entityCategory-instructions">Instructions</a>
        </h3>
        <div class="collapse<?=$instructionsShow?> multi-collapse" id="entityCategory-instructions">
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
          <ul style="list-style-type:none">
<?php foreach ($ECtestsDesc as $test => $desc) {
  printf ('            <li>
            <a href="https://%s.%s/Shibboleth.sso/Login?target=%s">%s</a> - %s
          </li>', $test, $basename, urlencode(sprintf('https://%s.%s/?singleTest', $test, $basename)), $test, $desc);
}
?>
          </ul>
          <p>Multiple Code of Conduct test require different attributes which the IdP either SHOULD or SHOULD NOT
            release in accordance REFEDS/GÉANT Code of Conduct.</p>
          <p>For further information on how personal data is processed in SWAMID Best Practice Attribute Release
            check see
            <a href="https://wiki.sunet.se/display/SWAMID/SWAMID+Entity+Category+Release+Check+-+Privacy+Policy">
              https://wiki.sunet.se/display/SWAMID/SWAMID+Entity+Category+Release+Check+-+Privacy+Policy
            </a>
          </p>
        </div><!-- end collapse -->
<?php
  $collapseIcons[] = "entityCategory-instructions";
  if ($result) {
    printf ("          <h3>Result for %s (%s)</h3>\n",$displayName,$IdP);
    showResultsSuite1($IdP);
  }
?>
      </div><!-- End tab-pane entityCategory -->
      <div class="tab-pane fade<?=$mfaShow?><?=$mfaActive?>" id="mfa-check"
        role="tabpanel" aria-labelledby="mfa-check-tab">
        <h2>SWAMID Best Practice MFA check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://mfa.<?=$basename?>/<?=$result ? "Shibboleth.sso/Login?entityID=".$IdP : ""?>">
              <button type="button" class="btn btn-success">Run tests</button>
            </a>
          </div>
<?php if (! $result ) { ?>
          <div class="col">
            <a href="https://<?=$basename?>/result/?tab=mfa">
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
    printf (HTML_RESULT_FOR,$displayName,$IdP);
    showResultsMFA($IdP);
  }
?>
      </div><!-- End tab-pane mfa-check -->
      <div class="tab-pane fade<?=$esiShow?><?=$esiActive?>" id="esi" role="tabpanel" aria-labelledby="esi-tab">
        <h2>SWAMID Best Practice Attribute Release check</h2>
        <br>
        <div class="row">
          <div class="col">
            <a href="https://esi.<?=$basename?>/<?=$result ? "Shibboleth.sso/Login?entityID=".$IdP : ""?>">
              <button type="button" class="btn btn-success">Run tests</button>
            </a>
          </div>
<?php if (! $result ) { ?>
          <div class="col">
            <a href="https://<?=$basename?>/result/?tab=esi">
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
    printf (HTML_RESULT_FOR,$displayName,$IdP);
    showResultsESI($IdP);
  }
?>
      </div><!-- End tab-pane esi -->
      <!-- Include the Seamless Access Sign in Button & Discovery Service -->
      <script src="//<?=$Mode == 'Prod' ?
        'service.seamlessaccess.org/thiss.js' :
        'ds.qa.swamid.se/thiss.js' ?>"></script>
      <script>
        window.onload = function() {
          // Render the Seamless Access button
          thiss.DiscoveryComponent({
            loginInitiatorURL: 'https://<?=$basename?>/Shibboleth.sso/DS/<?=$Mode == 'Prod' ?
              'seamless-access' : 'swamid-qa' ?>?target=https://<?=$basename?>/result',
          }).render('#DS-Thiss');
        };
      </script>
<?php
  if ($result) {
    include_once "../include/footer.php";
  } else {
    include_once "include/footer.php";
  }
