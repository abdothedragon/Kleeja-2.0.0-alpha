<?php
##################################################
#						Kleeja 
#
# Filename : common.php 
# purpose :  all things came from here ..:
# copyright 2008-2009 Kleeja.com ..
# license http://opensource.org/licenses/gpl-license.php GNU Public License
# $Author$ , $Rev$,  $Date::                           $
##################################################

		
	// not for directly open
	if (!defined('IN_INDEX'))
	{
		exit('no directly opening : ' . __file__);
	}

	//we are in the common file 
	define ('IN_COMMON', true);
	
	//
	//development stage;  developers stage
	//
	define('DEV_STAGE', true);
		 
		
	// Report all errors, except notices
	defined('DEV_STAGE') ? @error_reporting(E_ALL) : @error_reporting(E_ALL ^ E_NOTICE);
	//Just to check
	define('IN_PHP6', (version_compare(PHP_VERSION, '6.0.0-dev', '>=') ? true : false));
		
	//get admin path from config.php
	$adminpath = isset($adminpath) ? $adminpath : './admin/index.php';
	
	//admin path
	define('ADMIN_PATH', $adminpath);
		
	// start session
	$s_time = 86400 * 2; // 2 : two days 
	$s_key = (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
	$s_sid = 'klj_' . substr('_' . md5($s_key), 0, 8);
	@ini_set('session.use_only_cookies', false);
	@ini_set('session.auto_start', false);
	//this will help people with some problem with their sessions path
	//session_save_path('./cache/');
	if(defined('IN_ADMIN'))
	{
		//admin session timeout
		$admintime = isset($admintime) ? $admintime : 18000;
		//session_set_cookie_params($admintime);
		if (function_exists('session_set_cookie_params'))
		{
    		session_set_cookie_params($admintime, basename(ADMIN_PATH));
  		} 
		elseif (function_exists('ini_set'))
		{
    		ini_set('session.cookie_lifetime', $admintime);
    		ini_set('session.cookie_path', basename(ADMIN_PATH));
  		}


	}
	
	session_name($s_sid);
	@session_start();


	function stripslashes_our(&$value)
	{
		return is_array($value) ? array_map('stripslashes_our', $value) : stripslashes($value);  
	} 
	//unsets all global variables set from a superglobal array
	function unregister_globals() 
	{ 
		foreach (func_get_args() as $name)
		{
			foreach ($GLOBALS[$name] as $key=>$value)
			{
				if (isset($GLOBALS[$key]))
				{
					unset($GLOBALS[$key]);
				}
			}
		}
	}

	if (@ini_get('register_globals'))
	{
		unregister_globals('_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'); 
	}
	
	if( (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || 
		(@ini_get('magic_quotes_sybase') && (strtolower(@ini_get('magic_quotes_sybase')) != "off")) )
	{ 
		stripslashes_our($_GET); 
		stripslashes_our($_POST); 
		stripslashes_our($_COOKIE); 
	}
		
	
	//time of start and end and wutever
	function get_microtime()
	{
		list($usec, $sec) = explode(' ', microtime());	return ((float)$usec + (float)$sec);
	}
	
	$starttm = get_microtime();
	
	//path 
	if(!defined('PATH'))
	{
		define('PATH', './');
	}
	 
	// no config
	if (!file_exists(PATH . 'config.php'))
	{
		header('Location: ' . PATH . 'install/index.php');
		exit;
	}
	
	// there is a config
	require (PATH . 'config.php');

	//no enough data
	if (!$dbname || !$dbuser)
	{
		header('Location: ' . PATH . 'install/index.php');
		exit;
	}
	
	//include files .. & classes ..
	$path = dirname(__file__) . '/';
	$root_path = PATH;
	$db_type = isset($db_type) ? $db_type : 'mysql';
	include_once ($path . 'version.php');
	switch ($db_type)
	{
		case 'mysqli':
			require ($path . 'mysqli.php');
		break;
		default:
			require ($path . 'mysql.php');
	}
	require ($path . 'style.php');
	require ($path . 'KljUploader.php');
	require ($path . 'usr.php');
	require ($path . 'pager.php');
	require ($path . 'functions.php');
	require ($path . 'functions_display.php');

	//. install.php exists
	if (file_exists(PATH . 'install') && !defined('IN_ADMIN') && !defined('IN_LOGIN') && !defined('DEV_STAGE'))
	{
		big_error('install folder exists!', '<b>Install</b> folder detected! please delete it OR install <b>Kleeja</b> if you haven\'t done so yet...<br/><br/><a href="' . PATH . 'install">Click to Install</a><br/><br/>');
	}
	
	//fix intregation problems
	if(empty($script_encoding))
	{
		define('DISABLE_INTR', true);
	}
	
	// start classes ..
	$SQL	= new SSQL($dbserver, $dbuser, $dbpass, $dbname);
	//no need after now 
	unset($dbpass);
	$tpl	= new kleeja_style;
	$kljup	= new KljUploader;
	$usrcp	= new usrcp;

	//then get caches
	require ($path . 'cache.php');
	
	if(!defined('IN_LOGIN_POST') && !defined('IN_ADMIN_LOGIN_POST'))
	{
		//check user or guest
		$usrcp->kleeja_check_user();
	}
				
	//no tpl caching in dev stage  
	if(defined('DEV_STAGE'))
	{
		$tpl->caching = false;
	}
	
	//check if admin (true/false)
	$is_admin = $usrcp->admin();
	
	//kleeja session id
	$klj_session = $SQL->escape(session_id());

	// for gzip : php.net
	//fix bug # 181
	//we stopped this in development stage cuz it's will hide notices
	$do_gzip_compress = false; 
	if ($config['gzip'] == '1' && !defined('IN_DOWNLOAD') && !defined('IN_ADMIN') && !defined('DEV_STAGE')) 
	{
	    function compress_output($output)
		{
			return gzencode($output, 5, FORCE_GZIP);
		}
		
	    // Check if the browser supports gzip encoding, HTTP_ACCEPT_ENCODING
	    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false && !headers_sent() && @extension_loaded('zlib'))
		{
			$do_gzip_compress = true; 
	        // Start output buffering, and register compress_output()
			if(function_exists('gzencode') )
	        {
				@ob_start("compress_output");
	        }
			else
			{
				@ob_start();
			}
			
			// Tell the browser the content is compressed with gzip
	        header("Content-Encoding: gzip");
	    }
	}

	// ...header ..  i like it ;)
	header('Content-type: text/html; charset=UTF-8');	
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');	
	
	//check lang
	if(!$config['language'] || empty($config['language']))
	{
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) > 2)
		{
			$config['language'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			if(!file_exists(PATH . 'lang/' . $config['language'] . '/common.php'))
			{
				$config['language'] = 'en';
			}
		}
	}
	
	//check style
	if(!$config['style'] || empty($config['style']))
	{
		$config['style'] = 'default';
	}
	
	//check h_kay, important for kleeja
	if(empty($config['h_key']))
	{
		$h_k = sha1(microtime() . rand(1000,9999));
		if(!update_config('h_key', $h_k))
		{
			add_config('h_key', $h_k);
		}
	}
	
	$STYLE_PATH = PATH . 'styles/' . $config['style'] . '/';
	$STYLE_PATH_ADMIN  = PATH . 'admin/admin_style/';
	
	//get languge of common
	get_lang('common');
	
	//ban system 
	get_ban();
	
	//some languages have copyrights !
	$S_TRANSLATED_BY = false;
	if(isset($lang['S_TRANSLATED_BY']) && strlen($lang['S_TRANSLATED_BY']) > 2)
	{
		$S_TRANSLATED_BY = true;
	}
	
	//site close ..
	$login_page = '';
	if ($config['siteclose'] == '1' && !$usrcp->admin() && !defined('IN_LOGIN') && !defined('IN_ADMIN'))
	{
		// Send a 503 HTTP response code to prevent search bots from indexing the maintenace message
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		kleeja_info($config['closemsg'], $lang['SITE_CLOSED']);
	}
	
	//exceed total size 
	if (($stat_sizes >= ($config['total_size'] *(1048576))) && !defined('IN_LOGIN') && !defined('IN_ADMIN'))// convert megabytes to bytes
	{ 
		// Send a 503 HTTP response code to prevent search bots from indexing the maintenace message
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		kleeja_info($lang['SIZES_EXCCEDED'], $lang['STOP_FOR_SIZE']);
	}
	
	//calculate  onlines ...  
	if ($config['allow_online'] == '1')
	{
		KleejaOnline();
	}
	
	// claculate for counter ..
	 // of course , its not printable function , its just for calculating :)
	//visit_stats();
	
	//check for page numbr
	if(empty($perpage) || intval($perpage) == 0)
	{
		$perpage = 10;
	}
	
	//site url must end with /
	if($config['siteurl'])
	{
		$config['siteurl'] = ($config['siteurl'][strlen($config['siteurl'])-1] != '/') ? $config['siteurl'] . '/' : $config['siteurl'];
	}
	
	//captch file 
	$captcha_file_path = $config['siteurl'] . 'includes/captcha.php';
	
	
	//clean files
	if((int) $config['del_f_day'] > 0)
	{
		klj_clean_old_files($config['klj_clean_files_from']);
	}
	

	($hook = kleeja_run_hook('end_common')) ? eval($hook) : null; //run hook

#<-- EOF
