<?php
##################################################
#						Kleeja 
#
# Filename : style.php 
# purpose :  Template engine ..:
# copyright 2007-2008 Kleeja.com ..
#license http://opensource.org/licenses/gpl-license.php GNU Public License
#based on : easytemplate version  1.3 .. <http://daif.net/easy>
##################################################

//no for directly open
if (!defined('IN_COMMON'))
{
	exit('no directly opening : ' . __file__);
}

class kleeja_style
{
		var $vars; //Reference to $GLOBALS 
		var $HTML; //html page content
		var $color = array();
		
		//patterns Array
		var $pats = array(
		//Foreach Variables
		"/{{([A-Z0-9_\]\[\"]{1,})}}/i",
		//Globals Variables
		"/{([A-Z0-9_\]\[\"]{1,})}/i",
		//Foreach Statement
		"/<LOOP\s+NAME\s*=\s*(\"|)+([a-z0-9_]{1,})+(\"|)\s*>/i",
		//Foreach Statement With Limited Value
		"/<LOOP\s+NAME\s*=\s*(\"|)+([a-z0-9_]{1,})+(\"|)\s*LIMIT\s*=\s*(\"\\d+\"|\\d+)\s*>/i",
		"/<\/LOOP>/i",
		"/<ELSE>/i",
		"/<\/IF>/i",
		//Switch Statement
		"/<SWITCH\s+NAME\s*=\s*\"([A-Z0-9_]{1,})\"\s*CASE\s*=\s*\"(.+)\"\s*VALUE\s*=\s*\"(.+)\"\s*>/i",
		//Include Statement
		"/<INCLUDE\s+NAME\s*=\s*\"(.+)\"\s*>/iU",
		//----------------->++
		'#<ODD="([a-zA-Z0-9\_\-\+\./]+)"\>(.*?)<\/ODD\>#is',
		'#<EVEN="([a-zA-Z0-9\_\-\+\./]+)"\>(.*?)<\/EVEN\>#is',
		'#<RAND="(.*?)\"[^>],[^>]"(.*?)"[^>]>#is',
		//<-----------------
		);
		//Replacements Array
		var $reps = array(
		"<?php print \$var[\"\\1\"]?>",
		"<?php print \$this->vars[\"\\1\"]?>",
		"<?php foreach(\$this->vars[\"\\2\"] as \$key=>\$var){ ?>",
		"<?php \$this->_limit(\"\\2\",\\4);foreach(\$this->vars[\"\\2\"] as \$key=>\$var){ ?>",
		"<?php } ?>",
		"<?php } else { ?>",
		"<?php } ?>",
		"<?php print \$this->_switch(\$this->vars[\"\\1\"],\"\\2\",\"\\3\")?>",
		"<?php print kleeja_style::_include(\"\\1\"); ?>",
		//<---------------- ++
		"<?php if(intval(\$var['\\1'])%2){?> \\2 <?php } ?>",
		"<?php if(intval(\$var['\\1'])% 2 == 0){?> \\2 <?php } ?>",
		"<?php \$KLEEJA_tpl_rand_is=(\$KLEEJA_tpl_rand_is==0)?1:0; print((\$KLEEJA_tpl_rand_is==1) ?'\\1':'\\2'); ?>",
		//<-----------
		);

	
	//Function to make limited Array, I wrote this function On Ramadan 3eed :)
		function _limit($arr_name,$limit=10){
			$arr  = $this->vars[$arr_name];
			$page = $this->vars[_GET][$arr_name.'_PS'];
			$pagestart = ($page*$limit > count($arr))?0:$page*$limit;
			$pageend   = ($page*$limit+$limit > count($arr))?count($arr):$page*$limit+$limit;//need some Optimization
			for($i=$pagestart;$i<$pageend;$i++) $page_array[] = $arr[$i];
			$this->vars[$arr_name] = $page_array;
			$query = preg_replace("/(\&|)$arr_name+_PS=\\d+/i","",$_SERVER[QUERY_STRING]);
			$prefix = ($query)?"?$query&":"?";
			for($i=0;$i<count($arr)/$limit;$i++)
			$this->vars[$arr_name.'_paging'] .= ($page==$i)?"<b>$i</b> ":"<a href=".$prefix.$arr_name."_PS=$i class=paging>$i</a> ";
		}
	//Function to if.
		function _if_callback($matches){
			$condition = str_replace('.','"]["',$matches[2]).'"]';
			if($matches[4]){
				$condition = "$matches[2]\"]==\"$matches[4]\"";
			}
			if(strtoupper($matches[1])=="LOOP"){
				return "<?php if(\$var[\"$condition){ ?>";
			}else{
				return "<?php if(\$this->vars[\"$condition){ ?>";
			}
		}
		
