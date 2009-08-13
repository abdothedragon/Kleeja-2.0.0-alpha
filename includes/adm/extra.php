<?php
	//extra
	//part of admin extensions
	//conrtoll extra heaer and footer
	
	//copyright 2007-2009 Kleeja.com ..
	//license http://opensource.org/licenses/gpl-license.php GNU Public License
	//$Author$ , $Rev$,  $Date::                           $
	
	// not for directly open
	if (!defined('IN_ADMIN'))
	{
		exit('no directly opening : ' . __file__);
	}
	


		//for style ..
		$stylee 	= "admin_extra";
		$action 	= basename(ADMIN_PATH) . "?cp=extra";
		
		$query = array(
					'SELECT'	=> 'ex_header,ex_footer',
					'FROM'		=> "{$dbprefix}stats"
					);
						
		$result = $SQL->build($query);
		
		//is there any change !
		$AFFECTED = false;
		
		while($row=$SQL->fetch_array($result))
		{
			$ex_headere = isset($_POST["ex_header"]) ? $_POST['ex_header'] : $row['ex_header'];
			$ex_footere = isset($_POST["ex_footer"]) ? $_POST['ex_footer'] : $row['ex_footer'];
			
			$ex_header = htmlspecialchars($ex_headere);
			$ex_footer = htmlspecialchars($ex_footere);
				
			//when submit !!
			if (isset($_POST['submit']))
			{
				//update
				$update_query = array(
									'UPDATE'	=> "{$dbprefix}stats",
									'SET'		=> "ex_header = '" . $SQL->escape($ex_headere) . "', ex_footer = '" . $SQL->escape($ex_footere) . "'"
								);

				$SQL->build($update_query);
				
				if($SQL->affected())
				{
					$AFFECTED = true;
					//delete cache ..
					delete_cache('data_extra');
				}
			}
		}
		$SQL->freeresult($result);


		//after submit 
		if (isset($_POST['submit']))
		{
			$text	= $AFFECTED ? $lang['EXTRA_UPDATED'] : $lang['NO_UP_CHANGE_S'];
			$stylee	= "admin_info";
		}
		
#<--- EOF