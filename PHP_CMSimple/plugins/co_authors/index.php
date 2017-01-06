<?php
/*
============================================================
CMSimple Plugin CoAuthors
============================================================
Version:    CoAuthors 2.0
Released:   05/2016
Copyright:  Gert Ebersbach
Internet:   www.ge-webdesign.de/cmsimple/
============================================================ 
utf-8 check: äöü 
*/





function co_authors($co_author_folder, $co_author_page)
{
	global $pth;
	
	if(!defined('CMSIMPLE_VERSION'))
	{
		return '<p>This plugin requires <b>CMSimple 4.2</b> or higher.</p><p><a href="http://www.cmsimple.org/">CMSimple Download & Updates &raquo;</a></p>';
	}
	
	$GLOBALS['co_author_folder'] = $co_author_folder; global $co_author_folder;
//	echo 'von co_author: ' . $co_author_folder . $co_author_page . '<br>'; // for development only
	
	$co_author_doc = '';
//	$co_author_doc.= '<p class="cmsimplecore_warning" style="clear: both; font-family: arial, sans-serif; font-size: 12px; text-align: center;">Die folgenden Inhalte wurden extern mit CMSimpleCoAutors erstellt und mit dem Plugin CoAuthors_XH in diese Website eingebunden:</p>';
	$co_author_doc.= file_get_contents($pth['folder']['base'].$co_author_folder.'userfiles/co_author/' . $co_author_page . '.txt');
	$co_author_doc = preg_replace('~<h1>(.*)</h1>~', '', $co_author_doc);
	
	$co_author_doc = evaluate_scripting($co_author_doc);

	return($co_author_doc);
}





function co_authorsURL($fileURL)
{
	global $pth;
	
	if(!defined('CMSIMPLE_VERSION'))
	{
		return '<p>This plugin requires <b>CMSimple 4.2</b> or higher.</p><p><a href="http://www.cmsimple.org/">CMSimple Download & Updates &raquo;</a></p>';
	}

	$co_author_doc = '';
//	$co_author_doc.= '<p class="cmsimplecore_warning" style="clear: both; font-family: arial, sans-serif; font-size: 12px; text-align: center;">Die folgenden Inhalte wurden extern mit CMSimpleCoAutors erstellt und mit dem Plugin CoAuthors_XH in diese Website eingebunden:</p>';
	$co_author_doc.= file_get_contents($fileURL);
	$co_author_doc = evaluate_scripting($co_author_doc);

	return($co_author_doc);
}

?>