	//Function to Switch Color.
		function _sw($index){
			return $this->color["$index"] = ($this->color["$index"]) ? false:true;
		}
	//Function to Replace Array Variables
		function _replace_callback($matches){
			return str_replace('.','"]["',$matches[0]);
		}
	//Function to Replace Array Variables
		function _color_callback($matches){
			$rand = rand();
			return "=<?php print (\$this->_sw($rand)) ? \"$matches[1]\":\"$matches[2]\"?>";
		}
	//switch Tag
		function _switch($var,$case,$value){
			$case  = explode(',',$case);
			$value = explode(',',$value);
			foreach($case as $k=>$val)
			if($var==$val) return $value[$k];
		}
	//include Tag
		function _include($fn){
			return($this->display($fn));
		}		
	//Function to Assign Veriables
		function assign($var,&$to)
		{
			$GLOBALS[$var] = $to;
		}
		
		function _parse($code){
			$code = preg_replace_callback("/<IF\s+(NAME|LOOP)\s*=\s*\"([A-Z0-9_\.\-]{1,})+(=(.*)|)\"\s*>/i",array('kleeja_style','_if_callback'),$code);
			$code = preg_replace_callback("/({[A-Z0-9_\.\-]{1,}})/i",array('kleeja_style','_replace_callback'),$code);
			$code = preg_replace_callback("/=\"([#0-9A-Z_\.\-\/]{1,})\|([#0-9A-Z_\.\-\/]{1,})\"/iU",array('kleeja_style','_color_callback'),$code);
			$code = preg_replace($this->pats, $this->reps ,$code);
			return $code;
		}

		//get tpl
		function _load_template($template_name)
		{
			global $config, $SQL,$dbprefix,$root_path;
			
			if(empty($config['style'])) $config['style'] = 1;
			
			$style_id = $config['style'];
			
			//admin style id is 0 
			//so 
			if(substr($template_name, 0, 6) == 'admin_')
			{
				$style_id = 0;
			}
			
			$query = array(
							'SELECT'	=> 't.template_content',
							'FROM'		=> "{$dbprefix}templates t",
							'WHERE'		=>	"t.style_id='". (int) $style_id ."' AND t.template_name='" . (string) $template_name . "'"
						);
			$result	=	$SQL->build($query);
			$template_content = $SQL->fetch_array($result);

				
				if(!$template_content['template_content'] || empty($template_content['template_content'])) 
				{
					if($style_id != 1)
					{
						$query['FROM'] .= ", {$dbprefix}lists l";
						$query['WHERE'] = "(t.style_id='1' OR (l.list_name='default' AND t.style_id=l.list_id)) AND t.template_name='" . (string) $template_name . "'";

						$result	=	$SQL->build($query);
						$template_content = $SQL->fetch_array($result);
					}
					else
						big_error('No Template !', 'Requested "' . $template_name . '" template doesnt exists or an empty !! ');
				}
					
				$this->HTML = stripslashes($template_content['template_content']);
				$this->HTML = $this->_parse($this->HTML);
				$filename = fopen($root_path . 'cache/tpl_' . $this->re_name_tpl($template_name) . '.php', 'w');
				flock($filename, LOCK_EX); // exlusive look
				fwrite($filename, $this->HTML);
				fclose($filename);
		}
		
		//show it
		function display($template_name)
		{	
			global $config, $SQL, $root_path;
			
			$this->vars  = &$GLOBALS;
			
			//clean the name
			$template_name	=	$SQL->escape($template_name);
			
			//is there ?
			if(!file_exists($root_path.'cache/tpl_' . $this->re_name_tpl($template_name) . '.php'))
			{
				$this->_load_template($template_name);
			}

			ob_start();
			include($root_path.'cache/tpl_' . $this->re_name_tpl($template_name) . '.php');
			$page = ob_get_contents();
			ob_end_clean();
		
			return $page;
		}
		
		//change name of template to be valid 1rc6+
		function re_name_tpl($name)
		{
			return preg_replace("/[^a-z0-9-_]/", "-", strtolower($name));
		}

}

?>
