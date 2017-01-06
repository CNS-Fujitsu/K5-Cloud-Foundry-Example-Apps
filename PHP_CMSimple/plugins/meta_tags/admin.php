<?php // utf8-marker = äöü

if (!defined('CMSIMPLE_VERSION') || preg_match('#/meta_tags/admin.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/*
Meta Tags - module admin
@author Martin Damken
@link http://www.zeichenkombinat.de

Not in use anymore
*/

initvar('meta_tags');
if($meta_tags)
{
	$o .= "\n".'<p style="font-family: arial, sans-serif;"><b>Plugin "meta_tags" for CMSimple</b></p>
<hr />
<p style="font-family: arial, sans-serif;">' . $tx['message']['plugin_standard1'] . '</p>
<p style="font-family: arial, sans-serif;"><b>No admin options</b></p>
<hr />
<p style="font-family: arial, sans-serif;">Author: <a href="http://zeichenkombinat.de/" target="_blank">Martin Damken</a></p>
<p>Adapted for CMSimple 4.0 and higher by <a href="http://www.ge-webdesign.de/" target="_blank">ge-webdesign.de</a></p>';
}

?>