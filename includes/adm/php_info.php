<?php
//php_info
//part of admin extensions
//show php information
	
//copyright 2007-2009 Kleeja.com ..
//license http://opensource.org/licenses/gpl-license.php GNU Public License
//$Author: phpfalcon $ , $Rev: 449 $,  $Date:: 2009-06-29 05:46:07 +0300#$
	
// not for directly open
if (!defined('IN_ADMIN'))
{
	exit('no directly opening : ' . __file__);
}

//if not enabled !
if(isset($NO_PHPINFO) || !function_exists('phpinfo'))
{
	redirect('./');
}
	
//for style ..
$stylee	= "admin_php_info";
$action	= basename(ADMIN_PATH) . '?cp=' . basename(__file__, '.php');
	
ob_start();
@phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
$phpinfo = ob_get_clean();

$phpinfo = trim($phpinfo);

//get contents between body tag
preg_match_all('#<body[^>]*>(.*)</body>#si', $phpinfo, $output);

if (!empty($phpinfo) && !empty($output))
{
	$output = $output[1][0];

	// expose_php can make the image not exist
	if (preg_match('#<a[^>]*><img[^>]*></a>#', $output))
	{
		$output = preg_replace('#<tr class="v"><td>(.*?<a[^>]*><img[^>]*></a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
	}
	else
	{
		$output = preg_replace('#<tr class="v"><td>(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2" style="><tr><td>\1</td></tr></table></td></tr>', $output);
	}

	$output = preg_replace('#<table[^>]+>#i', '<table>', $output);
	$output = preg_replace('#<img border="0"#i', '<img', $output);
	$output = str_replace(array('class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'), array('class="row1"', 'class="row2" ', '', '', '<span', '</span>'), $output);

	if (!empty($output))
	{
		$orig_output = $output;
		preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
		$output = (!empty($output[1][0])) ? $output[1][0] : $orig_output;
	}
}
