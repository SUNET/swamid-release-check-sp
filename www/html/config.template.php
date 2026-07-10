<?php

$db = array(
  'servername'  => 'localhost',      # Name of DB server
  'username'    => 'admin',          # Username for DB
  'password'    => 'adminpwd',       # Password for DB NOSONAR
  'name'        => 'releasecheck',   # Name of Database

  # optional parameter

  ###
  # The file path to the SSL certificate authority.
  # Activates PDO::MYSQL_ATTR_SSL_CA in options.
  ###
  # 'caPath' => '/etc/ssl/CA.pem',
);

$basename = 'release-check.<org>.<tld>';

# Languages to offer support for.
# Supported is listed below (based on .po files)
# Rearange order to suite your needs
# Remove unwanted in your installation or change flag if wanted.
# Other options for flags see https://flagcdn.com/en/codes.json
$languages = array(
  'en' => array('name' => 'English', 'flag' => 'gb'),
  'fr' => array('name' => 'Français', 'flag' => 'fr'),
  #'fr' => array('name' => 'French', 'flag' => 'ca'),
  'it' => array('name' => 'Italiano', 'flag' => 'it'),
  'ro' => array('name' => 'Română', 'flag' => 'ro'),
  'sv' => array('name' => 'Svenska', 'flag' => 'se'),
  'sr' => array('name' => 'Cpпcки', 'flag' => 'rs'),
);

$federation = array(
  'displayName' => 'SWAMID',
  # Admin users that should have access to ops.php
  'adminUsers' => array('adminuser1@federation.org', 'adminuser2@federation.org',
    'user1@inst1.org', 'user1@inst2.org'),

  # Urls could be relative or absolute depending on if they are hosted on the same box or not.
  'aboutURL' => 'https://edugain.org/about-edugain/what-is-edugain/',
  'contactURL' => 'https://edugain.org/contact/',
  'logoURL' => 'https://edugain.org/wp-content/uploads/2017/06/header_logo_small-1.gif',
  'logoWidth' => 163,
  'logoHeight' => 40,

  # Optional if you want to extend HTML and TestSuite with an extended version
  # See TestSuiteSWAMID and HTMLSWAMID for examples
  #'extend' => 'SWAMID',

  # Optional if you want to change backgroudColor on the page
  #'backgroundColor' => '#F05523',

  # Optional if you want to change DiscoveryService or want to replace LoginURL
  # If not set defaults to service.seamlessaccess.org and Login';
  #'DS' => 'service.seamlessaccess.org',
  #'LoginURL' => 'DS/seamless-access',
  # Optional if you want to use profiles in magiq-button. Should alline with what is configured in shibboleth2.xml
  #'entityID' => 'https://release-check.dev-edugain.swamid.se/shibboleth',
  #'trustProfile' => 'edugain',

  # Optional if you want to fetch existiong IdP:s from a Metadata Tool
  #'metadataTool' => 'metadata.qa.swamid.se',

  # Optinal if you want to reuse session and not start a new testRun fore each session
  # true or false
  #'reuseSession' => true,

  # Optional if you want to hide the ESI tab
  #'hideESI' => true,

  # Optional instructions at Attributes tab. Default text below
  #'instructionsAttributes' => '<p>' .
  #      _('Click on the green button to see what attributes your Identity Provider releases.') . '</p>
  #        <p>' . sprintf(_('Description of all test available in the %s test suite'), $federation['displayName']) . ':
  #          <ul>
  #            <li>' . _('The Attributes tab shows all attributes the service release to the entityId') .
  #      sprintf(' https://%s/shibboleth. ', $basename) . _('The entityId uses the entity categories') . ':<ul>
  #              <li>' . _('REFEDS Personalized Access Entity Category') . ',</li>
  #              <li>' . _('REFEDS Research and Scholarship Entity Category, and') . '</li>
  #              <li>' . _('REFEDS Data Protection Code of Conduct ver 2.0 Entity Category') . '.</li>
  #            </ul></li>
  #            <li>' . _('The Authentication tab checks if an Identity Provider is correctly configured for' .
  #      ' handling request for different AuthnContextClassRef requests.') . '</li>
  #            <li>' . _('The Entity category tab does an extensive testing of that an Identity Provider follows' .
  #      ' Best Practice for attribute release via entity categories.') . '</li>
  #            <li>' . _('The ESI tab verifies if the Identity Provider release the right attributes for the' .
  #      ' European Digital Student Service Infrastructure.') . '</li>
  #          </ul>
  #        </p>',

  # Optional instructions at EntityCategory tab. Default text below
  #    'instructionsEntityCategory' => '<p>' .
  #      sprintf(_('In order for %s to work as effectively as possible for students and employees as well as for' .
  #        ' service providers and identity providers, %s recommends that service providers use entity categories' .
  #        ' to get the attributes that they require.'), $federation['displayName'], $federation['displayName']) . '</p>
  #        <p>' .
  #      sprintf(
  #        _('In order for services within the %s federation to work as effectively as possible, %s recommends' .
  #        ' the use of entity categories. Entity categories benefits not only students and employees but also' .
  #        ' administrators of relying and identity providers by providing a stable framework for the release' .
  #        ' of attributes.'),
  #        <value of $federation['displayName']>,
  #        <value of $federation['displayName']>
  #      ) . '</p>
  #        <p>' .
  #      sprintf(
  #        _('The %s best practice attribute release check consists of the following tests'),
  #        <value of $federation['displayName']>
  #      ) . ':</p>',

  # Optional instructions at end of EntityCategory tab. Default text below
  #    'instructionsEntityCategoryEnd' => '<p>' .
  #      sprintf(
  #        _('Multiple Code of Conduct test require different attributes which the IdP either SHOULD or SHOULD NOT ' .
  #        'release in accordance REFEDS/%s Code of Conduct.'),
  #        'GÉANT'
  #      ) . '</p>',
  # If you only use one / no Code of Conduct test. Change this into
  # 'instructionsEntityCategoryEnd' => '',
);

$template = array(
  # Header setup
  # src - source of page header content. Values:
  # 'config' - use content from 'template' param
  # 'file' - use content from readable header.php file param located in /www/html/resources/templates folder.
  #          file content will be loaded by applying include_once()
  # 'self' (or any other value) - use default content from HTML class
  'header' => array(
    'src' => 'self',
    'template' => '',
  ),
  'body' => array(),
  'footer' => array(
    'src' => 'self',
    'template' => '',
  )
);
