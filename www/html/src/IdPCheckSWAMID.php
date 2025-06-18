<?php
namespace releasecheck;

class IdPCheckSWAMID extends IdPCheck {

  /**
   * Assurance Level of IdP
   */
  protected string $idPAL;

  /**
   * Appoved Assurance Levels of IdP of IdP as a string
   */
  protected string $idPApproved;

  /**
   * Setup the class
   *
   * @return void
   */
  public function __construct() {
    $a = func_get_args();
    $i = func_num_args();
    parent::__construct();
    if (isset($this->config->getFederation()['metadataTool'])) {
      $metadatatool = sprintf('<a href="https://%s">%s</a>', $this->config->getFederation()['metadataTool'], $this->config->getFederation()['metadataTool']);
      $this->toListStr = 'to the list of supported ECs at ' . $metadatatool;
    }
    if (method_exists($this,$f='__construct'.$i)) {
      call_user_func_array(array($this,$f),$a);
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
      'http://www.swamid.se/policy/assurance/al1' => array ('level' => 'AL1', 'status' => 'NotExpected'), # NOSONAR Should be http://
      'http://www.swamid.se/policy/assurance/al2' => array ('level' => 'AL2', 'status' => 'NotExpected'), # NOSONAR Should be http://
      'http://www.swamid.se/policy/assurance/al3' => array ('level' => 'AL3', 'status' => 'NotExpected'), # NOSONAR Should be http://

      self::RAF_BASE                   => array ('level' => 'AL1', 'status' => 'NotExpected'),
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
    $this->idPAL='';
    $this->idPApproved='None';
    $this->notAllowed = false;

    # Fetch max allowed AL-level based on IdP Assurance-Certification
    foreach ($ac as $acLevel) {
      switch ($acLevel) {
        case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
          if ($this->idPAL < 'AL1') { $this->idPAL = 'AL1'; }
          $this->idPApproved = 'AL1';
          $this->rafAttributes['http://www.swamid.se/policy/assurance/al1']['status'] = 'Missing'; # NOSONAR Should be http://
          $this->rafAttributes[self::RAF_BASE]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ID/unique']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ID/eppn-unique-no-reassign']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_LOW]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ATP/ePA-1m']['status'] = 'Missing';
          break;
        case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
          if ($this->idPAL < 'AL2') { $this->idPAL = 'AL2'; }
          $this->idPApproved = 'AL1,AL2';
          $this->rafAttributes['http://www.swamid.se/policy/assurance/al2']['status'] = 'Missing'; # NOSONAR Should be http://
          $this->rafAttributes[self::RAF_BASE . '/profile/cappuccino']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_MEDIUM]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/IAP/local-enterprise']['status'] = 'Missing';
          break;
        case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
          if ($this->idPAL < 'AL3') { $this->idPAL = 'AL3'; }
          $this->idPApproved= 'AL1,AL2,AL3';
          $this->rafAttributes['http://www.swamid.se/policy/assurance/al3']['status'] = 'Missing'; # NOSONAR Should be http://
          $this->rafAttributes[self::RAF_BASE . '/profile/espresso']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_HIGH]['status'] = 'Missing';
          break;
        default:
      }
    }
    # Fetch user AL level
    if (isset($attributes['eduPersonAssurance'])) {
      foreach (explode(';',$attributes['eduPersonAssurance']) as $ALevel) {
        switch ($ALevel) {
          case 'http://www.swamid.se/policy/assurance/al1' : # NOSONAR Should be http://
            if ($this->userAL < 'AL1') { $this->userAL = 'AL1'; }
            break;
          case 'http://www.swamid.se/policy/assurance/al2' : # NOSONAR Should be http://
            if ($this->userAL < 'AL2') { $this->userAL = 'AL2'; }
            break;
          case 'http://www.swamid.se/policy/assurance/al3' : # NOSONAR Should be http://
            if ($this->userAL < 'AL3'  &&
              $_SERVER['Shib-AuthnContext-Class'] == 'https://refeds.org/profile/mfa') {
              $this->userAL = 'AL3';
            }
            break;
          default:
        }
      }

      foreach (explode(';',$attributes['eduPersonAssurance']) as $value) {
        if (isset($this->rafAttributes[$value])) {
          if ($this->rafAttributes[$value]['level'] > $this->userAL ||
            $this->rafAttributes[$value]['level'] > $this->idPAL) {
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

    if ($this->idPAL == 0) {
      if ($this->notAllowed) {
        $this->status['error'] .=
          'Identity Provider is not approved for any SWAMID Identity Assurance Profiles but sends Assurance information!.<br>';
        $this->status['testResult'] = 'Assurance Profile missing. Sends Assurance information!';
      } else {
        $this->status['error'] .= 'Identity Provider is not approved for any SWAMID Identity Assurance Profiles.<br>';
        $this->status['testResult'] = 'Assurance Profile missing.';
      }
      $this->status['infoText'] = '';
    } else {
      $this->status['infoText'] = sprintf('    <h3>Assurance Levels</h3>
    <table class="table table-striped table-bordered">
      <tr><th>IdP approved Assurance Level</th><td>%s</td></tr>
      <tr><th>Assurance Level of user</th><td>%s</td></tr>
    </table>
    <h3>Received Assurance Values</h3>
    <table class="table table-striped table-bordered">%s',
        $this->idPApproved, $this->userAL == '' ? 'None' : $this->userAL, "\n");
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
        $this->status['ok'] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>';
        $this->status['error'] .= 'Identity Provider is sending invalid Assurance information.<br>';
        $this->status['testResult'] = 'Have Assurance Profile. Sends invalid Assurance information.';
      } elseif ($this->userAL == '') {
        $this->status['ok'] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>';
        $this->status['error'] .= 'Missing Assurance information. Expected at least http://www.swamid.se/policy/assurance/al1<br>'; # NOSONAR Should be http://
        $this->status['testResult'] = 'Have Assurance Profile. Missing http://www.swamid.se/policy/assurance/al1 for user.'; # NOSONAR Should be http://
      } elseif ($missing) {
        $this->status['ok'] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.<br>';
        $this->status['warning'] .= 'Missing some Assurance information.<br>';
        $this->status['testResult'] = 'Have Assurance Profile. Missing some Assurance information.';
      } else {
        $this->status['ok'] .= 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles';
        $this->status['ok'] .= "and attribute release for current user follows SWAMID's recommendations.<br>";
        $this->status['testResult'] = 'Have Assurance Profile. Sends recommended Assurance information.';
      }
    }
  }
}
