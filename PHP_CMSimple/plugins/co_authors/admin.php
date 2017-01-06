<?php
/*
============================================================
CMSimple Plugin CoAuthors
============================================================
Version:    CoAuthors 2.0
Released:   2016-05
Copyright:  Gert Ebersbach
Internet:   www.ge-webdesign.de/cmsimple/
============================================================ 
utf-8 check: äöü 
*/

initvar('co_authors');
if ($co_authors) 
{
	if(!defined('CMSIMPLE_VERSION'))
	{
		$o.= '<p>This plugin requires <b>CMSimple 4.2</b> or higher.</p><p><a href="http://www.cmsimple.org/">CMSimple Download & Updates &raquo;</a></p>';
		return;
	}
	
	// Make CMSimple variables accessible
	global $sn,$sv,$sl,$pth;
	
	global $plugin;
	
	// Detect the foldername of the plugin.
	$plugin=basename(dirname(__FILE__),"/");

	$admin = isset($_GET['admin']) ? $_GET['admin'] : '';
	$admin .= isset($_POST['admin']) ? $_POST['admin'] : '';
	
	// Parameter "ON"  shows the Plugin Main Tab.
	// Blank "" or "OFF" does not show the Plugin Main Tab.
	$o.=print_plugin_admin('off');
	
	// First page when loading the plugin.
	if ($admin == '' || $admin == 'plugin_main') 
	{
		$o.='
		<h4>Plugin CoAuthors</h4>
		<hr />
		<ul>
		<li>Version: CoAuthors v2.0</li>
		<li>Released: 2016-05</li>
		<li>Copyright: Gert Ebersbach</li>
		<li>Internet: <a href="http://www.ge-webdesign.de/cmsimpleplugins/?Eigene_Plugins">www.ge-webdesign.de</a></li>
		</ul>
		<hr />
		<p><b>No admin options</b></p>
		';
	}

	if ($admin <> 'plugin_main') 
	{
		$hint=array();
		$hint['mode_donotshowvarnames'] = false;
		$o.=plugin_admin_common($action, $admin, $plugin, $hint);
	}
}
?>