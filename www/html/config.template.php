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

#$userLevels = array(
#  'adminuser1@federation.org' => 20,
#  'adminuser2@federation.org' => 20,
#  'user1@inst1.org' => 10,
#  'user1@inst2.org' => 5,
#);

$federation = array(
  'displayName' => 'SWAMID',

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
