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

$mode = 'Lab';  # Prod / QA / Labb
$basename = 'release-check.<org>.<tld>';

$federation = array(
  'displayName' => 'SWAMID',
  # Admin users that should have access to ops.php
  'adminUsers' => array('adminuser1@federation.org', 'adminuser2@federation.org',
    'user1@inst1.org', 'user1@inst2.org'),

  # Optional if you want to extend HTML and TestSuite with an extended version
  # See TestSuiteSWAMID and HTMLSWAMID for examples
  #'extend' => 'SWAMID',

  # Optional if you want to change backgroudColor on the page
  #'backgroundColor' => '#F05523',

  # Optional if you want to change DiscoveryService or want to replace LoginURL
  # If not set defaults to service.seamlessaccess.org and Login';
  #'DS' => 'service.seamlessaccess.org',
  #'LoginURL' => 'DS/seamless-access',
);
