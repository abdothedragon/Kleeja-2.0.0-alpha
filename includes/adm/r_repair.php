<?php
/**
*
* @package adm
* @version $Id$
* @copyright (c) 2007 Kleeja.com
* @license ./docs/license.txt
*
*/


// not for directly open
if (!defined('IN_ADMIN'))
{
	exit();
}


//
//fix tables ..
//
$query	= "SHOW TABLE STATUS";
$result	= $SQL->query($query);
$text = '';
	
while($row=$SQL->fetch_array($result))
{
	$queryf	=	"REPAIR TABLE `" . $row['Name'] . "`";
	$resultf = $SQL->query($queryf);
	if ($resultf)
	{
		$text .= '<li>' . $lang['REPAIRE_TABLE'] . $row['Name'] . '</li>';
	}
}
	
$SQL->freeresult($result);
	
//
//fix stats ..
//

//ge all files sizes
$query_s	= array(
					'SELECT'	=> 'size',
					'FROM'		=> "`{$dbprefix}files`"
				);

$result_s = $SQL->build($query_s);

$files_number = $files_sizes = 0;

while($row=$SQL->fetch_array($result_s))
{
	$files_number++;
	$files_sizes = $files_sizes+$row['size'];
}

$SQL->freeresult($result_s);

//get all users number
$query_w	= array(
					'SELECT'	=> 'name',
					'FROM'		=> "`{$dbprefix}users`"
				);

$result_w = $SQL->build($query_w);
		
$user_number = 0;
while($row=$SQL->fetch_array($result_w))
{
	$user_number++;
}
	
$SQL->freeresult($result_w);

$update_query	= array(
						'UPDATE'	=> "{$dbprefix}stats",
						'SET'		=> "files='" . $files_number . "', sizes='" . $files_sizes . "', users='" . $user_number . "'"
					);

if ($SQL->build($update_query))
{
	$text .= '<li>' . $lang['REPAIRE_F_STAT'] . '</li>';
	$text .= '<li>' . $lang['REPAIRE_S_STAT'] . '</li>';
}

//
//clear all cache ..
//
delete_cache('', true);
$text .= '<li>' . $lang['REPAIRE_CACHE'] . '</li>';

$stylee = "admin_info";

