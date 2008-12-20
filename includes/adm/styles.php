<?php
	//styles
	//part of admin extensions
	//conrtoll styles and templates 
	//kleeja.com
	
	// not for directly open
	if (!defined('IN_ADMIN'))
	{
		exit('no directly opening : ' . __file__);
	}

	
switch ($_GET['sty_t']) 
{
		default:
		case "st" :
		
		//for style ..
		$stylee 	= "admin_styles";
		$action 	= "admin.php?cp=styles&amp;sty_t=st";

		//get styles
		$query = array(
					'SELECT'	=> '*',
					'FROM'		=> "{$dbprefix}lists",
					'WHERE'		=> "list_type=1"
					);
						
		$result = $SQL->build($query);
		
		//admin style
		$arr = array();
		$arr[] = array('style_id' => 0, 'style_name' => 'admin', 'selected'=>''); 
		
		while($row=$SQL->fetch_array($result))
		{
				$arr[] = array( 'style_id'	=> $row['list_id'],
								'style_name'=>  $row['list_name'] . ($config['style'] == $row['list_id'] ? ' [' . $lang['STYLENAME'] . ']' : ''),
								'selected'	=>  ($config['style'] == $row['list_id'] ? 'selected="selected"' : ''),
							);

		}
		
		$SQL->freeresult($result);
	

		//after submit
		if(isset($_GET['style_choose']))
		{
			$_POST['submit']		= true;
			$_POST['style_choose']	= $_GET['style_choose'];
			$_POST['method']		= $_GET['method'];
		}
		
		if (isset($_POST['submit']))
		{
			$style_id = intval($_POST['style_choose']);
			
			switch($_POST['method'])
			{
				case '1': //show templates
					//for style ..
					$stylee = "admin_show_tpls";
					//words
					$action = "admin.php?cp=styles&sty_t=style_orders";
					
					//get_tpls
					$query = array(
								'SELECT'	=> '*',
								'FROM'		=> "{$dbprefix}templates",
								'WHERE'		=> "style_id=" . $style_id
								);
									
					$result = $SQL->build($query);
					
					$arr	=	array();
					
					while($row=$SQL->fetch_array($result))
					{
							$arr[] = array(
											'template_name' => $row['template_name']
							);
					}
					$SQL->freeresult($result);
					
				break;
			
				case '2': // del the style
				
						//style number 1, 0 not for deleting 
						if($style_id != 1 && $style_id != 0)
						{
							$query_del = array(
											'DELETE'	=> "{$dbprefix}lists",
											'WHERE'		=> "list_id='" . $style_id . "' AND list_type=1"
											);

											
							if (!$SQL->build($query_del)) die($lang['CANT_DELETE_SQL']);
											
							$query_del2 = array(
											'DELETE'	=> "{$dbprefix}templates",
											'WHERE'		=> 'style_id=' . $style_id
											);		
											
							if (!$SQL->build($query_del2)) die($lang['CANT_DELETE_SQL'] . ' 2');
							
							//show msg
							$text	= $lang['STYLE_DELETED'] . '<meta HTTP-EQUIV="REFRESH" content="2; url=./admin.php?cp=styles">' ."\n";
						}
						else
						{
							//show msg
							$text	= $lang['STYLE_1_NOT_FOR_DEL'].'<meta HTTP-EQUIV="REFRESH" content="2; url=./admin.php?cp=styles">' ."\n";
						}

							$stylee	= "admin_info";
						
				break;
				
				case '3': //export as xml 
				
					//get_style information
					$query = array(
								'SELECT'	=> '*',
								'FROM'		=> "{$dbprefix}lists",
								'WHERE'		=> "list_id='" . $style_id . "' AND list_type=1"
								);
									
					$result = $SQL->build($query);

					$style_info	=	$SQL->fetch_array($result);
					
					//build the xml sheet
					
					$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\r\n";
					$xml_data .= "<kleeja>\r\n";
					$xml_data .= "<info>\r\n";
					$xml_data .= "\t<style_name>" . $style_info['list_name']  . "</style_name>\r\n";
					$xml_data .= "\t<style_author>" . $style_info['list_author']  . "</style_author>\r\n";
					$xml_data .= "</info>\r\n";
					$xml_data .= "<templates>\r\n";
					
					//get tpls
					$query2 = array(
								'SELECT'	=> '*',
								'FROM'		=> "{$dbprefix}templates",
								'WHERE'		=> "style_id=" . $style_id
								);
									
					$result2 = $SQL->build($query2);

					while($row=$SQL->fetch_array($result2))
					{
						$xml_data .= "\t<template name=\"" . $row['template_name'] . "\">\r\n";
						$xml_data .= "\t<![CDATA[" . $row['template_content'] . "]]>\r\n";
						$xml_data .= "\t</template>\r\n";
					}
					$SQL->freeresult($result);
				
					$xml_data .= "</templates>\r\n";
					$xml_data .= "</kleeja>\r\n";
				
					//now , lets export
					header('Pragma: no-cache');
					header("Content-Type: application/xml; name=" . $style_info['list_name'] . ".xml");
					header("Content-disposition: attachment; filename=" . $style_info['list_name'] . ".xml");
					echo $xml_data;
					exit;
					
				break;
				
			}
		}
		//new style from xml
		if(isset($_POST['submit_new_style']))
		{
			$text	=	'';
			// oh , some errors
			if($_FILES['imp_file']['error'])
			{
				$text	= $lang['ERR_IN_UPLOAD_XML_FILE'];
			}
			else if(!is_uploaded_file($_FILES['imp_file']['tmp_name']))
			{
				$text	= $lang['ERR_UPLOAD_XML_FILE_NO_TMP'];
			}

			//get content
			$contents = @file_get_contents($_FILES['imp_file']['tmp_name']);
			// Delete the temporary file if possible
			
			// Are there contents?
			if(!trim($contents))
			{
				$text	= $lang['ERR_UPLOAD_XML_NO_CONTENT'];
			}
						
						
			if(empty($text))
			{
				if(creat_style_xml($contents))
				{
					$text	= $lang['NEW_STYLE_ADDED'].'<meta HTTP-EQUIV="REFRESH" content="2; url=./admin.php?cp=styles">' ."\n";	
				}
				else
				{
					$text	= $lang['ERR_IN_UPLOAD_XML_FILE'];
				}
			}		

			$stylee	= "admin_info";
		}
		
		break; 
		
		case "style_orders" :
		
		//edit or del tpl 
		if(isset($_POST['tpls_submit']))
		{
			
			//style id ..fix for zooz
			$style_id	=	$_POST['style_id'];
			
			//tpl name 
			$tpl_name	=	$SQL->escape($_POST['tpl_choose']);
			
			if(!is_numeric($style_id))
			{
				big_error($lang['ERROR_NAVIGATATION'], 'style_id is not exists!!');
			}
			
			if(empty($tpl_name))
			{
				big_error($lang['ERROR_NAVIGATATION'], $lang['NO_TPL_SHOOSED']);
			}
			
			switch($_POST['method'])
			{	
				case '1': //edit tpl
					//for style ..
					$stylee = "admin_edit_tpl";
					$action = "admin.php?cp=styles&amp;sty_t=style_orders";

					
					$query = array(
								'SELECT'	=> 'template_content',
								'FROM'		=> "{$dbprefix}templates",
								'WHERE'		=>	"style_id='$style_id' AND template_name='$tpl_name'"
								);
											
					$template_content	= $SQL->fetch_array($SQL->build($query));
					$template_content	= htmlspecialchars(stripslashes($template_content['template_content']));
					

				break;
				
				case '2' : //delete tpl
				
						$query_del = array(
										'DELETE'	=> "{$dbprefix}templates",
										'WHERE'		=>	"style_id='$style_id' AND template_name='$tpl_name'"
										);
															
						if (!$SQL->build($query_del)) die($lang['CANT_DELETE_SQL']);
						
						//show msg
						$link	= './admin.php?cp=styles&amp;style_choose=' . $style_id . '&amp;method=1';
						$text	= $lang['TPL_DELETED']  . '<br /> <a href="' . $link . '">' . $lang['GO_BACK_BROWSER'] . '</a><meta HTTP-EQUIV="REFRESH" content="1; url=' . $link . '">' ."\n";
						$stylee	= "admin_info";
						
				break;
			}
		}
		
		// submit edit of tpl
		if(isset($_POST['tpl_edit_submit']))
		{
			//style id 
			$style_id			=	intval($_POST['style_id']);
			//tpl name 
			$tpl_name			=	$SQL->escape($_POST['tpl_choose']);				
			//tpl contents 
			$template_content	=	$SQL->real_escape($_POST['template_content']);	
		
			//update
			$update_query = array(
									'UPDATE'	=> "{$dbprefix}templates",
									'SET'		=> "template_content = '". $template_content ."'",
									'WHERE'		=>	"style_id='$style_id' AND template_name='$tpl_name'"
								);

			if ($SQL->build($update_query))
			{
				//delete cache ..
				delete_cache('tpl_' . $tpl_name);
				//show msg
				$link	= './admin.php?cp=styles&amp;style_choose=' . $style_id . '&amp;method=1';
				$text	= $lang['TPL_UPDATED'] . '<br /> <a href="' . $link . '">' . $lang['GO_BACK_BROWSER'] . '</a><meta HTTP-EQUIV="REFRESH" content="1; url=' . $link . '">' ."\n";
				$stylee	= "admin_info";
					
			}
			else
			{
				big_error('', $lang['CANT_UPDATE_SQL']);
			}	
		}
		
		//new template file
		if(isset($_POST['submit_new_tpl']))
		{
			//style id 
			$style_id	= $_POST['style_id'];
			//tpl name 
			$tpl_name	= $SQL->escape($_POST['new_tpl']);
		
			if(!is_numeric($style_id))
			{
				big_error($lang['ERROR_NAVIGATATION'], 'style_id is not exists!!');
			}
			
			if(empty($tpl_name))
			{
				big_error($lang['ERROR_NAVIGATATION'], $lang['NO_TPL_NAME_WROTE']);
			}
			
			$insert_query = array(
							'INSERT'	=> 'style_id, template_name',
							'INTO'		=> "{$dbprefix}templates",
							'VALUES'	=> "'$style_id', '$tpl_name'"
							);

			
			if($SQL->build($insert_query))
			{
					$link	= './admin.php?cp=styles&amp;style_choose=' . $style_id . '&amp;method=1';
					$text	= $lang['TPL_CREATED']  . '<br /> <a href="' . $link . '">' . $lang['GO_BACK_BROWSER'] . '</a><meta HTTP-EQUIV="REFRESH" content="1; url=' . $link . '">' ."\n";
					$stylee	= "admin_info";
			}
			else
			{
				die($lang['CANT_INSERT_SQL']);	
			}
		
		
		}
		
		break;
}
?>
