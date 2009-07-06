<?php 
##################################################
#						Kleeja
#
# Filename : admin.php
# purpose :  control panel for administarator
# copyright 2007-2009 Kleeja.com ..
# license http://opensource.org/licenses/gpl-license.php GNU Public License
# $Author$ , $Rev$,  $Date::                           $
##################################################


	// security ..
	define ('IN_INDEX' , true);
	define ('IN_ADMIN' , true);
	
	if(isset($_GET['go']) && $_GET['go'] == 'login')
	{
		define('IN_ADMIN_LOGIN', true);
	}
	
	//include imprtant file ..
	include ('includes/common.php');
	include_once ('includes/version.php');

	//go to ..
	$go_to	= isset($_GET['cp']) ? htmlspecialchars($_GET['cp']) : 'start';
	$username = $usrcp->name();
	
	//need to login again
	if(empty($_SESSION['ADMINLOGIN']) or $_SESSION['ADMINLOGIN'] != '1') 
	{
		if(isset($_GET['go']) && $_GET['go'] == 'login') 
		{			
			
			if (isset($_POST['submit']))
			{
				//for onlines
				$ip		=  $SQL->escape($_SERVER['REMOTE_ADDR']);
					
				if ($config['allow_online'] == 1)
				{
					$query_del	= array(	'DELETE'	=> "{$dbprefix}online",
											'WHERE'		=> "ip='" . $ip . "'"
										);
						
					$SQL->build($query_del);
				}

				//login
				$ERRORS	=	'';
				if (empty($_POST['lname']) || empty($_POST['lpass']))
				{
					$ERRORS[] = $lang['EMPTY_FIELDS'];
				}
				elseif(!$usrcp->data($_POST['lname'], $_POST['lpass']))
				{
					$ERRORS[] = $lang['LOGIN_ERROR'];
				}
					
				
				if(empty($ERRORS))
				{
					$_SESSION['ADMINLOGIN'] = '1';
					header('Location: admin.php?cp=' . $go_to);
					$SQL->close();
					exit;
				}
				else
				{
					$errs =	'';
					foreach($ERRORS as $r)
					{
						$errs .= '- ' . $r . '. <br />';
					}
					//kleeja_admin_err($errs,false);
				}
			}
		}
			//show template login .
			//body
			$action	= "admin.php?go=login&amp;cp=" . $go_to;
			$err = false;
			if(!empty($errs))
			{
				$err = true;
			}
			
			if($config['user_system'] != '1' && isset($script_encoding) && function_exists('iconv') && !eregi('utf',strtolower($script_encoding)) && !defined('DISABLE_INTR'))
			{
				//send custom chaeset header
				header("Content-type: text/html; charset={$script_encoding}");
				//change login page encoding if kleeja is integrated with other script
				echo iconv("UTF-8", strtoupper($script_encoding) . "//IGNORE", $tpl->display("admin_login"));	

			}
			else
			{
				echo $tpl->display("admin_login");
			}
			
		$SQL->close();
		exit;	//stop	
	}


	//path of admin extensions
	$path_adm	= "includes/adm";

	//exception extentions
	$ext_expt	= array();
	$ext_expt[]	= 'start';
	//confirm msgs
	$ext_confirm	= array();
	$ext_confirm[]	= 'repair';	
	$ext_confirm[]	= 'lgoutcp';	
	
	($hook = kleeja_run_hook('begin_admin_page')) ? eval($hook) : null; //run hook 

	//for security
	if (!$usrcp->admin())
	{
		($hook = kleeja_run_hook('user_not_admin_admin_page')) ? eval($hook) : null; //run hook 
			
		$text = '<span style="color:red;">' . $lang['U_NOT_ADMIN'] . '</span><br /><a href="ucp.php?go=login&amp;return=' . str_replace(array('?', '/', '='), array('ooklj1oo', 'ooklj2oo', 'ooklj3oo'), kleeja_get_page()) . '">' . $lang['LOGIN'] . '</a>';
		kleeja_err($text);
	}
	
	
	$SHOW_LIST = true; //fix bug

	
	//get adm extensions
	$dh = @opendir($path_adm);
	while (($file = @readdir($dh)) !== false)
	{
		if(strpos($file, '.php') !== false) // fixed
		{
			$adm_extensions[] = str_replace('.php', '', $file);
		}
	}
	
	@closedir($dh);

	//no extensions ?
	if(!$adm_extensions || !is_array($adm_extensions))
	{
		big_error('No Extensions', 'ERROR IN LOADING ADMIN EXTENSIONS !');
	}
	
	
	//exception of 406 ! dirty hosting
	if($go_to == 'options')
	{
		$go_to = 'configs';
	}
	
	//no requst or wrong !
	if(!$go_to || empty($go_to) ||  !in_array($go_to, $adm_extensions))
	{
		$go_to = 'start';
	}
		
	//make array for menu 
	$adm_extensions_menu	=	array();
	
	//re oreder the items as alphapatic !
	sort($adm_extensions);
	$i = 0;
	
	//New calls notice
	$query = array('SELECT'	=> 'id',
					'FROM'		=> "{$dbprefix}call",
					'WHERE'		=> 'time > "' . (!empty($_SESSION['LAST_VISIT']) ? $_SESSION['LAST_VISIT'] : time() - 3600*12) . '"' 
					);
		$newcall = $SQL->num_rows($SQL->build($query));
		($newcall == 0) ? $newcall = '[0]' : $newcall = '[' . $newcall . ']'; 
	
	//New reports notice
	$query = array('SELECT'	=> 'id',
					'FROM'		=> "{$dbprefix}reports",
					'WHERE'		=> 'time > "' . (!empty($_SESSION['LAST_VISIT']) ? $_SESSION['LAST_VISIT'] : time() - 3600*12) . '"' 
					);
	$newreport = $SQL->num_rows($SQL->build($query));
	($newreport == 0) ? $newreport = '[0]' : $newreport = '[' . $newreport . ']'; 
		
	foreach($adm_extensions as $m)
	{
		//some exceptions
		if(@in_array($m, $ext_expt))
		{
			continue;
		}
		
		++$i;
		$adm_extensions_menu[$i]	= array('icon'	=> (file_exists($STYLE_PATH_ADMIN . 'images/' . ($m == 'configs' ? 'options' : $m) . '_button.gif'))	? $STYLE_PATH_ADMIN . 'images/' . ($m == 'configs' ? 'options' : $m) . '_button.gif' : $STYLE_PATH_ADMIN . 'images/no_icon_button.gif',
											'lang'	=> !empty($lang['R_'. strtoupper($m)]) ? $lang['R_'. strtoupper($m)] . (($m == 'calls') ? $newcall : '') . (($m == 'reports') ? $newreport : '') : (!empty($lang[strtoupper($m)]) ? $lang[strtoupper($m)] :  (!empty($olang[strtoupper($m)]) ? $olang[strtoupper($m)] : strtoupper($m))),
											'link'	=> 'admin.php?cp=' . ($m == 'configs' ? 'options' : $m),
											'confirm'	=> (@in_array($m, $ext_confirm)) ? true : false,
											'current'	=> ($m == $go_to) ? true : false
											);
											
		($hook = kleeja_run_hook('foreach_ext_admin_page')) ? eval($hook) : null; //run hook 
	
	}
	
	
	//get it 
	if (file_exists($path_adm . '/' . $go_to . '.php'))	
	{
		($hook = kleeja_run_hook("require_admin_page_begin_{$go_to}")) ? eval($hook) : null; //run hook 
		include_once ($path_adm . '/' . $go_to . '.php');
		($hook = kleeja_run_hook("require_admin_page_end_{$go_to}")) ? eval($hook) : null; //run hook 
	}
	else
	{
		big_error('In Loading !', 'ERROR IN LOADING ADMIN EXTENSION ! -> [' . $go_to . ']');
	}
	
	($hook = kleeja_run_hook('end_admin_page')) ? eval($hook) : null; //run hook 
	

	//show style .
	//header
	echo $tpl->display("admin_header");
		//body
		echo $tpl->display($stylee);
	//footer
	echo $tpl->display("admin_footer");
	
	
	
	//close db
	$SQL->close();
	exit;
?>
