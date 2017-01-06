<?php // utf8-marker: äöü 

if (!defined('CMSIMPLE_VERSION') || preg_match('#/jquery/admin.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* 
=========================================================
Adapted for CMSimple 4.0 and higher: Gert Ebersbach 2013
http://www.ge-webdesign.de
=========================================================
*/

/**
 * jQuery for CMSimple
 *
 * Admin-interface for configuring the plugin
 * via the standard-functions of pluginloader.
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.3.1 - 2011-09-30
 * @build 2011093001
 * @package jQuery
 **/

initvar('jquery');
if($jquery)
{
	$o.= '<div class="plugintext">
<div class="plugineditcaption">
Plugin jQuery for CMSimple
</div>
<hr />
<p>' . $tx['message']['plugin_standard1'] . '</p>
<p>' . $tx['message']['plugin_standard2'] . ' <a href="./?file=config&amp;action=array"><b>' . $tx['filetype']['config'] . '</b></a></p>
<hr />
<p>based on jQuery for CMSimple v. 1.3.1</p>' . 
'<p>Author: &copy; 2011 <a href="http://cmsimple.holgerirmler.de/" target="_blank">Holger Irmler</a></p>
<p>Adapted for CMSimple 4.0 and higher by <a href="http://www.ge-webdesign.de/" target="_blank">ge-webdesign.de</a></p>
</div>';
}
?>