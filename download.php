<?php
/**
*
* @package Kleeja
* @version $Id$
* @copyright (c) 2007 Kleeja.com
* @license ./docs/license.txt
*
*/

//
// we deprecated download.php , so we have to put it for those who upgraded
// to this version
//

$arr_req	= array();
$get_data	= empty($_GET) ? array() : $_GET;
foreach ($get_data as $key => $val)
{
	$arr_req[] = urlencode($key) . '=' . urlencode($val);
}

$request = implode($arr_req, "&");

header('Location: do.php?' . $request);