<?php
##################################################
#						Kleeja
#
# Filename : KljUplaoder.php
# purpose :  main class of script
# copyright 2007 Kleeja.com ..
#class by :  based on class.AksidSars.php  of Nadorino [@msn.com]
# last edit by : saanina
##################################################

	  if (!defined('IN_COMMON'))
	  {
	  echo '<strong><br /><span style="color:red">[NOTE]: This Is Dangrous Place !! [2007 saanina@gmail.com]</span></strong>';
	  exit();
	  }

class KljUploader
{
    var $folder;
    var $action; //page action
    var $filesnum; //number of fields
    var $types;  // filetypes
    var $ansaqimages;   // imagestypes
    var $filename;     // filename
	var $sizes;
	var $typet;
	var $sizet;
	var $id_for_url;
    var $filename2;  //alternative file name
    var $linksite;    //site link
    var $decode;     // decoding name with md5 or time
	var $id_user;
	var $errs = array();
	var $safe_code;


/**
// source : php.net
 */
 function watermark($name, $ext, $logo){

	if (preg_match("/jpg|jpeg/",$ext)){$src_img=imagecreatefromjpeg($name);}
	if (preg_match("/png/",$ext)){$src_img=imagecreatefrompng($name);}
	if (preg_match("/gif/",$ext)){$src_img=imagecreatefromgif($name);}

	$src_logo = imagecreatefrompng($logo);

    $bwidth  = imageSX($src_img);
    $bheight = imageSY($src_img);
    $lwidth  = imageSX($src_logo);
    $lheight = imageSY($src_logo);
	
	//fix bug for 1beta3
	if ( $bwidth > 160 &&  $bheight > 130 ) { 
	
    $src_x = $bwidth - ($lwidth + 5);
    $src_y = $bheight - ($lheight + 5);
    ImageAlphaBlending($src_img, true);
    ImageCopy($src_img,$src_logo,$src_x,$src_y,0,0,$lwidth,$lheight);

	if (preg_match("/jpg|jpeg/",$ext)){imagejpeg($src_img, $name);}
	if (preg_match("/png/",$ext)){imagepng($src_img, $name);}
	if (preg_match("/gif/",$ext)){imagegif($src_img, $name);}
	
	}# < 150
	else 
	{
	return false;
	}
	
}


/*
	Function createthumb($name,$filename,$new_w,$new_h)
	example : createthumb('pics/apple.jpg','thumbs/tn_apple.jpg',100,100);
	creates a resized image
	source :http://icant.co.uk/articles/phpthumbnails/
*/
function createthumb($name,$ext,$filename,$new_w,$new_h)
{

	if (preg_match("/jpg|jpeg/",$ext)){$src_img=imagecreatefromjpeg($name);}
	if (preg_match("/png/",$ext)){$src_img=imagecreatefrompng($name);}
	if (preg_match("/gif/",$ext)){$src_img=imagecreatefromgif($name);}

	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	
	if ($old_x > $old_y)
	{
		$thumb_w=$new_w;
		$thumb_h=$old_y*($new_h/$old_x);
	}
	elseif ($old_x < $old_y)
	{
		$thumb_w=$old_x*($new_w/$old_y);
		$thumb_h=$new_h;
	}
	elseif ($old_x == $old_y)
	{
		$thumb_w=$new_w;
		$thumb_h=$new_h;
	}
	$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);

	if (preg_match("/jpg|jpeg/",$ext)){imagejpeg($dst_img,$filename);}
	if (preg_match("/png/",$ext)){imagepng($dst_img,$filename);}
	if (preg_match("/gif/",$ext)){imagegif($dst_img,$filename);}
	
	imagedestroy($dst_img);
	imagedestroy($src_img);
}



################################


################################

