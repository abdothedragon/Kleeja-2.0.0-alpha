<?php
	//files
	//part of admin extensions
	//conrtoll files
	
	//copyright 2007-2009 Kleeja.com ..
	//license http://opensource.org/licenses/gpl-license.php GNU Public License
	//$Author$ , $Rev$,  $Date::                           $
	
	// not for directly open
	if (!defined('IN_ADMIN'))
	{
		exit('no directly opening : ' . __file__);
	}
	

	//for style ..
	$stylee		= "admin_files";
	
	$url_or		= (isset($_REQUEST['order_by']) ? '&amp;order_by=' . $_REQUEST['order_by'] . ((isset($_REQUEST['order_way'])) ? '&amp;order_by=1' : '') : '');
	$url_or2		= (isset($_REQUEST['order_by']) ? '&amp;order_by=' . $_REQUEST['order_by']  : '');
	$url_lst	= (isset($_REQUEST['last_visit']) ? '&amp;last_visit=' . $_REQUEST['last_visit'] : '');
	$url_sea	= (isset($_GET['search']) ? '&amp;search=' . $_GET['search'] : '');
	$url_pg		= (isset($_GET['page']) ? '&amp;page=' . intval($_GET['page']) : '');
	$page_action = basename(ADMIN_PATH) . '?cp=files' . $url_pg . $url_or . $url_sea . $url_lst;
	$ord_action	= basename(ADMIN_PATH) . "?cp=files" . $url_pg . $url_sea . $url_lst;
	$page2_action	= basename(ADMIN_PATH) . "?cp=files" . $url_or2 . $url_sea . $url_lst;
	$action		= $page_action;
	$is_search	= false;
	
	$query	= array('SELECT'	=> 'COUNT(f.id) AS total_files',
					'FROM'		=> "{$dbprefix}files f",
					'JOINS'		=> array(
							array(
								'LEFT JOIN'	=> "{$dbprefix}users u",
								'ON'		=> 'u.id=f.user'
							)
						),
					'ORDER BY'	=> 'f.id '
					);
					
	//new feature delete all user files [only one user]			
	if(isset($_GET['deletefiles']))
	{
		$query = array(
				'SELECT'	=> 'id,size,name,folder',
				'FROM'		=> "{$dbprefix}files AS f",
				);
				
		$search = base64_decode($_GET['deletefiles']);
		$search	= unserialize($search);
		$search['filename'] = (!isset($search['filename'])) ? '' : $search['filename']; 
		$search['username'] = (!isset($search['username'])) ? '' : $search['username'];
		$search['than'] = (!isset($search['than'])) ? '1' : $search['than'];
		$search['size'] = (!isset($search['size'])) ? '' : $search['size'];
		$search['ups'] = (!isset($search['ups'])) ? '' : $search['ups'];
		$search['uthan'] = (!isset($search['uthan'])) ? '1' : $search['uthan'];
		$search['rep'] = (!isset($search['rep'])) ? '' : $search['rep'];
		$search['rthan'] = (!isset($search['rthan'])) ? '1' : $search['rthan'];
		$search['lastdown'] = (!isset($search['lastdown'])) ? '' : $search['lastdown'];
		$search['ext'] = (!isset($search['ext'])) ? '' : $search['ext'];
		$search['user_ip'] = (!isset($search['user_ip'])) ? '' : $search['user_ip'];

		$file_namee	= ($search['filename'] != '') ? 'AND f.real_filename LIKE \'%' . $SQL->escape($search['filename']) . '%\' ' : ''; 
		$usernamee	= ($search['username'] != '') ? 'AND u.name LIKE \'%' . $SQL->escape($search['username']) . '%\'' : ''; 
		$size_than	=   ' f.size ' . ($search['than']!=1 ? '<=' : '>=') . (intval($search['size']) * 1024) . ' ';
		$ups_than	=  ($search['ups'] != '') ? 'AND f.uploads ' . ($search['uthan']!=1 ? '<' : '>') . intval($search['ups']) . ' ' : '';
		$rep_than	=  ($search['rep'] != '') ? 'AND f.report ' . ($search['rthan']!=1 ? '<' : '>') . intval($search['rep']) . ' ' : '';
		$lstd_than	=  ($search['lastdown'] != '') ? 'AND f.last_down =' . (time()-(intval($search['lastdown']) * (24 * 60 * 60))) . ' ' : '';
		$s_exts 	= 	explode(",",$SQL->escape($search['ext']));
		$exte		=  ($search['ext'] != '') ? "AND f.type IN ('" . implode("', '", $s_exts) . "')" : '';
		$ipp		=  ($search['user_ip'] != '') ? 'AND f.user_ip LIKE \'%' . $SQL->escape($search['user_ip']) . '%\' ' : '';
		$query['WHERE'] = "$size_than $file_namee $ups_than $exte $rep_than $usernamee $lstd_than $exte $ipp";
			
		$result = $SQL->build($query);
		$sizes = false;
		$num = 0;
		while($row=$SQL->fetch_array($result))
		{
			//delete from folder ..
			@kleeja_unlink ($root_path . $row['folder'] . "/" . $row['name']);
						
			//delete thumb
			if (file_exists($root_path . $row['folder'] . "/thumbs/" . $row['name']))
			{
				@kleeja_unlink ($root_path . $row['folder'] . "/thumbs/" . $row['name']);
			}
			
			$ids[] = $row['id'];
			$num++;		
			$sizes += $row['size'];
		}
		
		$SQL->freeresult($result);
			
		if($num == 0)
		{
			kleeja_admin_err($lang['ADMIN_DELETE_FILES_NOF']);
		}
			
		if($sizes)
		{
			//update number of stats
			$update_query	= array('UPDATE'	=> "{$dbprefix}stats",
									'SET'		=> "sizes=sizes-$sizes,files=files-$num",
									);
									
			$SQL->build($update_query);
			
			//delete all files in just one query
			$query_del = array('DELETE'	=> "{$dbprefix}files",
								'WHERE'	=> "id IN (" . implode(',', $ids) . ")",);
									
			$SQL->build($query_del);
			//$SQL->freeresult($result);
			
			$langdeleted = sprintf($lang['ADMIN_DELETE_FILES_OK'], $num);
			kleeja_admin_info($langdeleted);
		}
		else
		{
			$errs = $lang['ADMIN_DELETE_FILES_NOF'];
			kleeja_admin_err($errs);
		}
			
	}
						
	//posts search ..
	if (isset($_POST['search_file']))
	{
		redirect(basename(ADMIN_PATH) . '?cp=files&search=' . base64_encode(serialize($_POST)));
		$SQL->close();
		exit;
	}
	else if(isset($_GET['search']))
	{
		$deletelink = basename(ADMIN_PATH) . '?cp=files&deletefiles=' . $SQL->escape($_GET['search']);
		$search = base64_decode($_GET['search']);
		$search	= unserialize($search);
		$search['filename'] = (!isset($search['filename'])) ? '' : $search['filename']; 
		$search['username'] = (!isset($search['username'])) ? '' : $search['username'];
		$search['than'] = (!isset($search['than'])) ? '1' : $search['than'];
		$search['size'] = (!isset($search['size'])) ? '' : $search['size'];
		$search['ups'] = (!isset($search['ups'])) ? '' : $search['ups'];
		$search['uthan'] = (!isset($search['uthan'])) ? '1' : $search['uthan'];
		$search['rep'] = (!isset($search['rep'])) ? '' : $search['rep'];
		$search['rthan'] = (!isset($search['rthan'])) ? '1' : $search['rthan'];
		$search['lastdown'] = (!isset($search['lastdown'])) ? '' : $search['lastdown'];
		$search['ext'] = (!isset($search['ext'])) ? '' : $search['ext'];
		$search['user_ip'] = (!isset($search['user_ip'])) ? '' : $search['user_ip'];

		$file_namee	= ($search['filename'] != '') ? 'AND f.real_filename LIKE \'%' . $SQL->escape($search['filename']) . '%\' ' : ''; 
		$usernamee	= ($search['username'] != '') ? 'AND u.name LIKE \'%' . $SQL->escape($search['username']) . '%\'' : ''; 
		$size_than	=   ' f.size ' . ($search['than']!=1 ? '<=' : '>=') . (intval($search['size']) * 1024) . ' ';
		$ups_than	=  ($search['ups'] != '') ? 'AND f.uploads ' . ($search['uthan']!=1 ? '<' : '>') . intval($search['ups']) . ' ' : '';
		$rep_than	=  ($search['rep'] != '') ? 'AND f.report ' . ($search['rthan']!=1 ? '<' : '>') . intval($search['rep']) . ' ' : '';
		$lstd_than	=  ($search['lastdown'] != '') ? 'AND f.last_down =' . (time()-(intval($search['lastdown']) * (24 * 60 * 60))) . ' ' : '';
		$s_exts 	= 	explode(",",$SQL->escape($search['ext']));
		$exte		=  ($search['ext'] != '') ? "AND f.type IN ('" . implode("', '", $s_exts) . "')" : '';
		$ipp		=  ($search['user_ip'] != '') ? 'AND f.user_ip LIKE \'%' . $SQL->escape($search['user_ip']) . '%\' ' : '';
		$is_search	= true;
		$query['WHERE'] = "$size_than $file_namee $ups_than $exte $rep_than $usernamee $lstd_than $exte $ipp";
	}
	else if(isset($_REQUEST['last_visit']))
	{
		$query['WHERE']	= "f.time > '" . intval($_REQUEST['last_visit']) . "'";
	}
	
	if(isset($_REQUEST['order_by']) && ($_REQUEST['order_by'] == 'real_filename' OR $_REQUEST['order_by'] == 'size' OR $_REQUEST['order_by'] == 'user' OR $_REQUEST['order_by'] == 'user_ip' OR $_REQUEST['order_by'] == 'uploads' OR $_REQUEST['order_by'] == 'time' OR $_REQUEST['order_by'] == 'type' OR $_REQUEST['order_by'] == 'folder' OR $_REQUEST['order_by'] == 'report'))
	{
		$query['ORDER BY'] = "f." . $SQL->escape($_REQUEST['order_by']);
	}
	
	if(isset($_REQUEST['order_way']) && $_REQUEST['order_way'] == '1')
	{
		$query['ORDER BY'] .= ' ASC';
	}
	else
	{
		$query['ORDER BY'] .= ' DESC';
	}
	
	//display files or display pics and files only in search
	if(empty($query['WHERE']))
	{
		$query['WHERE'] = "f.type NOT IN ('gif','jpg','png','bmp','jpeg','tif','tiff','GIF','JPG','PNG','BMP','JPEG','TIF','TIFF')";
	}
	else if (isset($_REQUEST['last_visit']))
	{
		$query['WHERE'] .= "AND f.type NOT IN ('gif','jpg','png','bmp','jpeg','tif','tiff','GIF','JPG','PNG','BMP','JPEG','TIF','TIFF')";
	}

	$result_p = $SQL->build($query);
	
	$nums_rows = 0;
	$n_fetch = $SQL->fetch_array($result_p);
	$nums_rows = $n_fetch['total_files'];
	$SQL->freeresult($result_p);

	//pager 
	$currentPage = (isset($_GET['page']))? intval($_GET['page']) : 1;
	$Pager = new SimplePager($perpage, $nums_rows, $currentPage);
	$start = $Pager->getStartRow();

	$no_results = false;
	
	if ($nums_rows > 0)
	{
		$query['SELECT'] = 'f.*, u.name AS username';
		$query['LIMIT']	= "$start, $perpage";
		$result = $SQL->build($query);
		$sizes = false;
		$num = 0;
		while($row=$SQL->fetch_array($result))
		{
			//make new lovely arrays !!
			$userfile =  $config['siteurl'] . ($config['mod_writer'] ? 'fileuser-' . $row['user'] . '.html' : 'ucp.php?go=fileuser&amp;id=' . $row['user']);
			
			
			$arr[]	= array('id' => $row['id'],
							'name' => "<a title=\" " . ($row['real_filename'] == '' ? $row['name'] : $row['real_filename']) . "\" href=\"./" . $root_path . $row['folder'] . "/" . $row['name'] . "\" target=\"blank\">" . ($row['real_filename'] == '' ? ((strlen($row['name']) > 20) ? substr($row['name'], 0, 20) . '...' : $row['name']) : ((strlen($row['real_filename']) > 20) ? substr($row['real_filename'], 0, 20) . '...' : $row['real_filename'])) . "</a>",
							'size' => Customfile_size($row['size']),
							'ups' => $row['uploads'],
							'time' => date("d-m-Y H:a", $row['time']),
							'type' => $row['type'],
							'folder' => $row['folder'],
							'report' => ($row['report'] > 4) ? "<span style=\"color:red\"><big>" . $row['report'] . "</big></span>":$row['report'],
							'user' => ($row['user'] == '-1') ? $lang['GUST'] :  '<a href="' . $userfile . '" target="_blank">' . $row['username'] . '</a>',
							'ip' 	=> '<a href="http://www.ripe.net/whois?form_type=simple&amp;full_query_string=&amp;searchtext=' . $row['user_ip'] . '&amp;do_search=Search" target="_new">' . $row['user_ip'] . '</a>',
							'showfilesbyip' => '<br /><a href="'. basename(ADMIN_PATH) . "?cp=files" . '&search=' . base64_encode(serialize(array('user_ip' => $row['user_ip']))) . '">' . $lang['SHOWFILESBYIP'] . '</a>',
						);
			//
			$del[$row['id']] = (isset($_POST['del_' . $row['id']]) ) ? $_POST["del_" . $row['id']] : '';


				//when submit !!
				if (isset($_POST['submit']))
				{
					if ($del[$row['id']])
					{
						//delete from folder ..
						@kleeja_unlink ($root_path . $row['folder'] . "/" . $row['name']);
						
						//delete thumb
						if (is_file($row['folder'] . "/thumbs/" . $row['name'] ))
						{
							@kleeja_unlink ($root_path . $row['folder'] . "/thumbs/" . $row['name'] );
						}
						$ids[] = $row['id'];
						$num++;		
						$sizes += $row['size'];
						
					}
			}
		}
			
		if (isset($_POST['submit']))
		{
			//no files to delete
			if(isset($ids) && !empty($ids))
			{
				//$imp =  implode(',', $ids);
				//we have imprvove this and use implode with In statment in future [WE DID :D]
				$query_del = array('DELETE'	=> "{$dbprefix}files",
									'WHERE'	=> "id IN (" . implode(',', $ids) . ")",);
			
				$SQL->build($query_del);

				//update number of stats
				$update_query	= array('UPDATE'	=> "{$dbprefix}stats",
										'SET'		=> "sizes=sizes-$sizes,files=files-$num",
									);
				//echo $sizes;
				$SQL->build($update_query);
			}
		}
		
		$SQL->freeresult($result);
	}
	else  #num_rows
	{
		$no_results = true;
	}
		
	//some vars
	$total_pages= $Pager->getTotalPages(); 
	$page_nums 	= $Pager->print_nums($page_action); 
	
		
	//after submit 
	if (isset($_POST['submit']))
	{
		$text	= $lang['FILES_UPDATED'] . '<meta HTTP-EQUIV="REFRESH" content="0; url=' . $action . '">' ."\n";
		$stylee	= "admin_info";
	}
?>