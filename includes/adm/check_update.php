<?php
	//check_update
	//part of admin extensions
	//is there any new update !
	//kleeja.com
	
	// not for directly open
	if (!defined('IN_ADMIN'))
	{
		exit('no directly opening : ' . __file__);
	}

	
	//get data from kleeja database
	$b_data = fetch_remote_file('http://www.kleeja.com/check_vers/?i=' . urlencode($_SERVER['SERVER_NAME']) . '&v=' . KLEEJA_VERSION);

	if ($b_data === false)
	{
		$text	= $lang['ERROR_CHECK_VER'];
		$stylee	= "admin_err";
	}
	else
	{
		//
		// there is a file that we brought it !
		//
		
		$b_data = @explode('|', $b_data);
		
		$version_data = trim(htmlspecialchars($b_data[0]));
		
		if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '<'))
		{
			$text	= $lang['UPDATE_KLJ_NOW'];
			$stylee	= "admin_err";
		}
		else if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '='))
		{
			
			$text	= $lang['U_LAST_VER_KLJ'];
			$stylee	= "admin_info";
			
		}
		else if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '>'))
		{
			$text	= $lang['U_USE_PRE_RE'];
			$stylee	= "admin_info";
		}
		
		//lets recore it
		$v = unserialize($config['new_version']);
	
		//if(version_compare(strtolower($v['version_number']), strtolower($version_data), '<') || isset($_GET['show_msg']))
		//{
			
			//to prevent expected error [ infinit loop ]
			if(isset($_GET['show_msg']))
			{
				$query_get = array(
									'SELECT'	=> '*',
									'FROM'		=> "{$dbprefix}config",
									'WHERE'		=> "name = 'new_version'"
									);
				$result_get =  $SQL->build($query_get);
				if(!$SQL->num_rows($result_get))
				{
					$SQL->query("INSERT INTO `{$dbprefix}config` (`name` ,`value`)VALUES ('new_version', '')");
				}
			}
			
			$data	= array('version_number'	=> $version_data,
							'last_check'		=> time(),
							'msg_appeared'		=> isset($_GET['show_msg']) ? true : false,
							'copyrights'		=> strpos($b_data[1], 'yes') !== false ? true : false,
						);
			

			$data = serialize($data);
			
			$update_query = array(
									'UPDATE'	=> "{$dbprefix}config",
									'SET'		=> "value='"  . addslashes($data) . "'",
									'WHERE'		=> "name='new_version'"
									);

			if (!$SQL->build($update_query))
			{
				die($lang['CANT_UPDATE_SQL']);
			}
			
			//clean cache
			delete_cache('data_config');
			
			
			//then go back  to start
			if(isset($_GET['show_msg']))
			{
				header('location: ./admin.php');
			}
		//}	
	}

?>
