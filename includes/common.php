<?
##################################################
#						Kleeja 
#
# Filename : common.php 
# purpose :  all things came from here ..:
# copyright 2007 Kleeja.com ..
#
##################################################

	// start session
	session_start();
	
	// for security ..
	  if (!defined('IN_INDEX'))
	  {
	  echo '<strong><br /><span style="color:red">[NOTE]: This Is Dangrous Place !! [2007 saanina@gmail.com]</span></strong>';
	  exit();
	  }
	 define ( 'IN_COMMON' , true);
	 
	 
// Report all errors, except notices
error_reporting(E_ALL ^ E_NOTICE);

$starttm = explode(" ", microtime());
$starttm = $starttm[1] + $starttm[0];



    // support for php older than 4.1.0 
    if ( phpversion() < '4.1.0' ){
        $_GET 			= $HTTP_GET_VARS;
        $_POST 			= $HTTP_POST_VARS;
        $_COOKIE 		= $HTTP_COOKIE_VARS;
        $_SESSION		= $HTTP_SESSION_VARS;
        $_SERVER 		= $HTTP_SERVER_VARS;
		$_ENV 			= $HTTP_ENV_VARS;
		$_FILES 		= $HTTP_POST_FILES;
     }
	  

	  
	//include files .. & classes ..
	$path = dirname(__FILE__).'/';

	include ($path.'config.php');
	include ($path.'easytemplate.php');
	include ($path.'mysql.php');
	include ($path.'js.php');
	include ($path.'KljUploader.php');
	include ($path.'usr.php');
	include ($path.'pager.php');


	//no data.. install.php exists
	if (!$dbname || !$dbuser)
	{
	print '<span style="color:red;">CHANGE DATA IN config.php OR INSTALL FROM <a href="./install.php">INSTALL NOW</a></span>';
	exit();
	}
	elseif ( file_exists('./install.php') ) 
	{
	 echo '<span style="color:red;"> Install.php detected! please delete it OR install kleeja if you haven\'t done so yet...
			<br/><a href="./install.php">Click to Install</a></apan>';
	 exit();
	}
	
	// start classes ..
	$SQL	= new SSQL($dbserver,$dbuser,$dbpass,$dbname);				# Author : MaaSTaaR & saanina
	$tpl	= new EasyTemplate;		# Author : daif
	$kljup	= new KljUploader;		# Author : Nadorino and saanina
	$usrcp	= new usrcp;			# Author : saanina
	

	unset($dbpass); // We do not need this any longer, unset for safety purposes

	//get caches .. 
	require ($path.'cache.php');
	
	//get language .. 
	if (!$config[language]) { $config[language] = "ar"; }
	include ('language/'.$config[language].'.php' );
	
	// for gzip
	$do_gzip_compress = FALSE; 
	if ( $config[gzip] ) 
	{ 
	   $phpver = phpversion(); 

	   $useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT; 

	   if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) ) 
	   { 
	      if ( extension_loaded('zlib') ) 
	      { 
	         ob_start('ob_gzhandler'); 
	      } 
	   } 
	   else if ( $phpver > '4.0' ) 
	   { 
	      if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') ) 
	      { 
	         if ( extension_loaded('zlib') ) 
	         { 
	            $do_gzip_compress = TRUE; 
	            ob_start(); 
	            ob_implicit_flush(0); 

	            header('Content-Encoding: gzip'); 
	         } 
	      } 
	   } 
	} ## end gzip 
	
	
	# specify folder of style
	$tpl->Temp = "styles/".$config['style'];
	$tpl->Cache = "cache";
	$stylepath = $tpl->Temp;
	
	
	// ...header ..  i like it ;)
	header('Content-type: text/html; charset=UTF-8');
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');	
	
	//ban system 
	get_ban();
	
	
	//anti floods  system
	//if ( $usrcp->admin() === false ){
	//antifloods(7, 700); // (number of floods, per seconds ) 
	//}
	
	//site close ..
	if ($config[siteclose] == 1 && !$usrcp->admin() ) {
		Saaheader($lang['SITE_CLOSED']);
		$text = $config[closemsg];
		print $tpl->display("info.html");
		Saafooter();
		exit();
	}
	
	//exceed total size 
	if ($stat_sizes >= ($config[total_size] *(1048576))) { // convert megabytes to bytes
		Saaheader($lang['STOP_FOR_SIZE']);
		$text = $lang['SIZES_EXCCEDED'];
		print $tpl->display("info.html");
		Saafooter();
		exit();
	}
	
	//calculate  onlines ...  
	if ($config[allow_online] == 1 ){
		KleejaOnline();
	}
	
	// claculate for counter ..
	visit_stats(); // of course , its not printable function , its just for calculate :)
	
	
?>