<?php
##################################################
#						Kleeja
#
# Filename : go.php
# purpose :  File for Navigataion .
# copyright 2007-2009 Kleeja.com ..
# license http://opensource.org/licenses/gpl-license.php GNU Public License
# $Author$ , $Rev$,  $Date::                           $
##################################################

// security ..
define ('IN_INDEX' , true);
define ('IN_GO' , true);

//include imprtant file ..
include ('includes/common.php');

($hook = kleeja_run_hook('begin_go_page')) ? eval($hook) : null; //run hook

if(!isset($_GET['go']))
{
	$_GET['go'] = null;	
}

switch ($_GET['go'])
{
	case "guide" : 
	case "extsg" : 

		$stylee	= "guide";
		$titlee	= $lang['GUIDE'];

		//make it loop
		$gusts_data = array();
		foreach($g_exts as $ext=>$data)
		{
			$gusts_data[]	= array('ext' => $ext,
									'num' => Customfile_size($data['size'])//format size as kb, mb,...
									);
		}

		//make it loop
		$users_data = array();
		foreach($u_exts as $ext=>$data)
		{
			$users_data[]	= array('ext' => $ext,
									'num' => Customfile_size($data['size'])//format size as kb, mb,...
									);
		}
		
		($hook = kleeja_run_hook('guide_go_page')) ? eval($hook) : null; //run hook
	
	break;
	
	case "report" :

		//start captcha class
		$ch = new ocr_captcha;
		
		//_post
		$t_rname = isset($_POST['rname']) ? htmlspecialchars($_POST['rname']) : ''; 
		$t_rmail = isset($_POST['rmail']) ? htmlspecialchars($_POST['rmail']) : ''; 
		$t_rtext = isset($_POST['rtext']) ? htmlspecialchars($_POST['rtext']) : ''; 
		
		if (!isset($_POST['submit']))
		{
				$stylee	= "report";
				$titlee	= $lang['REPORT'];
				$url_id	= ($config['mod_writer']) ? $config['siteurl'] . "download" . intval($_GET['id']) . ".html" : $config['siteurl'] . "download.php?id=" . intval($_GET['id']);
				$action	= "./go.php?go=report";
				$code	= $ch->display_captcha(true);
				$id_d	= intval($_GET['id']);
				
				// first
				if (!$_GET['id'])
				{
					kleeja_err($lang['NO_ID']);
				}
				
				($hook = kleeja_run_hook('no_submit_report_go_page')) ? eval($hook) : null; //run hook
		}
		else
		{
			$ERRORS	=	'';
			
			($hook = kleeja_run_hook('submit_report_go_page')) ? eval($hook) : null; //run hook
			
			
			if (empty($_POST['rname']) || empty($_POST['rurl']))
			{
				$ERRORS[]	= $lang['EMPTY_FIELDS'] . ' : ' . (empty($_POST['rname']) ? ' [ ' . $lang['YOURNAME'] . ' ] ' : '')  . (empty($_POST['rurl']) ? '  [ ' . $lang['URL']  . ' ] ': '');
			}
			if(empty($_POST['rid']))
			{
				$ERRORS[]	= $lang['NO_ID'];
			}
			else if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", trim(strtolower($_POST['rmail']))))
			{
				$ERRORS[]	= $lang['WRONG_EMAIL'];
			}
			else if (strlen($_POST['rtext']) > 300)
			{
				$ERRORS[]	= $lang['NO_ME300RES'];
			}
			else if (!$ch->check_captcha($_POST['public_key'], $_POST['code_answer']))
			{
				$ERRORS[]	= $lang['WRONG_VERTY_CODE'];
			}
			
			//no error , lets do process
			if(empty($ERRORS))
			{
					$name	= (string) $SQL->escape($_POST['rname']);
					$text	= (string) $SQL->escape($_POST['rtext']);
					$mail	= (string) strtolower(trim($SQL->escape($_POST['rmail'])));
					$url	= (string) $SQL->real_escape($_POST['rurl']);
					$time 	= (int) time();
					$rid	= (int) intval($_POST['rid']);
					$ip		=  get_ip();


					$insert_query	= array('INSERT'	=> '`name` ,`mail` ,`url` ,`text` ,`time` ,`ip`',
											'INTO'		=> "`{$dbprefix}reports`",
											'VALUES'	=> "'$name', '$mail', '$url', '$text', '$time', '$ip'"
										);
					
					($hook = kleeja_run_hook('qr_insert_new_report')) ? eval($hook) : null; //run hook
			
					$SQL->build($insert_query);
					
					//update number of reports
					$update_query	= array('UPDATE'	=> "{$dbprefix}files",
											'SET'		=> 'report=report+1',
											'WHERE'		=> 'id=' . $rid,
											);
								
					($hook = kleeja_run_hook('qr_update_no_file_report')) ? eval($hook) : null; //run hook
					
					$SQL->build($update_query);
					
					$to = $config['sitemail2']; //administrator e-mail
					$message = $text . "\n\n\n\n" . 'URL :' . $url . ' - TIME : ' . date("d-m-Y h:i a", $time) . ' - IP:' . $ip;
					$subject = $lang['REPORT'];
					send_mail($to, $message, $subject, $mail, $name);
					
					kleeja_info($lang['THNX_REPORTED']);
					
			}
			else
			{
				$errs = '';
				foreach($ERRORS as $r)
				{
					$errs .= '- ' . $r . ' <br/>';
				}			
	
				kleeja_err($errs);
			}
		}
		
		($hook = kleeja_run_hook('report_go_page')) ? eval($hook) : null; //run hook
	
	break; 
	
	
	case "rules" :
	
		$stylee	= "rules";
		$titlee	= $lang['RULES'];
		$contents = (strlen($ruless) > 3) ? stripslashes($ruless) : $lang['NO_RULES_NOW'];
		
		($hook = kleeja_run_hook('rules_go_page')) ? eval($hook) : null; //run hook
	
	break;
	
	
	case "call" : 
	
		//start  captcha class
		$ch = new ocr_captcha;
		
		//_post
		$t_cname = isset($_POST['cname']) ? htmlspecialchars($_POST['cname']) : ''; 
		$t_cmail = isset($_POST['cmail']) ? htmlspecialchars($_POST['cmail']) : ''; 
		$t_ctext = isset($_POST['ctext']) ? htmlspecialchars($_POST['ctext']) : ''; 
		
		if (!isset($_POST['submit']))
		{
			$stylee	= "call";
			$titlee	= $lang['CALL'];
			$action	= "./go.php?go=call";
			$code	= $ch->display_captcha(true);
			
			($hook = kleeja_run_hook('no_submit_call_go_page')) ? eval($hook) : null; //run hook
		}
		else
		{
			//after sumit
			$ERRORS	=	'';
			($hook = kleeja_run_hook('submit_call_go_page')) ? eval($hook) : null; //run hook
			
			if (empty($_POST['cname'])  || empty($_POST['ctext']) )
			{
				$ERRORS[]	= $lang['EMPTY_FIELDS'] . ' : ' . (empty($_POST['cname']) ? ' [ ' . $lang['YOURNAME'] . ' ] ' : '')  . (empty($_POST['ctext']) ? '  [ ' . $lang['TEXT']  . ' ] ': '');
			}
			else if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", trim(strtolower($_POST['cmail']))))
			{
				$ERRORS[] = $lang['WRONG_EMAIL'];
			}
			else if (strlen($_POST['ctext']) > 300)
			{
				$ERRORS[] = $lang['NO_ME300TEXT'];
			}
			else if (!$ch->check_captcha($_POST['public_key'], $_POST['code_answer']))
			{
				$ERRORS[] = $lang['WRONG_VERTY_CODE'];
			}
			
			//no errors ,lets do process
			if(empty($ERRORS))
			{
				$name	= (string) $SQL->escape($_POST['cname']);
				$text	= (string) $SQL->escape($_POST['ctext']);
				$mail	= (string) strtolower(trim($SQL->escape($_POST['cmail'])));
				$timee	= (int)	time();
				$ip		=  get_ip();
					

				$insert_query	= array('INSERT'	=> "`name` ,`text` ,`mail` ,`time` ,`ip`",
										'INTO'		=> "`{$dbprefix}call`",
										'VALUES'	=> "'$name', '$text', '$mail', '$timee', '$ip'"
										);
					
				($hook = kleeja_run_hook('qr_insert_new_call')) ? eval($hook) : null; //run hook
			
				if ($SQL->build($insert_query))
				{
					send_mail($config['sitemail2'], $text  . "\n\n\n\n" . 'TIME : ' . date("d-m-Y h:i a", $timee) . ' - IP:' . $ip, $lang['CALL'], $mail, $name);
					kleeja_info($lang['THNX_CALLED']);
				}
			}
			else
			{
				$errs = '';
				foreach($ERRORS as $r)
				{
					$errs .= '- ' . $r . '. <br/>';
				}				
				kleeja_err($errs);
			}
		}
		
		($hook = kleeja_run_hook('call_go_page')) ? eval($hook) : null; //run hook

	break;
	
	case "del" :

		($hook = kleeja_run_hook('del_go_page')) ? eval($hook) : null; //run hook
		
		//stop .. check first ..
		if (!$config['del_url_file'])
		{
			kleeja_info($lang['NO_DEL_F'], $lang['E_DEL_F']);
		}

		//ok .. go on
		//it's must be more strong . saanina check it again !
		$cd	= $SQL->escape($_GET['cd']); // may.. will protect

		if (!$cd)
		{
			kleeja_err($lang['WRONG_URL']);
		}
		else
		{
			//to check
			if(isset($_GET['sure']) && $_GET['sure'] == 'ok')
			{
				$query = array('SELECT'=> 'f.id, f.name, f.folder, f.size',
								'FROM'	=> "{$dbprefix}files f",
								'WHERE'	=> "f.code_del='" . $cd . "'"
							);
					
				($hook = kleeja_run_hook('qr_select_file_with_code_del')) ? eval($hook) : null; //run hook	
				
				$result	=	$SQL->build($query);

				if ($SQL->num_rows($result) != 0)
				{
					while($row=$SQL->fetch_array($result))
					{
						@kleeja_unlink ($row['folder'] . "/" . $row['name']);
						//delete thumb
						if (file_exists($row['folder'] . "/thumbs/" . $row['name']))
						{
							@kleeja_unlink ($row['folder'] . "/thumbs/" . $row['name']);
						}
						
						$query_del = array(
											'DELETE' => "{$dbprefix}files",
											'WHERE'	=> "id='" . $row['id'] . "'"
											);
								
						($hook = kleeja_run_hook('qr_del_file_with_code_del')) ? eval($hook) : null; //run hook	
						
						if ($SQL->build($query_del))
						{
							//update number of stats
							$update_query	= array('UPDATE'	=> "{$dbprefix}stats",
													'SET'		=> 'files=files-1,sizes=sizes-' . $row['size'],
												);
							
							$SQL->build($update_query);
							kleeja_info($lang['DELETE_SUCCESFUL']);
						}
					}
				
					$SQL->freeresult($result);
				}
			}
			else
			{
				kleeja_info('<script type="text/javascript">
						function confirm_from()
						{
						if(confirm(\'' . $lang['ARE_YOU_SURE_DO_THIS'] . '\'))
							window.location = "go.php?go=del&sure=ok&cd='.$cd.'";
						else
							window.location = "index.php";
						}
					</script>
				<body onload="javascript:confirm_from()">');
			}
		}#else

	break;
	
	
	case "stats" :

		//stop .. check first ..
		if (!$config['allow_stat_pg'])
		{
			kleeja_info($lang['STATS_CLOSED'], $lang['STATS_CLOSED']);
		}

		//ok .. go on
		$titlee		= $lang['STATS'];
		$stylee		= "stats";
		$files_st	= $stat_files;
		$users_st	= $stat_users;
		$sizes_st	= Customfile_size($stat_sizes);	
		$lst_dl_st	= ((int)$config['del_f_day'] <= 0) ? ' [ ' . $lang['CLOSED_FEATURE'] . ' ] ' : gmdate("d-m-Y H:a", $stat_last_f_del);
		$lst_reg	= $stat_last_user;
		
		($hook = kleeja_run_hook('stats_go_page')) ? eval($hook) : null; //run hook
		
	break; 
	
	case "down":
	
		#depreacted from 1rc6+, see download.php
		//go.php?go=down&n=$1&f=$2&i=$3
		if(isset($_GET['n']))
		{
			$url_file = ($config['mod_writer']) ? $config['siteurl'] . "download" . intval($_GET['i']) . ".html" : $config['siteurl'] . "download.php?id=" . intval($_GET['n']);
		}
		else
		{
			$url_file = $config['siteurl'];
		}
		
		header('Location:' . $url_file);
		$SQL->close();
		exit;
		
	break;
	
	default:
		
		($hook = kleeja_run_hook('default_go_page')) ? eval($hook) : null; //run hook	
	
		kleeja_err($lang['ERROR_NAVIGATATION']);
	
	break;
}#end switch

($hook = kleeja_run_hook('end_go_page')) ? eval($hook) : null; //run hook

	//show style ...
	//header
	Saaheader($titlee);
		//tpl
		echo $tpl->display($stylee);
	//footer
	Saafooter();
?>