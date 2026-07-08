<?php

namespace releasecheck;

class IdPCheckSWAMID extends IdPCheck
{
  protected const S_ASSURANCE = 'http://www.swamid.se/policy/assurance';  # NOSONAR Should be http://
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
  public function __construct()
  {
    $a = func_get_args();
    $i = func_num_args();
    parent::__construct();
    if (isset($this->config->getFederation()['metadataTool'])) {
      $metadatatool = sprintf(
        '<a href="https://%s">%s</a>',
        $this->config->getFederation()['metadataTool'],
        $this->config->getFederation()['metadataTool']
      );
      $this->toListStr = 'to the list of supported ECs at ' . $metadatatool;
    }
    if (method_exists($this, $f = '__construct' . $i)) {
      call_user_func_array(array($this, $f), $a);
    }
  }

  /**
   * Check https://myacademicid.org/entity-categories/esi
   *
   * * Check  if all attributes that are requied are sent and in correct format
   *
   * @param array $attributes Attributes released
   */
  protected function checkESI($attributes)
  {
    if (isset($attributes['schacPersonalUniqueCode'])) {
      $rows = 0;
      foreach (explode(';', $attributes['schacPersonalUniqueCode']) as $row) {
        if (strtolower(substr($row, 0, 37)) == 'urn:schac:personaluniquecode:int:esi:') {
          if (strtolower(substr($row, 0, 40)) == 'urn:schac:personaluniquecode:int:esi:se:') {
            $this->status['error'] .=
              'schacPersonalUniqueCode should not announce SE. Use ladok.se / eduid.se or &lt;sHO&gt;.se<br>';
            $this->status['testResult'] =
              'schacPersonalUniqueCode starting with urn:schac:personalUniqueCode:int:esi:se:';
          } elseif (substr($row, 0, 37) == 'urn:schac:personalUniqueCode:int:esi:') {
            $this->status['testResult'] = 'schacPersonalUniqueCode OK';
          } else {
            # Some chars not in correct case
            $this->status['warning'] .=
              'schacPersonalUniqueCode in wrong case. Not urn:schac:personalUniqueCode:int:esi.';
            $this->status['warning'] .= ' Might create problem in some SP:s<br>';
            $this->status['testResult'] = 'schacPersonalUniqueCode OK. BUT wrong case';
          }
        } else {
          $this->status['error'] .=
            'schacPersonalUniqueCode should start with urn:schac:personalUniqueCode:int:esi:<br>';
          $this->status['testResult'] =
            'schacPersonalUniqueCode not starting with urn:schac:personalUniqueCode:int:esi:';
        }
        $rows++;
      }
      if ($rows > 1) {
        $this->status['warning'] .= 'schacPersonalUniqueCode should only contain <b>one</b> value.<br>';
        if ($this->status['testResult'] == '') {
          $this->status['testResult'] = 'More than one schacPersonalUniqueCode';
        }
      }
      if ($this->status['testResult'] == '') {
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
  protected function setupAssurance(array &$attributes, array &$ac)
  {
    $this->rafAttributes = array(
      $this::S_ASSURANCE . '/al1' =>
        array ('level' => 'AL1', 'status' => 'NotExpected'),
      $this::S_ASSURANCE . '/al2' =>
        array ('level' => 'AL2', 'status' => 'NotExpected'),
      $this::S_ASSURANCE . '/al3' =>
        array ('level' => 'AL3', 'status' => 'NotExpected'),

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
    $this->idPAL = '';
    $this->idPApproved = 'None';
    $this->notAllowed = false;

    # Fetch max allowed AL-level based on IdP Assurance-Certification
    foreach ($ac as $acLevel) {
      switch ($acLevel) {
        case $this::S_ASSURANCE . '/al1':
          if ($this->idPAL < 'AL1') {
            $this->idPAL = 'AL1';
          }
          $this->idPApproved = 'AL1';
          $this->rafAttributes[$this::S_ASSURANCE . '/al1']['status'] =
            'Missing';
          $this->rafAttributes[self::RAF_BASE]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ID/unique']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ID/eppn-unique-no-reassign']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_LOW]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/ATP/ePA-1m']['status'] = 'Missing';
          break;
        case $this::S_ASSURANCE . '/al2':
          if ($this->idPAL < 'AL2') {
            $this->idPAL = 'AL2';
          }
          $this->idPApproved = 'AL1,AL2';
          $this->rafAttributes[$this::S_ASSURANCE . '/al2']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/profile/cappuccino']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_MEDIUM]['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/IAP/local-enterprise']['status'] = 'Missing';
          break;
        case $this::S_ASSURANCE . '/al3':
          if ($this->idPAL < 'AL3') {
            $this->idPAL = 'AL3';
          }
          $this->idPApproved = 'AL1,AL2,AL3';
          $this->rafAttributes[$this::S_ASSURANCE . '/al3']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_BASE . '/profile/espresso']['status'] = 'Missing';
          $this->rafAttributes[self::RAF_HIGH]['status'] = 'Missing';
          break;
        default:
      }
    }
    # Fetch user AL level
    if (isset($attributes['eduPersonAssurance'])) {
      foreach (explode(';', $attributes['eduPersonAssurance']) as $ALevel) {
        switch ($ALevel) {
          case $this::S_ASSURANCE . '/al1':
            if ($this->userAL < 'AL1') {
              $this->userAL = 'AL1';
            }
            break;
          case $this::S_ASSURANCE . '/al2':
            if ($this->userAL < 'AL2') {
              $this->userAL = 'AL2';
            }
            break;
          case $this::S_ASSURANCE . '/al3':
            if (
              $this->userAL < 'AL3'  &&
              $_SERVER['Shib-AuthnContext-Class'] == 'https://refeds.org/profile/mfa'
            ) {
              $this->userAL = 'AL3';
            }
            break;
          default:
        }
      }

      foreach (explode(';', $attributes['eduPersonAssurance']) as $value) {
        if (isset($this->rafAttributes[$value])) {
          if (
            $this->rafAttributes[$value]['level'] > $this->userAL ||
            $this->rafAttributes[$value]['level'] > $this->idPAL
          ) {
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
  protected function checkRAF(array &$attributes, array &$ac)
  {
    $missing = false;
    $this->setupAssurance($attributes, $ac);

    if ($this->idPAL == 0) {
      if ($this->notAllowed) {
        $this->status['error'][] =
          'Identity Provider is not approved for any SWAMID Identity Assurance Profiles' .
          ' but sends Assurance information!.';
        $this->status['testResult'] = 'Assurance Profile missing. Sends Assurance information!';
      } else {
        $this->status['error'][] = 'Identity Provider is not approved for any SWAMID Identity Assurance Profiles.';
        $this->status['testResult'] = 'Assurance Profile missing.';
      }
      $this->status['infoText'] = '';
    } else {
      $this->status['infoText'] = sprintf(
        '    <h3>' . _('Assurance Levels') . '</h3>
    <table class="table table-striped table-bordered">
      <tr><th>' . _('IdP approved Assurance Level') . '</th><td>%s</td></tr>
      <tr><th>' . _('Assurance Level of user') . '</th><td>%s</td></tr>
    </table>
    <h3>' . _('Received Assurance Values') . '</h3>
    <table class="table table-striped table-bordered">%s',
        $this->idPApproved,
        $this->userAL == '' ? _('None') : $this->userAL,
        "\n"
      );
      foreach ($this->rafAttributes as $key => $data) {
        switch ($data['status']) {
          case 'Missing':
            if ($data['level'] <= $this->userAL) {
              $missing = true;
              $this->status['infoText'] .= "    <tr><th>$key</th><td>" . _("Missing") . "</td></tr>\n";
            }
            break;
          case 'NotExpected':
            # OK do nothing
            break;
          case 'Not Allowed':
          case 'OK':
            #Print Info from status
            $this->status['infoText'] .= "    <tr><th>$key</th><td>" . $data['status'] . "</td></tr>\n";
            break;
          default:
        }
      }
      if ($this->userAL == '') {
        $this->status['infoText'] .= "    <tr><th>" . _("No Assurance information received") . "</th></tr>\n";
      }
      $this->status['infoText'] .= "    </table>\n";

      if ($this->notAllowed) {
        $this->status['ok'][] = 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.';
        $this->status['error'][] = 'Identity Provider is sending invalid Assurance information.';
        $this->status['testResult'] = 'Have Assurance Profile. Sends invalid Assurance information.';
      } elseif ($this->userAL == '') {
        $this->status['ok'][] = 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.';
        $this->status['error'][] = 'Missing Assurance information. Expected at least [[SWAMID_ASSURANCE]]/al1';
        $this->status['testResult'] = 'Have Assurance Profile. Missing [[SWAMID_ASSURANCE]]/al1 for user.';
      } elseif ($missing) {
        $this->status['ok'][] = 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.';
        $this->status['warning'][] = 'Missing some Assurance information.';
        $this->status['testResult'] = 'Have Assurance Profile. Missing some Assurance information.';
      } else {
        $this->status['ok'][] = 'Identity Provider is approved for at least one SWAMID Identity Assurance Profiles.' .
          " and attribute release for current user follows SWAMID's recommendations.";
        $this->status['testResult'] = 'Have Assurance Profile. Sends recommended Assurance information.';
      }
    }
  }
}
