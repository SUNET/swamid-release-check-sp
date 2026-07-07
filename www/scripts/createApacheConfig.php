<?php

require_once __DIR__ . '/../html/vendor/autoload.php';
$config = new \releasecheck\Configuration();

$testSuite = $config->getExtendedClass('TestSuite');

print '
Alias /images/ "/var/www/images/"

<IfModule mod_ssl.c>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/tests
	SSLCertificateFile	/etc/dehydrated/cert.pem
	SSLCertificateKeyFile	/etc/dehydrated/privkey.pem
	SSLCertificateChainFile /etc/dehydrated/chain.pem

	<VirtualHost _default_:443>
		ServerAdmin webmaster@localhost

		<Location /result>
			AuthType shibboleth
			ShibRequestSetting requireSession true
			ShibRequestSetting entityIDSelf https://$hostname/shibboleth
			require shib-session
		</Location>

		<Location /admin>
			AuthType shibboleth
			ShibRequestSetting requireSession true
			ShibRequestSetting entityIDSelf https://$hostname/shibboleth
			require shib-session
		</Location>

		DocumentRoot /var/www/html

		<FilesMatch "\.(ttf|ttc|otf|eot|woff|woff2|font.css|css|js)$">
			Header set Access-Control-Allow-Origin "*"
		</FilesMatch>

		SSLEngine on

		<FilesMatch "\.(php)$">
				SSLOptions +StdEnvVars
		</FilesMatch>
	</VirtualHost>
';

foreach ($testSuite->getTests() as $test => $testConfig) {
  printf(
    '
	<VirtualHost _default_:443>
		ServerName %s.%s

		<Location />
			AuthType shibboleth
			ShibRequestSetting requireSession true
			ShibRequestSetting entityIDSelf https://$hostname/shibboleth
			require shib-session
		</Location>
		SSLEngine on
	</VirtualHost>%s',
    $test,
    $config->basename(),
    "\n"
  );
}

print '
</IfModule>';
