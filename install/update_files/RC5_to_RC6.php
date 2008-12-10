<?php
// not for directly open
if (!defined('IN_COMMON'))	exit();

///////////////////////////////////////////////////////////////////////////////////////////////////////
// sqls /////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////





///////////////////////////////////////////////////////////////////////////////////////////////////////
//notes ////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////

$update_notes[]	= $lang['INST_NOTE_RC5_TO_RC6'];



///////////////////////////////////////////////////////////////////////////////////////////////////////
//functions ////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

//index changes
$style_changes = <<<KLEEJA
<div class="middle">

<!-- begin welcome index -->
<h2>{lang.WELCOME}..</h2>        
<br />
{welcome_msg}
<br />
<br />
<!-- end welcome index -->


<!---- START  FOR UPLOAD BOXES ---->
<form name="uploader" id="uploader" action="{action}" method="post"  enctype="multipart/form-data" onsubmit="form_submit();">

        
<IF NAME="config.www_url">
    <a href="#"  onclick="showhide();" title="{lang.CHANG_TO_URL_FILE}"><img src="images/urlORfile.gif" alt="{lang.CHANG_TO_URL_FILE}"  /></a>
        <br /><br />
</IF>


        
        <div id="filetype">
                <LOOP NAME=FILES_NUM_LOOP>
				<input type="file" name="file[{{i}}]" id="file[{{i}}]" style="display:{{show}}" />
				</LOOP>
                <br />
                <div id="upload_forum"></div>
                <br />
                <input name="mraupload" onclick="javascript:plus(1);" type="button" value="+" />
                <input name="mreupload" onclick="javascript:minus(1);" type="button" value="-" />
                <br />
                <br />
        <IF NAME="config.safe_code">
                        {SAFE_CODE}
                        <br />
                        <input type="text" name="answer_safe" style="direction:ltr;" />
                        <br />
        </IF>
                <input id="checkr" type="checkbox" onclick="accept_terms('submitr','checkr');" />{lang.AGREE_RULES}
                <br />
                <input type="submit" id="submitr" name="submitr" value="{lang.DOWNLOAD_F}"  disabled="disabled" />
                <input type="text" id="upload_num" value="1" size="1" readonly="readonly"/>
        </div>

        
<IF NAME="config.www_url">
        <div id="texttype">
		 <LOOP NAME=FILES_NUM_LOOP>
		 <input type="text" name="file[{{i}}]" id="file[{{i}}]" size="50" value="{lang.PAST_URL_HERE}" onclick="this.value=''" style="color:silver;display{{show}}" dir="ltr">
		 </LOOP>
                
                <br />
                <div id="upload_f_forum"></div>
                <br />
                <input name="mraupload" onclick="javascript:plus(2);" type="button" value="+" />
                <input name="mreupload" onclick="javascript:minus(2);" type="button" value="-" />
                <br />
                <br />
        <IF NAME="config.safe_code">
                        {SAFE_CODE2}
                        <br />
                        <input type="text" name="answer_safe2" style="direction:ltr;" />
                        <br />
        </IF>
                <input id="checkr2" type="checkbox" onclick="accept_terms('submittxt','checkr2');" />{lang.AGREE_RULES}
                <br />
                <input type="submit" id="submittxt" name="submittxt" value="{lang.DOWNLOAD_T}"   disabled="disabled" />
                <input type="text" id="upload_f_num" value="1" size="1" readonly="readonly"/>
        </div>
</IF>


</form>

<div id="loadbox"><img src="./images/loading.gif" alt="loading ..." /></div>
<!---- END  FOR UPLOAD BOXES ---->

<br />

<!---- START  FOR INFORMATION ---->
<IF NAME="info">
        <div class="result">
                <LOOP NAME="info">
                <b>{lang.INFORMATION}:</b> {{i}}<br />
                </LOOP>
        </div>
</IF>
<!---- END  FOR INFORMATION ---->


<!---- START FOR ONLINE ---->
<IF NAME="show_online">
<b>{lang.NUMBER_ONLINE} :</b>{allnumbers} ( {lang.NUMBER_UONLINE} : {usersnum} , {lang.NUMBER_VONLINE}: {visitornum} )
<br />
<LOOP NAME="shownames">
{{name}} , 
</LOOP>
</IF>
<!---- END FOR ONLINE ---->

</div>
KLEEJA;


function up_date_style()
{
	global $config, $dbprefix, $SQL, $style_changes;
	
	$update_query = array(
		'UPDATE'	=> "{$dbprefix}templates",
		'SET'		=> "template_content = '". $SQL->real_escape($style_changes) ."'",
		'WHERE'		=>	"style_id='". intval($config['style']) ."' AND template_name='index_body'"
	);
	$SQL->build($update_query);
	delete_cache('', true, true);
}

$update_functions[]	=	'up_date_style()';

?>