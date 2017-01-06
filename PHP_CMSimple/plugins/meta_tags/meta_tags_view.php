<?php

if (!defined('CMSIMPLE_VERSION') || preg_match('#/meta_tags/meta_tags_view.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* utf8-marker = äöüß */
/**
 * Meta-Tags - module meta_tags_view
 *
 * Creates the menu for the user to change
 * meta-tags (description, keywords, title
 * and robots) per page.
 *
 * @author Martin Damken
 * @link http://www.zeichenkombinat.de
 * @version 1.0.02 
 * @package pluginloader
 * @subpackage meta_tags
 */ 
/**
 * meta_tags_view()
 * 
 * @param array $page Gets cleaned of unallowed 
 * doublequotes, that will destroy input-fields
 * @return string $view Returns the created view
 */
function meta_tags_view($page){
	global $sn, $su, $tx, $pth;
	array_walk($page, create_function('&$data','$data=str_replace("\"", "&quot;", $data);'));

	$lang = $tx['meta_tags'];
	$help_icon = tag('img src="' . $pth['folder']['base'] . 'css/icons/help_icon.gif" alt="" class="helpicon"');
	
	$my_fields = array('title', 'description', 'keywords', 'robots');

	$view ="\n".'<form action="'.$sn.'?'.$su.'" method="post" id="meta_tags">';	
	$view .= "\n\t".'<p><b>'.$lang['form_title'].'</b></p>';

	foreach($my_fields as $field){
		$view .= "\n\t".'<a class="pl_tooltip" href="#">'.$help_icon.'<span style="padding: 10px 9px 12px 9px;">' . str_replace(tag('br').tag('br').tag('br').tag('br'),tag('br').tag('br'), str_replace("\r",tag('br'),str_replace("\n",tag('br'),$lang['hint_'.$field]))) . '</span></a>';
		$view .= "\n\t".'<label for = "'.$field.'"><span class = "mt_label">'.$lang[$field] .'</span></label>' .tag('br');
		$view .= "\n\t\t".tag('input type="text" size="50" name="'.$field.'" id="'.$field.'" value="'. $page[$field].'"').tag('hr');
	}
	$view .= "\n\t".tag('input name="save_page_data" type="hidden"');
	$view .= "\n\t".'<div style="text-align: right;">';
	$view .= "\n\t\t".tag('input type="submit" value="'.$lang['submit'].'"').tag('br');
	$view .= "\n\t".'</div>';
	$view .= "\n".'</form>';
	return $view;
}
?>