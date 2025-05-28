<?php
namespace releasecheck;

class IdPCheckSWAMID extends IdPCheck {

  /**
   * Assurance Level of IdP
   */
  protected string $IdPAL;

  /**
   * Appoved Assurance Levels of IdP of IdP as a string
   */
  protected string $IdPApproved;
  
  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
    if ($this->config->getMode() == 'QA') {
      $metadatatool = "<a href='https://metadata.qa.swamid.se'>metadata.qa.swamid.se</a>";
    } else {
      $metadatatool = "<a href='https://metadata.swamid.se'>metadata.swamid.se</a>";
    }
    $this->toListStr = 'to the list of supported ECs at ' . $metadatatool;
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
    $this->RAFAttributes = array(
      "http://www.swamid.se/policy/assurance/al1" => array ('level' => 'AL1', "status" => "NotExpected"), # NOSONAR Should be http://
      "http://www.swamid.se/policy/assurance/al2" => array ('level' => 'AL2', "status" => "NotExpected"), # NOSONAR Should be http://
      "http://www.swamid.se/policy/assurance/al3" => array ('level' => 'AL3', "status" => "NotExpected"), # NOSONAR Should be http://

      "https://refeds.org/assurance"              => array ('level' => 'AL1', "status" => "NotExpected"),
      "https://refeds.org/assurance/profile/cappuccino" => array ('level' => 'AL2', "status" => "NotExpected"),
      "https://refeds.org/assurance/profile/espresso" => array ('level' => 'AL3', "status" => "NotExpected"),
      "https://refeds.org/assurance/ID/unique"    => array ('level' => 'AL1', "status" => "NotExpected"),
      "https://refeds.org/assurance/ID/eppn-unique-no-reassign" => array ('level' => 'AL1', "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/low"      => array ('level' => 'AL1', "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/medium"   => array ('level' => 'AL2', "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/high"     => array ('level' => 'AL3', "status" => "NotExpected"),
      "https://refeds.org/assurance/IAP/local-enterprise" => array ('level' => 'AL2', "status" => "NotExpected"),
      "https://refeds.org/assurance/ATP/ePA-1m"   => array ('level' => 'AL1', "status" => "NotExpected")
    );
    $this->IdPAL='';
    $this->IdPApproved="None";
    $this->notAllowed = false;

    # Fetch max allowed AL-level based on IdP Assurance-Certification
    foreach ($ac as $acLevel) {
      switch ($acLevel) {
        case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
          if ($this->IdPAL < 'AL1') { $this->IdPAL = 'AL1'; }
          $this->IdPApproved="AL1";
          $this->RAFAttributes["http://www.swamid.se/policy/assurance/al1"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttributes["https://refeds.org/assurance"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/ID/unique"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/ID/eppn-unique-no-reassign"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/IAP/low"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/ATP/ePA-1m"]["status"] = "Missing";
          break;
        case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
          if ($this->IdPAL < 'AL2') { $this->IdPAL = 'AL2'; }
          $this->IdPApproved="AL1,AL2";
          $this->RAFAttributes["http://www.swamid.se/policy/assurance/al2"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttributes["https://refeds.org/assurance/profile/cappuccino"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/IAP/medium"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/IAP/local-enterprise"]["status"] = "Missing";
          break;
        case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
          if ($this->IdPAL < 'AL3') { $this->IdPAL = 'AL3'; }
          $this->IdPApproved="AL1,AL2,AL3";
          $this->RAFAttributes["http://www.swamid.se/policy/assurance/al3"]["status"] = "Missing"; # NOSONAR Should be http://
          $this->RAFAttributes["https://refeds.org/assurance/profile/espresso"]["status"] = "Missing";
          $this->RAFAttributes["https://refeds.org/assurance/IAP/high"]["status"] = "Missing";
          break;
        default:
      }
    }
    # Fetch user AL level
    if (isset($attributes["eduPersonAssurance"])) {
      foreach (explode(";",$attributes["eduPersonAssurance"]) as $ALevel) {
        switch ($ALevel) {
          case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
            if ($this->UserAL < 'AL1') { $this->UserAL = 'AL1'; }
            break;
          case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
            if ($this->UserAL < 'AL2') { $this->UserAL = 'AL2'; }
            break;
          case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
            if ($this->UserAL < 'AL3'  &&
              $_SERVER['Shib-AuthnContext-Class'] == "https://refeds.org/profile/mfa") {
              $this->UserAL = 'AL3';
            }
            break;
          default:
        }
      }

      foreach (explode(";",$attributes["eduPersonAssurance"]) as $value) {
        if (isset($this->RAFAttributes[$value])) {
          if ($this->RAFAttributes[$value]["level"] > $this->UserAL ||
            $this->RAFAttributes[$value]["level"] > $this->IdPAL) {
            $this->RAFAttributes[$value]["status"] = "Not Allowed";
            $this->notAllowed = true;
          } else {
            $this->RAFAttributes[$value]["status"] = "OK";
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

    if ($this->IdPAL == 0) {
      if ($this->notAllowed) {
        $this->status["error"] .=
          "Identity Provider is not approved for any SWAMID Identity Assurance Profiles but sends Assurance information!.<br>";
        $this->status["testResult"] = "Assurance Profile missing. Sends Assurance information!";
      } else {
        $this->status["error"] .= "Identity Provider is not approved for any SWAMID Identity Assurance Profiles.<br>";
        $this->status["testResult"] = "Assurance Profile missing.";
      }
      $this->status["infoText"] = "";
    } else {
      $this->status["infoText"] = sprintf('    <h3>Assurance Levels</h3>
    <table class="table table-striped table-bordered">
      <tr><th>IdP approved Assurance Level</th><td>%s</td></tr>
      <tr><th>Assurance Level of user</th><td>%s</td></tr>
    </table>
    <h3>Received Assurance Values</h3>
    <table class="table table-striped table-bordered">%s',
        $this->IdPApproved, $this->UserAL == '' ? 'None' : $this->UserAL, "\n");
      foreach ($this->RAFAttributes as $key => $data) {
        switch ($data["status"]) {
          case 'Missing' :
            if ($data["level"] <= $this->UserAL ) {
              $missing=true;
              $this->status["infoText"] .= "    <tr><th>$key</th><td>Missing</td></tr>\n";
            }
            break;
          case 'NotExpected' :
            # OK do nothing
            break;
          case 'Not Allowed' :
          case 'OK' :
            #Print Info from status
            $this->status["infoText"] .="    <tr><th>$key</th><td>".$data["status"]."</td></tr>\n";
            break;
          default :
        }
      }
      if ($this->UserAL == '') {
        $this->status["infoText"] .= "    <tr><th>No Assurance information recived</th></tr>\n";
      }
      $this->status["infoText"] .="    </table>\n";

      if ($this->notAllowed) {
        $this->status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $this->status["error"] .= "Identity Provider is sending invalid Assurance information.<br>";
        $this->status["testResult"] = "Have Assurance Profile. Sends invalid Assurance information.";
      } elseif ($this->UserAL == '') {
        $this->status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $this->status["error"] .= "Missing Assurance information. Expected at least http://www.swamid.se/policy/assurance/al1<br>"; # NOSONAR Should be http://
        $this->status["testResult"] = "Have Assurance Profile. Missing http://www.swamid.se/policy/assurance/al1 for user."; # NOSONAR Should be http://
      } elseif ($missing) {
        $this->status["ok"] .= "Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>";
        $this->status["warning"] .= "Missing some Assurance information.<br>";
        $this->status["testResult"] = "Have Assurance Profile. Missing some Assurance information.";
      } else {
        $this->status["ok"] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles';
        $this->status["ok"] .= "and attribute release for current user follows SWAMID's recommendations.<br>";
        $this->status["testResult"] = "Have Assurance Profile. Sends recommended Assurance information.";
      }
    }
  }
}