function process () {
		global $SQL,$dbprefix,$config,$lang;
		global $use_ftp,$ftp_server,$ftp_user,$ftp_pass,$ch;

//for folder
if(!file_exists($this->folder))   // نهاية التحقق من المجلد
{
	$jadid=@mkdir($this->folder);
	$jadid2=@mkdir($this->folder.'/thumbs');
	if($jadid){

	$this->errs[]= $lang['NEW_DIR_CRT'];

	$fo=@fopen($this->folder."/index.html","w");
	$fo2=@fopen($this->folder."/thumbs/index.html","w");
	$fw=@fwrite($fo,'<p>KLEEJA ..</p>');
	$fw2=@fwrite($fo2,'<p>KLEEJA ..</p>');
	$fi=@fopen($this->folder."/.htaccess","w");
	$fi2=@fopen($this->folder."/thumbs/.htaccess","w");
	$fy=@fwrite($fi,'RemoveType .php .php3 .phtml .pl .cgi .asp .htm .html
	php_flag engine off');
	$fy2=@fwrite($fi2,'RemoveType .php .php3 .phtml .pl .cgi .asp .htm .html
	php_flag engine off');
	$chmod=@chmod($this->folder,0777);
	$chmod2=@chmod($this->folder.'/thumbs/',0777);

	if(!$chmod){$this->errs[]=   $lang['PR_DIR_CRT'];} //if !chmod
	}
	else
	{
		$this->errs[]= '"<font color=red><b>' . $lang['CANT_DIR_CRT'] . '<b></font>';
	}
}

	//then wut did u click
	if ( isset($_POST['submitr']) ) { $wut=1; }
	elseif( isset($_POST['submittxt']) ){$wut=2;}

	//safe_code
	if($this->safe_code and ($wut==1 or $wut==3)){
		if(!$ch->check_captcha($_POST['public_key'],$_POST['answer_safe'])){
		return $this->errs[]= $lang['WRONG_VERTY_CODE'];
		}
	}
	
	// no url
	if ($wut == 1) {
	 #-----------------------------------------------------------------------------------------------------------------------------------------------------------#
	for($i=0;$i<$this->filesnum;$i++){
	$this->filename2=@explode(".",$_FILES['file']['name'][$i]);
	$this->filename2=$this->filename2[count($this->filename2)-1];
	$this->typet = $this->filename2;
	$this->sizet = $_FILES['file']['size'][$i];
		//tashfer [decode]
		if($this->decode == "time"){
		$zaid=time();
		$this->filename2=$this->filename.$zaid.$i.".".$this->filename2;
		}
		elseif($this->tashfir == "md5")
		{
		$zaid=md5(time());
		$zaid=substr($zaid,0,10);
		$this->filename2=$this->filename.$zaid.$i.".".$this->filename2;
		}  //if($this->tashfir == "time"){
		else
		{
		// اسم الصورة الحقيقي
		$this->filename2=$_FILES['file']['name'][$i];
		}
		//end tashfer

	if(empty($_FILES['file']['tmp_name'][$i])){ }

	elseif(file_exists($this->folder.'/'.$_FILES['file']['name'][$i]))
	{
	$this->errs[]=  $lang['SAME_FILE_EXIST'];
    }
	elseif( preg_match ("#[\\\/\:\*\?\<\>\|\"]#", $this->filename2 ) )
	{
    $this->errs[]= $lang['WRONG_F_NAME'] . '['.$this->filename2.']';
    }
    elseif(!in_array(strtolower($this->typet),$this->types))
	{
    $this->errs[]= $lang['FORBID_EXT'] . '['.$this->typet.']';
    }
	elseif($this->sizes[strtolower($this->typet)] > 0 && $this->sizet >= $this->sizes[strtolower($this->typet)])
	{
	$this->errs[]=  $lang['SIZE_F_BIG'] . ' ' . Customfile_size($this->sizes[$this->typet]);
	}
    else
    {
#----------------------------------------------------------uplaod----------------------------------------------------------------------
//ob_end_flush();
//flush();
	if (!$use_ftp)
	{
				$file = move_uploaded_file($_FILES['file']['tmp_name'][$i], $this->folder."/".$this->filename2);
	}
	else // use ftp account
	{
				// set up a connection or die
				$conn_id = @ftp_connect($ftp_server);
	            // Login with username and password
	            $login_result = @ftp_login($conn_id, $ftp_user, $ftp_pass);

	            // Check the connection
	            if ((!$conn_id) || (!$login_result)) {
	                  $this->errs[]= $lang['CANT_CON_FTP'] . $ftp_server;
	                }
	            // Upload the file
	            $file = @ftp_put($conn_id, $this->folder."/".$this->filename2,$_FILES['file']['tmp_name'][$i], FTP_BINARY);
				@ftp_close($conn_id);
	}
	//flush();

	if ($file) {
	$this->saveit ($this->filename2,$this->folder,$this->sizet,$this->typet);
	} else {
	$this->errs[]	= $lang['CANT_UPLAOD'];
	}

	}
}
	 #-----------------------------------------------------------------------------------------------------------------------------------------------------------#

	}#wut=1
	elseif ( $wut == 2 && $config['www_url'] == '1' ){
////
for($i=0;$i<$this->filesnum;$i++){
//


		$filename 			=  basename($_POST['file'][$i]);
		$this->filename2	= @explode(".",$filename);
		$this->filename2	= $this->filename2[count($this->filename2)-1];
		$this->typet 		= $this->filename2;

		
		//tashfer [decode]
		if($this->decode == "time"){
		$zaid=time();
		$this->filename2=$this->filename.$zaid.$i.".".$this->filename2;
		}
		elseif($this->tashfir == "md5"){
		$zaid=md5(time());
		$zaid=substr($zaid,0,10);
		$this->filename2=$this->filename.$zaid.$i.".".$this->filename2;
		}
		else{
		// اسم الملف الحقيقي
		$this->filename2=$filename;
		}
		//end tashfer


	if(empty($_POST['file'][$i])){}
	else
	if(!preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $_POST['file'][$i]))
	{
	$this->errs[]=  $lang['WRONG_LINK'].$filename ;
    }
	elseif(file_exists($this->folder.'/'.$filename))
	{
	$this->errs[]=  $lang['SAME_FILE_EXIST'];
    }
	elseif( preg_match ("#[\\\/\:\*\?\<\>\|\"]#", $this->filename2 ) )
	{
    $this->errs[]= $lang['WRONG_F_NAME'] . '['.$this->filename2.']';
    }
    elseif(!in_array(strtolower($this->typet),$this->types))
	{
    $this->errs[]= $lang['FORBID_EXT'] . '['.$this->typet.']';
    }
	else //end err .. start upload
	{

	//sooo
	if (function_exists('curl_init'))
	{

	// attempt retrieveing the url
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$_POST['file'][$i]);
	curl_setopt($curl_handle,CURLOPT_TIMEOUT,30);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,15);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl_handle,CURLOPT_FAILONERROR,1);
	$data = curl_exec($curl_handle);
	curl_close($curl_handle);

	$this->sizet = strlen($data);

		if($this->sizes[strtolower($this->typet)] > 0 && $this->sizet >= $this->sizes[strtolower($this->typet)])
		{
		$this->errs[]=  $lang['SIZE_F_BIG'] . ' ' . Customfile_size($this->sizes[$this->typet]);
		}
		else
		{
		//then ..write new file
	    $fp2 = @fopen($this->folder."/".$this->filename2,"w");
	    fwrite($fp2,$data);
	    fclose($fp2);
		}

		$this->saveit ($this->filename2,$this->folder,$this->sizet,$this->typet);
	}
	else
	{
	$this->errs[]	= $lang['CURL_IS_OFF'];
	}

}#else

		}#end loop
	}#end wut2

}#END process





