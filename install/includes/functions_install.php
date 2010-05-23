<?php
/**
*
* @package install
* @version $Id: func_inst.php 1187 2009-10-18 23:10:13Z saanina $
* @copyright (c) 2007 Kleeja.com
* @license ./docs/license.txt
*
*/

/*
* Requirements of Kleeja
*/
define('MIN_PHP_VERSION', '4.3.0');
define('MIN_MYSQL_VERSION', '4.1.2');
//version of latest changes at db
define ('LAST_DB_VERSION' , '7');
//set no errors
define('MYSQL_NO_ERRORS', true);


// Detect choosing another lang while installing
if(isset($_GET['change_lang']))
{
	if (!empty($_POST['lang']))
	{
		header('Location: ' . $_SERVER['PHP_SELF'] . '?step=' . $_POST['step_is'] . '&lang=' . $_POST['lang']); 
	}
}

// Including current language
include ($_path . 'lang/' . getlang() . '/install.php');


/**
* Return current language of installing wizard 
*/
function getlang ($link = false)
{
	global $_path;

	if (isset($_GET['lang']))
	{
		$_GET['lang'] = empty($_GET['lang']) ? 'en' : preg_replace('/[^a-z0-9]/i', '', $_GET['lang']);

		$ln	= file_exists($_path . 'lang/' . $_GET['lang'] . '/install.php') ? $_GET['lang'] : 'en';
	}
	else
	{
		$ln	= 'en';
	}

	return $link ? 'lang=' . $ln : $ln;
}

function getjquerylink()
{
	global $_path;

	if(file_exists($_path . 'admin/admin_style/js/jquery.js'))
	{
		return $_path . 'admin/admin_style/js/jquery.js';
	}
	else
	{
		return 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js';
	}
}

/**
* Parsing installing templates
*/
function gettpl($tplname)
{
	global $lang, $_path;

	$tpl = preg_replace('/{{([^}]+)}}/', '<?php \\1 ?>', file_get_contents('style/' . $tplname));
	ob_start();
	eval('?> ' . $tpl . '<?php ');
	$stpl = ob_get_contents();
	ob_end_clean();
	
	return $stpl;
}

/**
* Export config 
*/
function do_config_export($type, $srv, $usr, $pass, $nm, $prf, $fpath = '')
{
		global $_path;
		
		if(!in_array($type, array('mysql', 'mysqli')))
		{
			$type = 'mysql';
		}
		
		$data	= '<?php'."\n\n" . '//fill those varaibles with your data' . "\n";
		$data	.= '$db_type		= \'' . $type . "'; //mysqli or mysql \n";
		$data	.= '$dbserver		= \'' . str_replace("'", "\'", $srv) . "'; //database server \n";
		$data	.= '$dbuser			= \'' . str_replace("'", "\'", $usr) . "' ; // database user \n";
		$data	.= '$dbpass			= \'' . str_replace("'", "\'", $pass) . "'; // database password \n";
		$data	.= '$dbname			= \'' . str_replace("'", "\'", $nm) . "'; // database name \n";
		$data	.= '$dbprefix		= \'' . str_replace("'", "\'", $prf) . "'; // if you use perfix for tables , fill it \n";
		//$data	.= '$adminpath		= \'admin.php\';// if you renamed your acp file , please fill the new name here \n';
		//$data	.= "\n\n\n";
		//$data	.= "//for integration with script  must change user systen from admin cp  \n";
		//$data	.= '$script_path	= \'' . str_replace("'", "\'", $fpath) . "'; // path of script (./forums)  \n";
		//$data	.= "\n\n";
		//$data	.= '?'.'>';
	
		$written = false;
		if (is_writable($_path))
		{
			$fh = @fopen($_path . 'config.php', 'wb');
			if ($fh)
			{
				fwrite($fh, $data);
				fclose($fh);

				$written = true;
			}
		}
		
		if(!$written)
		{
			header('Content-Type: text/x-delimtext; name="config.php"');
			header('Content-disposition: attachment; filename=config.php');
			echo $data;
			exit;
		}
		
		return true;
}	


/**
* Usefull to caluculte time of execution
*/
function get_microtime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float) $usec + (float) $sec);
}

/**
* Get config value from database directly, if not return false.
*/
function inst_get_config($name)
{
	global $SQL, $dbprefix;
	
	if(!is_resource($SQL))
	{
		global $dbserver, $dbuser, $dbpass, $dbname;
		if(!isset($dbserver))
		{
			return false;
		}
		$SQL = new SSQL($dbserver, $dbuser, $dbpass, $dbname);
	}
	
	$SQL->show_errors = false;
	$sql = "SELECT value FROM `{$dbprefix}config` WHERE `name` = '" . $name . "'";
	$result	= $SQL->query($sql);
	if($SQL->num_rows($result) == 0)
	{
		return false;
	}
	else
	{
		$current_ver  = $SQL->fetch_array($result);
		return $current_ver['value'];
	}
}

/**
* Update only plugins you have ! 
*/
function updating_exists_plugin($plugin_name)
{
	global $dbprefix, $SQL;

	$query = array(
					'SELECT'	=> 'plg_id',
					'FROM'		=> "{$dbprefix}plugins",
					'WHERE'		=> 'plg_name="' . $plugin_name . '"' 
					);

	$res = $SQL->build($query);
	if($SQL->num_rows($res))
	{
		return true;
	}
	return false;
}


/**
* trying to detect cookies settings
*/
function get_cookies_settings()
{
	$server_port = !empty($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : (int) @getenv('SERVER_PORT');
	$server_name = $server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : @getenv('SERVER_NAME'));
	
	// HTTP HOST can carry a port number...
	if (strpos($server_name, ':') !== false)
		$server_name = substr($server_name, 0, strpos($server_name, ':'));


	$cookie_secure	= isset($_SERVER['HTTPS'])  && $_SERVER['HTTPS'] == 'on' ? true : false;
	$cookie_name	= 'klj_' . strtolower(substr(str_replace('0', 'z', base_convert(md5(mt_rand()), 16, 35)), 0, 5));

	$name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$name)
		$name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : @getenv('REQUEST_URI');

	$script_path = trim(dirname(str_replace(array('\\', '//'), '/', $name)));

	
	if ($script_path !== '/')
	{
		if (substr($script_path, -1) == '/')
			$script_path = substr($script_path, 0, -1);

		$script_path = str_replace(array('../', './'), '', $script_path);
		if ($script_path[0] != '/')
			$script_path = '/' . $script_path;
	}
	
	$cookie_domain = $server_name;
	if (strpos($cookie_domain, 'www.') === 0)
	{
		$cookie_domain = str_replace('www.', '.', $cookie_domain);
	}

	return array(
		'server_name'	=> $server_name,
		'cookie_secure'	=> $cookie_secure,
		'cookie_name'	=> $cookie_name,
		'cookie_domain'	=> $cookie_domain,
		'cookie_path'	=> str_replace('/install', '', $script_path),
	);
	
}