<?php
//
//auth integration mysmbb with kleeja
//


//no for directly open
if (!defined('IN_COMMON'))
{
	exit('no directly opening : ' . __file__);
}
  

function kleeja_auth_login ($name, $pass)
{
	global $script_path, $lang, $script_encoding, $script_srv, $script_db, $script_user, $script_pass, $script_prefix;
	
	if(isset($script_path)) {
	//check for last slash / 
	if($script_path[strlen($script_path)] == '/')
	{
		$script_path = substr($script_path, 0, strlen($script_path));
	}

	$script_path = ($script_path[0] == '/' ? '..' : '../') .  $script_path;
	
	
	//get database data from mysmartbb config file
	if(file_exists($script_path . '/engine/config.php')) 
	{
		require ($script_path . '/engine/config.php');
		$forum_srv	= $config['db']['server'];
		$forum_db	= $config['db']['name'];
		$forum_user	= $config['db']['username'];
		$forum_pass	= $config['db']['password'];
		$forum_prefix = $config['db']['prefix'];
	} 
	else
	{
		big_error('Forum path is not correct', sprintf($lang['SCRIPT_AUTH_PATH_WRONG'], 'MySmartBB'));
	}
	}
	else
	{
		$forum_srv	= $script_srv;
		$forum_db	= $script_db;
		$forum_user	= $script_user;
		$forum_pass	= $script_pass;
		$forum_prefix = $script_prefix;
	}
	
	if(empty($forum_srv) || empty($forum_user) || empty($forum_db))
	{
		return;
	}
	
	$SQLMS	= new SSQL($forum_srv, $forum_user, $forum_pass, $forum_db, TRUE);
	//$charset_db = @mysql_client_encoding($SQLMS->connect_id);
	unset($forum_pass); // We do not need this any longe
	
	if(!function_exists('iconv') && strpos(strtolower($script_encoding), 'utf') === false)
 	{
 		big_error('No support for ICONV', 'You must enable the ICONV library to integrate kleeja with your forum. You can solve your problem by changing your forum db charset to UTF8.'); 
 	}
 	
	$query = array('SELECT'	=> '*',
					'FROM'	=> "`{$forum_prefix}member`",
					'WHERE'	=> "username='" . $SQLMS->real_escape($name) . "' AND password='" . md5($pass) . "'"
					);

	($hook = kleeja_run_hook('qr_select_usrdata_mysbb_usr_class')) ? eval($hook) : null; //run hook	
	$result = $SQLMS->build($query);
	

	if ($SQLMS->num_rows($result) != 0) 
	{
	
		while($row=$SQLMS->fetch_array($result))
		{
			$_SESSION['USER_ID']	= $row['id'];
			$_SESSION['USER_NAME']	= (strpos(strtolower($script_encoding), 'utf') == true) ? $row['username'] : iconv(strtoupper($script_encoding),"UTF-8//IGNORE",$row['username']);
			$_SESSION['USER_MAIL']	= $row['email'];
			$_SESSION['USER_ADMIN']	= ($row['usergroup'] == 1) ? 1 : 0;
			$_SESSION['USER_SESS']	= session_id();
			($hook = kleeja_run_hook('qr_while_usrdata_mysbb_usr_class')) ? eval($hook) : null; //run hook
			
		}
		
		$SQLMS->freeresult($result);   
		unset($pass);
		$SQLMS->close();
		
		
		return true;
	}
	else
	{
		$SQLMS->close();
		return false;
	}
}	

function kleeja_auth_username ($user_id)
{
	global $script_path, $lang, $script_encoding, $script_srv, $script_db, $script_user, $script_pass, $script_prefix;
	
	if(isset($script_path)) {
	//check for last slash / 
	if($script_path[strlen($script_path)] == '/')
	{
		$script_path = substr($script_path, 0, strlen($script_path));
	}

	$script_path = ($script_path[0] == '/' ? '..' : '../') .  $script_path;
	
	
	//get database data from mysmartbb config file
	if(file_exists($script_path . '/engine/config.php')) 
	{
		require ($script_path . '/engine/config.php');
		$forum_srv	= $config['db']['server'];
		$forum_db	= $config['db']['name'];
		$forum_user	= $config['db']['username'];
		$forum_pass	= $config['db']['password'];
		$forum_prefix = $config['db']['prefix'];
	} 
	else
	{
		big_error('Forum path is not correct', sprintf($lang['SCRIPT_AUTH_PATH_WRONG'], 'MySmartBB'));
	}
	}
	else
	{
		$forum_srv	= $script_srv;
		$forum_db	= $script_db;
		$forum_user	= $script_user;
		$forum_pass	= $script_pass;
		$forum_prefix = $script_prefix;
	}
	
	if(empty($forum_srv) || empty($forum_user) || empty($forum_db))
	{
		return;
	}
	
	$SQLMS	= new SSQL($forum_srv, $forum_user, $forum_pass, $forum_db, TRUE);
	//$charset_db = @mysql_client_encoding($SQLMS->connect_id);
	unset($forum_pass); // We do not need this any longe

	if(!function_exists('iconv') && strpos(strtolower($script_encoding), 'utf') === false)
 	{
 		big_error('No support for ICONV', 'You must enable the ICONV library to integrate kleeja with your forum. You can solve your problem by changing your forum db charset to UTF8.'); 
 	}

	$query_name = array(
					'SELECT'	=> 'username',
					'FROM'		=> "`{$forum_prefix}member`",
					'WHERE'		=> "id='" . intval($user_id) . "'"
				);
			
	($hook = kleeja_run_hook('qr_select_usrname_ms_usr_class')) ? eval($hook) : null; //run hook				
	$result_name = $SQLVB->build($query_name);
				
	if ($SQLMS->num_rows($result_name) > 0) 
	{
		while($row = $SQLMS->fetch_array($result_name))
		{
			$returnname = (strpos(strtolower($script_encoding), 'utf') == true) ? $row['username'] : iconv(strtoupper($script_encoding),"UTF-8//IGNORE",$row['username']);

		}#whil1
		$SQLMS->freeresult($result_name); 
		$SQLMS->close();
		return $returnname;
	}
	else
	{
		$SQLMS->close();
		return false;
	}
}	
	
?>