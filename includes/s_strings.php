<?php
##################################################
#						Kleeja
#
# Filename : s_strings.php
# purpose : find strings in texrt and add after in another text or replace it ..
# copyright 2007-2009 Kleeja.com ..
# license http://opensource.org/licenses/gpl-license.php GNU Public License
# $Author$ , $Rev$,  $Date::                           $
##################################################

//no for directly open
if (!defined('IN_COMMON'))
{
	exit('no directly opening : ' . __file__);
}


/*
 * Text search engine ,we designed this system to perform 
 * some actions for our plugins that make replacements in files
 * 
 * do_search (numbers) : 
 * 1 : to find and replace 
 * 2 : find and replace which between to sentences 
 * 3 : find and add after in new line 
 * 4 : find and add after in the same line
 * 5 : find and add before in new line 
 * 6 : find and add before in the same liner
 */


class sa_srch
{

	var $text			=	''; 
	var $find_word		=	''; 
	var $another_word	=	''; 


	/**
	* initiate class
	*/
	function do_search($type_of_do=1)
	{
		if($this->text == '')
		{
			return false;
		}

		switch($type_of_do):
			case 1: 
				$this->type_replace();
			break;
			case 2: 
				$this->type_replace(1);
			break;
			case 3: 
				$this->type_after();
			break;
			case 4: 
				$this->type_after(1);
			break;
			case 5: 
				$this->type_before();
			break;
			case 6: 
				$this->type_before(1);
			break;
		endswitch;
	
	}

	/*
	* find and replace
	*/
	function type_replace($many = false)
	{
		if($many || is_array($this->find_word))
		{
			$md5_f = md5($this->text);
			$this->text	= preg_replace('#' . $this->ig_sp_exper(preg_quote($this->find_word[0] , '#')) . '(.*?)' . $this->ig_sp_exper(preg_quote($this->find_word[1], '#')) . '#', $this->another_word, $this->text);
			
			//lets do it with another idea
			if($md5_f == md5($this->text))
			{
				$ex1 = explode($this->find_word[0], $this->text);
				$ex2 = explode($this->find_word[1], $this->text);
				$this->text = $ex1[0] . $this->another_word . $ex2[1];
			}
		}
		else
		{
			$md5_f = md5($this->text);
			$this->text	= preg_replace('#' . $this->ig_sp_exper(preg_quote($this->find_word, '#')) . '#', $this->another_word, $this->text);
			
			//lets do it with another idea
			if($md5_f == md5($this->text))
			{
				$ex = explode($this->find_word, $this->text, 2);
				$this->text = $ex[0] . $this->another_word . $ex[1];
			}
		}
	
	}
	
	/*
	* find and add after 
	*/
	function type_after($same_line=false)
	{
		$md5_f = md5($this->text);
		$this->text	=	preg_replace('#' . $this->ig_sp_exper(preg_quote($this->find_word, '#'))  . '#',  $this->find_word . (!$same_line ? "\n" : "") . $this->another_word . (!$same_line ? "\n" : ""), $this->text);

		//lets do it with another idea
		if($md5_f == md5($this->text))
		{
			$ex = explode($this->find_word, $this->text, 2);
			$this->text = $ex[0] . $this->find_word . (!$same_line ? "\n" : "") . $this->another_word . $ex[1];
		}
	}

	/*
	* find and add before 
	*/
	function type_before($same_line=false)
	{
		$md5_f = md5($this->text);
		$this->text	=	preg_replace('#' . $this->ig_sp_exper(preg_quote($this->find_word, '#')) . '#',   (!$same_line ? "\n" : "") . $this->another_word . (!$same_line ? "\n" : "")  . $this->find_word, $this->text);
		
		//lets do it with another idea
		if($md5_f == md5($this->text))
		{
			$ex = explode($this->find_word, $this->text, 2);
			$this->text = $ex[0] . (!$same_line ? "\n" : "") . $this->another_word . (!$same_line ? "\n" : "")  . $this->find_word . $ex[1];
		}
	}

	/**
	* Ignore spaces & other not important chars
	*/
	function ig_sp_exper($text)
	{
		//clean spaces
		$text = str_replace("\t", ' ', $text);
		$text = preg_replace("#\s{2,}#", ' ', $text);

		//i can put * here, and will be very usefull, but will make
		//space like not here !
		$text = str_replace(' ', '\s+', $text);
		return $text;
	}
}

