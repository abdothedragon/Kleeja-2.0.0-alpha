<?php
	
  	$v = @unserialize($config['new_version']);
	if(version_compare(strtolower(KLEEJA_VERSION), strtolower($v['version_number']), '<'))
	{
		$data 		= fetch_remote_file('http://www.kleeja.com/check_vers/kleeja_versions.txt', false);
		$dataa 		= explode(strtolower(KLEEJA_VERSION), $data);
		$dataa 		= explode('|', $dataa[2]);
		$new_ver 	= $dataa[1];
		
		if(!isset($_GET['astep']))
		{
			$_GET['astep'] = null;	
		}
		
		switch($_GET['astep'])
		{
			case '1': //download
			
				if(function_exists("curl_init"))
				{
					// www.kleeja.com/aupdatekleeja.zip
					$data = fetch_remote_file('http://www.kleeja.com/check_vers/aupdatekleeja' . $new_ver . '.tar');
					if($data != false)
					{
						//then ..write new file
						$fp2 = @fopen(PATH . $config['foldername'] . '/' . 'aupdatekleeja.tar', 'w');
						@fwrite($fp2, $data);
						@fclose($fp2);
						kleeja_admin_info('OK');

					}					
					else
					{
						kleeja_admin_err($lang['URL_CANT_GET']);
					}
				}
				else //OTHER FUNCTION
				{
					$data = fetch_remote_file('http://www.kleeja.com/check_vers/aupdatekleeja' . $new_ver . '.tar' , PATH . $config['foldername'] . '/' . 'aupdatekleeja.tar');
						
					if($data === false)
					{
						kleeja_admin_err($lang['URL_CANT_GET']);		
					}
					else
					{
						kleeja_admin_info('OK');
					}
				}
								
			break;
			
			case '2' : //extract
			
				include(PATH . 'includes/extract.php');
				
				$zip = new Archive_Tar_Ex(PATH . $config['foldername'] . '/' . 'aupdatekleeja.tar');
				$extractedFileList = $zip->extract(PATH);
				
				if($extractedFileList)
				{
					/*
					$filesextracted = '<div style="text-align: left;">Replaced Files:<br />';
					
					//print_r($extractedFileList);
					foreach($extractedFileList as $key => $value)
					{
						$filesextracted .= $value['filename'] . '   ... <strong><em>Replaced</em></strong><br />';
					}
					
					$filesextracted .= '</div>';
					*/
					
					kleeja_admin_info(/**$filesextracted**/'OK');
				}
				else
				{
					kleeja_admin_err('error while extracting');
				}
				
			break;
			
			case '3' :
				
				//get data from kleeja database
				$data = fetch_remote_file('http://www.kleeja.com/check_vers/update-sqls-' . $new_ver . '.txt', false);

				if ($data === false)
				{
					$text	= $lang['ERROR_CHECK_VER'];
					kleeja_admin_err($text);
				}
				
				eval($data);
				
				//$complete_upate = true;
			
				if($config['db_version'] >= DB_VERSION && !defined('DEV_STAGE'))
				{
					kleeja_admin_info('<br /><br /><span style="color:green;">' . $lang['INST_UPDATE_CUR_VER_IS_UP']. '</span><br />');
					//$complete_upate = false;
				}
				
				$msg = '';
				
				//
				//is there any sqls 
				//
				/*
				if(($complete_upate or defined('DEV_STAGE')) && !defined('C_U_F'))
				{
				*/
					$SQL->show_errors = false;
					if(isset($update_sqls) && sizeof($update_sqls) > 0)
					{
						$err = '';
						foreach($update_sqls as $name=>$sql_content)
						{
							$err = '';
							$SQL->query($sql_content);
							$err = $SQL->get_error();
								
							if(strpos($err[1], 'Duplicate') !== false || $err[0] == '1062' || $err[0] == '1060')
							{
								$sql = "UPDATE `{$dbprefix}config` SET `value` = '" . DB_VERSION . "' WHERE `name` = 'db_version'";
								$SQL->query($sql);
								kleeja_admin_info('<br /><br /><span style="color:green;">' . $lang['INST_UPDATE_CUR_VER_IS_UP']. '</span><br />');
								//$complete_upate = false;
							}
						}
					}
				//}
			
				//
				//is there any functions 
				//
				/*
				if($complete_upate or defined('DEV_STAGE') or defined('C_U_F'))
				{
				*/
					if(isset($update_functions) && sizeof($update_functions) > 0)
					{
						foreach($update_functions as $n)
						{
							eval('' . $n . '; ');
						}
					}
				//}
			
				//
				//is there any notes 
				//
				/*
				if($complete_upate or defined('DEV_STAGE'))
				{
				*/
					if(isset($update_notes) && sizeof($update_notes) > 0)
					{
						$msg .= '<br /><span style="color:blue;"><b>' . $lang['INST_NOTES_UPDATE'] . ' :</b> </span><br />';
					
						$i=1;
						foreach($update_notes as $n)
						{
							$msg .= '  [<b>' . $i . '</b>] <br /><span style="color:black;">' . $n. ' </span><br />';
							++$i;
						}

					}
				//}
				
				
				if($complete_upate)
				{
					delete_cache(null, true);
					$msg .= '<br /><br /><span style="color:green;">' . $lang['INST_UPDATE_IS_FINISH']. '</span><br />';
					kleeja_admin_info($msg);
				}
					
			break;
			
			default :
					
					kleeja_admin_info("ok");
				
			break;	
			
		}
	}
	else if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '='))
	{
		
		$text	= $lang['U_LAST_VER_KLJ'];
		kleeja_admin_info($text);
			
	}
	else if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '>'))
	{
		$text	= $lang['U_USE_PRE_RE'];
		kleeja_admin_info($text);
	}
	
exit;
//print_r($extractedFileList);