function saveit ($filname,$folderee,$sizeee,$typeee) { //
		global $SQL,$dbprefix,$config,$lang;

				// sometime cant see file after uploading.. but ..
				@chmod($filname."/".$folderee, 0755);//0755
//---------->
				$name 	= (string)	$SQL->escape($filname);
				$size	= (int) 	$sizeee;
				$type 	= (string)	$SQL->escape($typeee);
				$folder	= (string)	$SQL->escape($folderee);
				$timeww	= (int)		time();
				$user	= (int)		$this->id_user;
				$code_del=(string)	md5(time());

				$insert	= $SQL->query("INSERT INTO `{$dbprefix}files`(
				`name` ,`size` ,`time` ,`folder` ,`type`,`user`,`code_del`
				)
				VALUES (
				'$name','$size','$timeww','$folder','$type','$user','$code_del'
				)");

				if (!$insert) { $this->errs[]=  $lang['CANT_INSERT_SQL'];}

				$this->id_for_url =  $SQL->insert_id();

				//calculate stats ..s
				$update1 = $SQL->query("UPDATE `{$dbprefix}stats` SET
				`files`=files+1,
				`sizes`=sizes+" . $size . ",
				`last_file`='" . $folder ."/". $name . "'
				");
				if ( !$update1 ){ die($lang['CANT_UPDATE_SQL']);}
				//calculate stats ..e
//<---------------
	//must be img //
$this->imgstypes	= array('png','gif','jpg','jpeg','tif','tiff');
$this->thmbstypes	= array('png','jpg','jpeg','gif');
if ($config[del_url_file]){$extra_del = $lang['URL_F_DEL'] . ':<br /><textarea rows=2 cols=49 rows=1>'.$this->linksite.(($config[mod_writer]) ? "del".$code_del.".html" : 'go.php?go=del&amp;cd='.$code_del ).'</textarea><br/>';}


//show imgs
if (in_array(strtolower($this->typet),$this->imgstypes)){

	//make thumbs
	if( ($config[thumbs_imgs]!=0) && in_array(strtolower($this->typet),$this->thmbstypes))
	{
	@$this->createthumb($folderee."/".$filname,strtolower($this->typet),$folderee.'/thumbs/'.$filname,100,100);
	$extra_thmb = $lang['URL_F_THMB'] . ':<br /><textarea rows=2 cols=49 rows=1>[url='.$this->linksite.(($config[mod_writer]) ? "image".$this->id_for_url.".html" : "download.php?img=".$this->id_for_url ).'][img]'.$this->linksite.(($config[mod_writer]) ? "thumb".$this->id_for_url.".html" : "download.php?thmb=".$this->id_for_url ).'[/img][/url]</textarea><br />';
	$extra_show_img = '<div style="text-align:center"><img src="'.$this->linksite.(($config[mod_writer]) ? "thumb".$this->id_for_url.".html" : "download.php?thmb=".$this->id_for_url ).'" /></div></br>';
	}
	
	//write on image
	if( ($config[write_imgs]!=0) && in_array(strtolower($this->typet),$this->thmbstypes))
	{
		$this->watermark($folderee . "/" . $filname,strtolower($this->typet), 'images/watermark.png');
	}

	//then show
	$this->errs[] = $lang['IMG_DOWNLAODED'] . '<br />' . $extra_show_img . '
			' . $lang['URL_F_IMG'] . ':<br /><textarea rows=2 cols=49 rows=1>'.$this->linksite.(($config[mod_writer]) ? "image".$this->id_for_url.".html" : "download.php?img=".$this->id_for_url ).'</textarea><br />
			' . $lang['URL_F_BBC'] . ':<br /><textarea rows=2 cols=49 rows=1>[url='.$config[siteurl].'][img]'.$this->linksite.(($config[mod_writer]) ? "image".$this->id_for_url.".html" : "download.php?img=".$this->id_for_url ).'[/img][/url]</textarea><br />
			'.$extra_thmb.$extra_del;

}else {
	//then show other files
	$this->errs[] = $lang['FILE_DOWNLAODED'] . '<br />
			' . $lang['URL_F_FILE'] . ':<br /><textarea cols=49 rows=1>'.$this->linksite.(($config[mod_writer]) ? "download".$this->id_for_url.".html" : "download.php?id=".$this->id_for_url ).'</textarea><br />
			' . $lang['URL_F_BBC'] . ':<br /><textarea rows=2 cols=49 rows=1>[url]'.$this->linksite.(($config[mod_writer]) ? "download".$this->id_for_url.".html" : "download.php?id=".$this->id_for_url ).'[/url]</textarea><br />
			'.$extra_del;
}

unset ($filename,$folderee,$sizeee,$typeee);

}#save it




}#end class

?>