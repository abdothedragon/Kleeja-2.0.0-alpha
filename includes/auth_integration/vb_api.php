<?php
//
//auth integration remote vb with kleeja
//
//copyright 2007-2009 Kleeja.com ..
//license http://opensource.org/licenses/gpl-license.php GNU Public License
//$Author: phpfalcon $ , $Rev: 817 $,  $Date:: 2009-08-12 14:43:06 +0300#$
//

//no for directly open
if (!defined('IN_COMMON'))
{
	exit('no directly opening : ' . __file__);
}
  

function kleeja_auth_login ($name, $pass, $hashed = false, $expire, $loginadm = false, $return_username = false)
{
	global $script_path, $lang, $script_encoding, $script_api_key, $script_prefix, $config, $usrcp, $userinfo;
	
	//URL must be begin with http://
	if(empty($script_path) || $script_path[0] != 'h')
	{
		big_error('Forum URL must be begin with http://', sprintf($lang['SCRIPT_AUTH_PATH_WRONG'], 'Vbulletin'));
	}
	
	//api key is the key to make the query between the remote script and kleeja more secure !
	//this must be changed in the real use 
	if(empty($script_api_key))
	{
		big_error('Vbulletin api key', 'To connect to the remote Vbulletin you have to write the API key ...');
	}
	
	//if not utf8 and no iconv , i think it's fuckin bad situation
	if(!function_exists('iconv') && !preg_match('/utf/i',strtolower($script_encoding)))
 	{
 		big_error('No support for ICONV', 'You must enable the ICONV library to integrate kleeja with your forum. You can solve your problem by changing your forum db charset to UTF8.'); 
 	}
	
	//check for last slash
	if(isset($script_path[strlen($script_path)]) && $script_path[strlen($script_path)] == '/')
	{
		$script_path = substr($script_path, 0, strlen($script_path));
	}

	/*
		@content of file : kleeja_api.php
		
		if(!isset($_GET['api_key']) || (isset($_GET['api_key']) &&  $_GET['api_key'] != sha1('123456')))
		{
			return false;
		}
		
		//if return_username, no password just userid , and reutnr username
		if(isset($_GET['return_username']))
		{
			//1:ok
			//0:error
			echo base64_encode('1|username'));
			exit;
		}
		
		if(isset($_GET['userid']))
		{
			//hased query
		}
		else
		{
			//$_GET['username'];
		}
		
		//bla bla bla
		
		//1 : ok
		//0 : error
		echo base64_encode('1|userid|username|email|password|usergroup|...|...'));
		exit;
		
	*/
	
	$api_http_query = 'api_key=' . urlencode($script_api_key) . '&' . ($hashed ? 'userid' : 'username') . '=' . urlencode($name) . '&pass=' . urlencode($pass);
	//if only username, let tell him in the query
	$api_http_query . = $return_username ? '&return_username=1';
	
	//get it
	$remote_data = fetch_remote_file($script_path . '/kleeja_api.php?' . $api_http_query);

	//no responde
	//empty or can not connect
	if ($remote_data == false || empty($remote_data)) 
	{
		return false;
	}
			
	//see kleeja_api.php file
	//split the data , the first one is always 0 or 1 
	//0 : error
	//1: ok
	$user_info = explode('|', base64_decode($remote_data);
	
	//omg, it's 0 , 0 : error, lets die here
	if((int)$user_info[0] == 0)
	{
		return false;
	}
	
	//
	//if we want username only we have to return it quickly and die here
	//
	if($return_username)
	{
		return preg_match('/utf/i', strtolower($script_encoding)) ? $user_info[1] : iconv(strtoupper($script_encoding), "UTF-8//IGNORE", $user_info[1]));
	}
	
	//
	//when loggin to admin, we just want a check, no data setup ..
	//
	if(!$loginadm)
	{
		define('USER_ID', $user_info[1]);
		define('USER_NAME', preg_match('/utf/i', strtolower($script_encoding)) ? $user_info[2] : iconv(strtoupper($script_encoding), "UTF-8//IGNORE", $user_info[2]));
		define('USER_MAIL', $user_info[3]);
		define('USER_ADMIN',((int) $user_info[5] == 6) ? 1 : 0);
	}
	
	//user ifo
	//and this must be filled with user data comming from url
	$userinfo = array();
	
	
	//for cookirs
	$hash_key_expire = sha1(md5($config['h_key']) .  $expire);
	
	//add cookies
	if(!$loginadm)
	{
		$usrcp->kleeja_set_cookie('ulogu', $usrcp->en_de_crypt($user_info[1] . '|' . $user_info[4] . '|' . $expire . '|' . $hash_key_expire), $expire);
	}

	//no need after now
	unset($pass);

	//yes ! he is a real user
	return true;
}

//
//return username 
//
function kleeja_auth_username ($user_id)
{
	return kleeja_auth_login($user_id, false, false, false, false, true);
}	

//<-- EOF