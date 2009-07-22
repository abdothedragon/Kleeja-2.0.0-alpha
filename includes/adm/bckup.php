<?php
	//bckup
	//part of admin extensions
	//get backup of tables
	//thanks for [b]coder[/b] from montadaphp.net  
	
	//copyright 2007-2009 Kleeja.com ..
	//license http://opensource.org/licenses/gpl-license.php GNU Public License
	//$Author$ , $Rev$,  $Date::                           $
	
	// not for directly open
	if (!defined('IN_ADMIN'))
	{
		exit('no directly opening : ' . __file__);
	}
	
	//for style ..
	$stylee 	= "admin_backup";
	$action 	= ADMIN_PATH . "?cp=bckup";


	$query	=	"SHOW TABLE STATUS";
	$result	=	$SQL->query($query);
	$i = 0;
	while($row=$SQL->fetch_array($result))
	{
		//make new lovely arrays !!
		$size[$row['Name']]	= round($row['Data_length']/1024, 2);
	}
	
	$SQL->freeresult($result);


		// to output our tables only !!
		$tables_sho		= (isset($tables_sho)  && is_array($tables_sho)) ? $tables_sho : array();
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}config",	'size' =>$size["{$dbprefix}config"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}files",		'size' =>$size["{$dbprefix}files"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}stats",		'size' =>$size["{$dbprefix}stats"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}users",		'size' =>$size["{$dbprefix}users"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}call",		'size' =>$size["{$dbprefix}call"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}exts",		'size' =>$size["{$dbprefix}exts"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}online",	'size' =>$size["{$dbprefix}online"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}reports",	'size' =>$size["{$dbprefix}reports"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}hooks",		'size' =>$size["{$dbprefix}hooks"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}plugins",	'size' =>$size["{$dbprefix}plugins"]);
		$tables_sho[]  	= array( 'name' =>"{$dbprefix}lang",	'size' =>$size["{$dbprefix}lang"]);


		//after submit ////////////////
		if (isset($_POST['submit']))
		{
			//variables
			$tables = $_POST['check'];
			$outta = "";
			
			//then
			foreach($tables as $table)
			{
				$query	=	"SHOW CREATE TABLE `" . $table . "`";
				
			    $result = $SQL->query($query); //get code of tables ceation
			    $que	= $SQL->fetch_array($result);
			    $outta .= $que['Create Table'] . "\r\n";//preivous code iside file
				
				$query2	=	"SELECT * FROM `$que[Table]`";
				
			    $result2 = $SQL->query($query2);// gets rows of table
				
				$fields	=	$values = array();
			    while($ro = $SQL->fetch_array($result2))
			    {
					$fields	=	$values	= array();
			        while($res = current($ro))
			        {
			            $fields[] = "`" . key($ro) . "`";
			            $values[] = "'$res'";
			            next($ro);
			        }
					
					if(is_array($fields))  $fields = implode(', ', $fields);
			        if(is_array($values))  $values = implode(', ', $values);
			        $q = "INSERT INTO `" . $que[Table] . "` ($fields) VALUES ($values);";
			        $outta .= $q . "\r\n";
					unset($fields);
					unset($values);
			    }

				$SQL->freeresult($result);
				$SQL->freeresult($result2);
			}
			
				//download now
			$sql_data = "#\n";
			$sql_data .= "# Kleeja Backup kleeja.com  kleeja version : " . KLEEJA_VERSION . ", DB version:" . $config['db_version'] . "\n";
			$sql_data .= "# DATE : " . gmdate("d-m-Y H:i:s", time()) . " GMT\n";
			$sql_data .= "#\n\n\n";
			
			$db_name_save = $dbname . '_kleeja.sql';
			@set_time_limit(0);
			header("Content-length: " . strlen($outta));
			header("Content-type: text/plain");
			header("Content-Disposition: attachment; filename=$db_name_save");
			echo $sql_data . $outta;
			$SQL->close();
			exit;
	}

?>
