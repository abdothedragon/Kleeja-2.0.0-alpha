<?php
// not for directly open
if (!defined('IN_COMMON'))
{
	exit();
}

//
//db version when this update was released
//
define ('DB_VERSION' , '7');

///////////////////////////////////////////////////////////////////////////////////////////////////////
// sqls /////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////

$update_sqls['up_dbv_config'] = "UPDATE `{$dbprefix}config` SET `value` = '" . DB_VERSION . "' WHERE `name` = 'db_version'";
$update_sqls['online_i'] = "INSERT INTO `{$dbprefix}config` (`name`, `value`, `option`, `display_order`) VALUES ('last_online_time_update', '" .  time() . "', '', 0)";
$update_sqls['files_del_c'] = "INSERT INTO `{$dbprefix}config` (`name`, `value`, `option`, `display_order`) VALUES ('klj_clean_files_from', '0', '', 0)";
$update_sqls['online_t'] = "TRUNCATE TABLE `{$dbprefix}online`";
$update_sqls['online_c'] = "ALTER TABLE `{$dbprefix}online` ADD `session` VARCHAR( 100 ) NOT NULL";
$update_sqls['online_a'] = "ALTER TABLE `{$dbprefix}online` ADD UNIQUE (`session`)";
$update_sqls['online_moue1'] = "ALTER TABLE `{$dbprefix}stats` ADD `most_user_online_ever` INT( 11 ) NOT NULL";
$update_sqls['online_moue2'] = "ALTER TABLE `{$dbprefix}stats` ADD `lastuser` VARCHAR( 300 ) NOT NULL ";
$update_sqls['online_moue3'] = "ALTER TABLE `{$dbprefix}stats` ADD `last_muoe` INT( 10 ) NOT NULL";

$update_sqls['livexts_feature'] = "INSERT INTO `{$dbprefix}config` (`name` ,`value` ,`option` ,`display_order`)VALUES ('livexts', 'swf', '<input type=\"text\" id=\"livexts\" name=\"livexts\" value=\"{con.livexts}\" size=\"20\">', '70')";

///////////////////////////////////////////////////////////////////////////////////////////////////////
//notes ////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////

$update_notes[]	= $lang['INST_NOTE_RC6_TO_1.0.0'];



///////////////////////////////////////////////////////////////////////////////////////////////////////
//functions ////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////


//$update_functions[]	=	'name()';

?>
