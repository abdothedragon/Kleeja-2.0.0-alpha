<?php 
##################################################
#						Kleeja
#
# Filename : admin.php
# purpose :  control panel for administarator
# copyright 2007-2009 Kleeja.com ..
#license http://opensource.org/licenses/gpl-license.php GNU Public License
# last edit by : saanina
##################################################


	// security ..
	define ( 'IN_INDEX' , true);
	define ( 'IN_ADMIN' , true);
	
	//include imprtant file ..
	require ('includes/common.php');
	include_once ('includes/version.php');

	//path of admin extensions
	$path_adm	= "includes/adm";

	//exception extentions
	$ext_expt	= array();
	$ext_expt[]	= 'start';
	
	($hook = kleeja_run_hook('begin_admin_page')) ? eval($hook) : null; //run hook 

	//for security
	if (!$usrcp->admin())
	{
			($hook = kleeja_run_hook('user_not_admin_admin_page')) ? eval($hook) : null; //run hook 
			
			$text = '<span style="color:red;">' . $lang['U_NOT_ADMIN'] . '</span><br /><a href="ucp.php?go=login">' . $lang['LOGIN'] . '</a>';
			kleeja_err($text);
	}

	
	$SHOW_LIST = true; //fix bug

	$go_to	= htmlspecialchars($_GET['cp']);
	
	//get adm extensions
	$dh = @opendir($path_adm);
	while (($file = @readdir($dh)) !== false)
	{
		    if(strpos($file, '.php') !== false) // fixed
			{
				$adm_extensions[]	=  str_replace('.php', '', $file);
		    }
	}
	@closedir($dh);

	//no extensions ?
	if(!$adm_extensions || !is_array($adm_extensions))
	{
		big_error('No Extensions', 'ERROR IN LOADING ADMIN EXTENSIONS !');
	}
	
	//no requst or wrong !
	if(!$go_to || empty($go_to) ||  !in_array($go_to, $adm_extensions))
	{
		$go_to = 'start';
	}
	
	//get it 
	if (file_exists($path_adm . '/' . $go_to . '.php'))	
	{
		($hook = kleeja_run_hook("require_admin_page_begin_{$go_to}")) ? eval($hook) : null; //run hook 
		
		require_once ($path_adm . '/' . $go_to . '.php');
		
		($hook = kleeja_run_hook("require_admin_page_end_{$go_to}")) ? eval($hook) : null; //run hook 
	}
	else
	{
		 big_error('In Loading !', 'ERROR IN LOADING ADMIN EXTENSION ! -> [' . $go_to . ']');
	}
	
	
	//make array for menu 
	$adm_extensions_menu	=	array();
	
	//re oreder the items as alphapatic !
	sort($adm_extensions);
	$i = 0;
	
	foreach($adm_extensions as $m)
	{
		//some exceptions
		if(@in_array($m, $ext_expt)) continue;
		++$i;
		$adm_extensions_menu[$i]	= array('icon'	=> (file_exists($STYLE_PATH_ADMIN . 'images/' . $m . '_button.gif'))	? $STYLE_PATH_ADMIN . 'images/' . $m . '_button.gif' : $STYLE_PATH_ADMIN . 'images/no_icon_button.gif',
											'lang'	=>	($lang['R_'. strtoupper($m)]) ? $lang['R_'. strtoupper($m)]: (($lang[strtoupper($m)]) ? $lang[strtoupper($m)] : strtoupper($m)),
											'link'	=>	'admin.php?cp=' . $m,
											);
	
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
