#!/usr/bin/php
<?php
/**
 * @version     $Revision$:
 * @author      $Author$:
 * @date        $Date$:
 */

if (php_sapi_name() != 'cli')
{
	echo "You have to call the setup script from command line";
	exit(1);
}

if (!function_exists('shell_exec'))
{
	echo "The function shell_exec() is disabled but required";
	exit(1);
}

$curlOk = function_exists('curl_version');
if($curlOk)
{
	$curlVersion = curl_version();
	$curlSslOk = (function_exists('curl_exec') && in_array('https', $curlVersion['protocols'], true));
}
$phpOk = (function_exists('version_compare') && version_compare(phpversion(), '5.3.1', '>='));
$mysqlOk = extension_loaded('mysql');

if (!$phpOk)
{
	echo "PHP VERSION IS TOO OLD, REQUIRED: 5.3.1".PHP_EOL;
	exit(1);
}
if (!$mysqlOk)
{
	echo "mySQL extension not loaded".PHP_EOL;
	exit(1);
}
if (!$curlOk)
{
	echo 'You should install cURL PHP extension'.PHP_EOL;
	echo '  on debian/ubuntu : sudo apt-get install php5-curl'.PHP_EOL;
	exit(1);
}

$configFile = __DIR__.'/config/app.ini';

if (file_exists($configFile))
{
	echo 'The config file app.ini already exists. Continuing will overwrite your settings'.PHP_EOL;
	echo 'Type "Y" to continue';
	if (trim(fgets(STDIN)) != "Y")
	{
		exit(1);
	}
	
}

echo "Initial check: OK".PHP_EOL;

$data = array('DATE' => date('l jS \of F Y h:i:s A'));

echo 'Your application URL?'.PHP_EOL;
echo 'It could be http://178.45.56.26/competitions/'.PHP_EOL;
echo 'or http://example.com/maniaplanet-competition-manager/'.PHP_EOL;
$data['APPLICATION_URL'] = trim(fgets(STDIN));

echo 'Your ManiaLink? It should point to the application URL.'.PHP_EOL;
$data['MANIALINK'] = trim(fgets(STDIN));

echo 'API Username?'.PHP_EOL;
$data['API_USER'] = trim(fgets(STDIN));

echo 'API Password?'.PHP_EOL;
$data['API_PASS'] = trim(fgets(STDIN));

echo 'Database host? (Default: 127.0.0.1)'.PHP_EOL;
$data['DATABASE_HOST'] = trim(fgets(STDIN)) ? : '127.0.0.1';

echo 'Database username?'.PHP_EOL;
$data['DATABASE_USER'] = trim(fgets(STDIN));

echo 'Database password?'.PHP_EOL;
$data['DATABASE_PASS'] = trim(fgets(STDIN));

echo 'Dedicated server path?'.PHP_EOL;
$data['PATH_DEDICATED'] = trim(fgets(STDIN));

echo 'ManiaLive server path?'.PHP_EOL;
$data['PATH_MANIALIVE'] = trim(fgets(STDIN));

echo 'Your ManiaPlanet login?'.PHP_EOL;
$data['ADMIN_LOGIN'] = trim(fgets(STDIN));

$content = file_get_contents(__DIR__.'/config/app-template.ini');

foreach($data as $key => $value)
{
	$content = str_replace('%%'.$key.'%%', $value, $content);
}

file_put_contents($configFile, $content);

touch(__DIR__.'/.setup');

echo 'app.ini written'.PHP_EOL;

echo 'Creating mySQL database/tables...'.PHP_EOL;

shell_exec(sprintf("mysql -u%s -p%s -h %s < %s",
		$data['DATABASE_USER'],
		$data['DATABASE_PASS'],
		$data['DATABASE_HOST'],
		__DIR__.'/competition.sql'));


echo sprintf('You should be be able to access: %s',$data['APPLICATION_URL']).PHP_EOL;

?>