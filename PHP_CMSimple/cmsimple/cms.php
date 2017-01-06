<?php // utf8-marker = äöü

/*
================================================== 
This file is a part of CMSimple 4.6.3
Released: 2016-05-29
Project website: www.cmsimple.org
================================================== 
CMSimple COPYRIGHT INFORMATION

(c) Gert Ebersbach - mail@ge-webdesign.de

CMSimple is released under the GPL3 licence. 
You may not remove copyright information from the files. 
Any modifications will fall under the copyleft conditions of GPL3.

GPL 3 Deutsch: http://www.gnu.de/documents/gpl.de.html
GPL 3 English: http://www.gnu.org/licenses/gpl.html

HISTORY:
-------------------------------------------------- 
2012-11-11 CMSimple 4 - www.cmsimple.org
(c) Gert Ebersbach - mail@ge-webdesign.de
-------------------------------------------------- 
CMSimple_XH 1.5.3
2012-03-19
based on CMSimple version 3.3 - December 31. 2009
For changelog, downloads and information please see http://www.cmsimple-xh.com
-------------------------------------------------- 
CMSimple version 3.3 - December 31. 2009
Small - simple - smart
(c) 1999-2009 Peter Andreas Harteg - peter@harteg.dk 

END CMSimple COPYRIGHT INFORMATION
================================================== 
*/

ini_set('opcache.enable', 0);
ini_set('xcache.cacher', 'Off');
ini_set('xcache.stat', 'Off');

if (preg_match('/cms.php/i', $_SERVER['SCRIPT_NAME'])){die('Access Denied');}

$title = '';
$o = '';
$e = '';
$hjs = '';
$bjs = '';
$onload = '';

// version-informations
define('CMSIMPLE_VERSION', 'CMSimple 4.6.3');
define('CMSIMPLE_RELEASE', 2016052901);
define('CMSIMPLE_DATE', '2016-05-29');
define('CMSIMPLE_VERSIONSINFO', 'http://www.cmsimple.org/downloads_cmsimple40/versioninfo/version.nfo');
//END version-informations

header('Content-Type: text/html; charset=utf-8');

$pth['folder']['cmsimple'] = $pth['folder']['base'] . 'cmsimple/';
$pth['file']['log'] = $pth['folder']['cmsimple'] . 'log.php';
$pth['file']['cms'] = $pth['folder']['cmsimple'] . 'cms.php';

$pth['file']['execute'] = './index.php';
$pth['folder']['content'] = './content/';

// 4.5
if(file_exists($pth['folder']['content'] . 'content.htm') && !file_exists($pth['folder']['content'] . 'content.php'))
{
	copy($pth['folder']['content'] . 'content.htm',$pth['folder']['content'] . date("Ymd_His") . '_UPDATE45_content.htm');
	copy($pth['folder']['content'] . 'pagedata.php',$pth['folder']['content'] . date("Ymd_His") . '_UPDATE45_pagedata.php');
	copy($pth['folder']['content'] . 'content.htm',$pth['folder']['content'] . 'content.php');
}

if(file_exists($pth['folder']['content'] . 'content.htm') && file_exists($pth['folder']['content'] . 'content.php'))
{
	unlink($pth['folder']['content'] . 'content.htm');
}

if(file_exists($pth['folder']['cmsimple'] . 'log.txt') && file_get_contents($pth['folder']['cmsimple'] . 'log.txt') !== 'nix')
{
	copy($pth['folder']['cmsimple'] . 'log.txt',$pth['folder']['content'] . date("Ymd_His") . '_UPDATE45_log.txt');
	$logfileHandle = fopen($pth['folder']['cmsimple'] . 'log.txt', 'w+');
	fwrite($logfileHandle, 'nix');
	fclose($logfileHandle);
}
// END 4.5

$pth['file']['content'] = $pth['folder']['content'] . 'content.php';
$pth['file']['pagedata'] = $pth['folder']['content'] . 'pagedata.php';

// CMSimple 4: $pth['folder']['base'] removed => defined in index.php's now


// CMSimple 4 define config.php
if(file_exists('./config.php') && $pth['folder']['base'] != './')
{
	$pth['file']['config'] = './config.php';
}
else
{
	$pth['file']['config'] = $pth['folder']['cmsimple'] . 'config.php';
}


if (file_exists($pth['folder']['cmsimple'].'defaultconfig.php')) 
{
    include($pth['folder']['cmsimple'].'defaultconfig.php');
}
if (!include($pth['file']['config']))
    die('Config file missing');

//for compatibility with older versions
if (!isset($cf['folders']['userfiles']))
    $cf['folders']['userfiles'] = 'userfiles/';
if (!isset($cf['folders']['downloads']))
    $cf['folders']['downloads'] = 'downloads/';
if (!isset($cf['folders']['images']))
    $cf['folders']['images'] = 'images/';
if (!isset($cf['folders']['media']))
    $cf['folders']['media'] = 'downloads/';


// fix $_SERVER['SCRIPT_NAME'] for crazy configured IIS servers 

$cmsimpleScriptNameVar = $_SERVER['SCRIPT_NAME'];
$cmsimpleScriptNameArray = explode('/', $cmsimpleScriptNameVar);

if($cf['server']['script_name_slice'] == 'true')
{
	$cmsimpleScriptNameArray = array_slice($cmsimpleScriptNameArray,2);
	$cmsimpleScriptNameVarNew = '/' . implode('/', $cmsimpleScriptNameArray);
}
else
{
	$cmsimpleScriptNameVarNew = implode('/', $cmsimpleScriptNameArray);
}

$_SERVER['SCRIPT_NAME'] = $cmsimpleScriptNameVarNew;


// CMSimpleSubsites: prepare CMSIMPLE_ROOT

$cmsimpleRootVar = preg_replace('/\/index.php/i', '', $_SERVER['SCRIPT_NAME']);
$cmsimpleRootArray = explode('/', $cmsimpleRootVar);

if($pth['folder']['base'] == '../../')
{
	array_pop($cmsimpleRootArray);
	array_pop($cmsimpleRootArray);
}

if($pth['folder']['base'] == '../')
{
	array_pop($cmsimpleRootArray);
}

$cmsimpleRootVarNew = implode('/', $cmsimpleRootArray) . '/';


define('CMSIMPLE_ROOT', $cmsimpleRootVarNew);
define('CMSIMPLE_BASE', $pth['folder']['base']);


// prepare $subsite_folder
if(CMSIMPLE_ROOT == '/')
{
	$cmsimpleRootReplace = '';
}
else
{
	$cmsimpleRootReplace = CMSIMPLE_ROOT;
}

$subsite_folder = str_replace($cmsimpleRootReplace,'',$_SERVER['SCRIPT_NAME']);
$subsite_folder = str_replace('index.php', '', $subsite_folder);
$subsite_folder = trim($subsite_folder, '/') . '/';

// own userfiles folders (if exists and writable) for subsites and second languages
if(is_writable('./userfiles') && is_writable('./userfiles/downloads') && is_writable('./userfiles/images') && is_writable('./userfiles/media') && $pth['folder']['base'] != './')
{
	$userfiles_path = $subsite_folder . $cf['folders']['userfiles'];
	$userfiles_path_downloads = $subsite_folder . $cf['folders']['downloads'];
	$userfiles_path_images = $subsite_folder . $cf['folders']['images'];
	$userfiles_path_media = $subsite_folder . $cf['folders']['media'];
}
else
{
	$userfiles_path = $cf['folders']['userfiles'];
	$userfiles_path_downloads = $cf['folders']['downloads'];
	$userfiles_path_images = $cf['folders']['images'];
	$userfiles_path_media = $cf['folders']['media'];
}


// Userfiles-folders
if(is_writable('./userfiles') && is_writable('./userfiles/downloads') && is_writable('./userfiles/images') && is_writable('./userfiles/media') && $pth['folder']['base'] != './')
{
	$pth['folder']['userfiles'] = './' . $cf['folders']['userfiles'];
	$pth['folder']['downloads'] = './' . $cf['folders']['downloads'];
	$pth['folder']['images'] = './' . $cf['folders']['images'];
	$pth['folder']['media'] = './' . $cf['folders']['media'];
	$pth['folder']['flags'] = $pth['folder']['images'] . 'flags/';
}
else
{
	$pth['folder']['userfiles'] = $pth['folder']['base'] . $cf['folders']['userfiles'];
	$pth['folder']['downloads'] = $pth['folder']['base'] . $cf['folders']['downloads'];
	$pth['folder']['images'] = $pth['folder']['base'] . $cf['folders']['images'];
	$pth['folder']['media'] = $pth['folder']['base'] . $cf['folders']['media'];
	$pth['folder']['flags'] = $pth['folder']['images'] . 'flags/';
}

// 4.5
if(file_exists($pth['folder']['downloads'] . 'XHdebug.txt') && is_writable($pth['folder']['downloads'])){unlink($pth['folder']['downloads'] . 'XHdebug.txt');}
if(file_exists($pth['folder']['downloads'] . '_XHdebug.txt') && is_writable($pth['folder']['downloads'])){unlink($pth['folder']['downloads'] . '_XHdebug.txt');}
// END 4.5


if ($cf['functions']['file'] != "")
{
	include($pth['folder']['cmsimple'] . $cf['functions']['file']);
}

// debug-mode, enables error-reporting
CMSimpleDebugMode();
$errors = array();

// CMSimple 4: define selected language
$slVar = $_SERVER['SCRIPT_NAME'];
$slVarArray = explode('/', $slVar);
array_pop($slVarArray);

if (file_exists('./cmsimplelanguage.htm'))
{
	$sl = array_pop($slVarArray);
}
else
{
	$sl = $cf['language']['default'];
}

// fallback selected language
if (!isset($sl))
{
    $sl = $cf['language']['default'];
}
// END define $sl

$pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';
$pth['folder']['language_default'] = $pth['folder']['cmsimple'] . 'languages/';
$pth['file']['language'] = $pth['folder']['language'] . basename($sl) . '.php';
$pth['file']['corestyle'] = $pth['folder']['base'] . 'css/core.css';

if (!file_exists($pth['file']['language'])) 
{
    copy($pth['folder']['language_default'].'default.php', $pth['file']['language']);
}

if (!file_exists($pth['file']['language']) && !file_exists($pth['folder']['language_default'].'default.php')) 
{
    die('Language file ' . $pth['file']['language'] . ' missing');
}

include $pth['folder']['language_default'] . 'default.php';
include $pth['file']['language'];

if(file_exists('./templates'))
{
	$pth['folder']['templates'] = './templates/';
}
else
{
	$pth['folder']['templates'] = $pth['folder']['base'] . 'templates/';
}
$pth['folder']['template'] = $pth['folder']['templates'] . $cf['site']['template'] . '/';
$pth['file']['template'] = $pth['folder']['template'].'template.htm';
$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';


// template fallback
if (!is_readable($pth['file']['template']) || $pth['folder']['template'] == $pth['folder']['templates'].'__maintenance__/') 
{
	$pth['folder']['template'] = $pth['folder']['templates'].'__fallback__/';
	$pth['file']['template'] = $pth['folder']['template'].'template.htm';
	$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
	$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
}
// END template fallback

// fallback template text 1-9
$txc['template']['text1'] = $tx['template']['text1'];
$txc['template']['text2'] = $tx['template']['text2'];
$txc['template']['text3'] = $tx['template']['text3'];
$txc['template']['text4'] = $tx['template']['text4'];
$txc['template']['text5'] = $tx['template']['text5'];
$txc['template']['text6'] = $tx['template']['text6'];
$txc['template']['text7'] = $tx['template']['text7'];
$txc['template']['text8'] = $tx['template']['text8'];
$txc['template']['text9'] = $tx['template']['text9'];
// END fallback template text 1-9

$pth['folder']['plugins'] = $pth['folder']['base'] . $cf['plugins']['folder'] . '/';

$iis = strpos(sv('SERVER_SOFTWARE'), "IIS");
$cgi = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi');


// create $sn variable (2014)
$sn = preg_replace('/index.php/i', '', $_SERVER['SCRIPT_NAME']);

foreach (array('download', 'function', 'media', 'search', 'mailform', 'sitemap', 'text', 'selected', 'login', 'logout', 'settings', 'print', 'retrieve', 'file', 'action', 'validate', 'images', 'downloads', 'edit', 'normal', 'stylesheet', 'passwd', 'userfiles', 'xhpages')as $i)
    initvar($i);

// define su - selected url
$su = '';
if (sv('QUERY_STRING') != '') 
{
    $rq = explode('&', sv('QUERY_STRING'));
    if (!strpos($rq[0], '='))
        $su = $rq[0];
    $v = count($rq);
    for ($i = 0; $i < $v; $i++)
        if (!strpos($rq[$i], '='))
            $GLOBALS[$rq[$i]] = 'true';
}
else
{
    $su = $selected;
}

if (!isset($cf['uri']['length']))
{
    $cf['uri']['length'] = 200;
}
$su = substr($su, 0, $cf['uri']['length']);

if ($stylesheet != '') 
{
    header("Content-type: text/css");
    include($pth['file']['stylesheet']);
    exit;
}

if ($download != '')
{
    download($pth['folder']['downloads'] . basename($download));
}

$pth['file']['login'] = $pth['folder']['cmsimple'] . 'login.php';
$pth['file']['adm'] = $pth['folder']['cmsimple'] . 'adm.php';
$pth['file']['search'] = $pth['folder']['cmsimple'] . 'search.php';
$pth['file']['mailform'] = $pth['folder']['cmsimple'] . 'mailform.php';

$adm = 0;
$f = '';

if (!@include($pth['file']['login']))
{
    if ($login)
    {
        e('missing', 'file', $pth['file']['login']);
    }
}

$cl = 0;

// maintenance mode warning
if($adm && $cf['site']['content_visible'] != 'true')
{
	$o.= '<p class="cmsimplecore_warning" style="text-align: center; font-weight: 700; margin: 0;">' . $tx['message']['maintenance_backend'] . '</p>';
}

rfc(); // Here content is loaded

if ($function == 'search')
    $f = 'search';
if ($mailform || $function == 'mailform')
    $f = 'mailform';
if ($sitemap)
    $f = 'sitemap';
if ($xhpages)
    $f = 'xhpages';

if (file_exists($pth['folder']['cmsimple'] . 'userfuncs.php')) 
{
	include($pth['folder']['cmsimple'] . 'userfuncs.php');
}


// $txc fallback for Plugins
$txc['site']['title'] = $cf['site']['title'];
$txc['subsite']['template'] = $cf['site']['template'];
$txc['meta']['keywords'] = $cf['meta']['keywords'];
$txc['meta']['description'] = $cf['meta']['description'];
$txc['mailform']['email'] = $cf['mailform']['email'];
$txc['mailform']['captcha'] = $cf['mailform']['captcha'];

// fallback for TinyMCE toolbar
$plugin_cf['tinymce']['init'] = $cf['editor']['tinymce_toolbar'];


// Create plugins array
$handle_pluginlist = opendir($pth['folder']['plugins']);

while ($pluginlist_item = readdir($handle_pluginlist)) 
{
	if (strpos($pluginlist_item, '.') === false && $pluginlist_item != 'pluginloader' && is_dir($pth['folder']['plugins'] . $pluginlist_item)) 
	{
		$pluginlist_array[] = $pluginlist_item;
	}
}
closedir($handle_pluginlist);
sort($pluginlist_array);

$plugins = $pluginlist_array;


// update message CMSimple and plugins
if($cf['site']['allow_versionsinfo'] != 'true' && @$sysinfo && $adm)
{
	$o.= '<p class="cmsimplecore_warning" style="float: left;">' . $tx['sysinfo']['version_info_disabled'];
	$o.= '&nbsp;- <a href="./?file=config&action=array"><b>' . $tx['sysinfo']['version_info_configlink'] . '</b></a>';
	$o.= '</p><br /><br />';
}

if
(
	!@$sysinfo && 
	$adm && 
	$file == 'config' || 
	$file == 'language' || 
	$file == 'template' || 
	$file == 'stylesheet' || 
	isset($cmsimple_pluginmanager)
)
{
	$o.= '<p id="update_message"><a href="./?&sysinfo"><b>' . $tx['sysinfo']['version_info'] . '</b></a>';
	$o.= '</p>
<p style="width: 100%; float: left;"> </p><br />';
}

// Plugin loading
if ($function == 'save') 
{
    $edit = true;
}
if ($cf['plugins']['folder'] != "")
    include($pth['folder']['plugins'] . 'index.php');

if ($f == 'search')
    @include($pth['file']['search']);
	
if ($f == 'mailform') 
{
	if ($cf['mailform']['email'] != '') 
	{
		include($pth['file']['mailform']);
	} 
	else 
	{
		shead(404);
	}
} 
	
if ($f == 'sitemap') 
{
    $title = $tx['title'][$f];
    $ta = array();
    $o .= '<h1>' . $title . '</h1>' . "\n";
    for ($i = 0; $i < $cl; $i++)
        if (!hide($i) || $cf['hidden']['pages_sitemap'] == 'true')
            $ta[] = $i;
    $o .= li($ta, 'sitemaplevel');
}

// Compatibility for DHTML menus, moved from functions.php to cms.php
$si = -1;
$hc = array();
for ($i = 0; $i < $cl; $i++) 
{
    if (!hide($i) || ($i == $s && $cf['hidden']['pages_toc'] == 'true'))
        $hc[] = $i;
    if ($i == $s)
        $si = count($hc);
}
$hl = count($hc);
//END Compatibility for DHTML menus

// LEGAL NOTICES - no needed under GPL3
if (@$cf['menu']['legal'] == '')
    $cf['menu']['legal'] = 'CMSimple Legal Notices';

if ($su == uenc($cf['menu']['legal'])) 
{
    $f = $title = $cf['menu']['legal'];
    $s = -1;
    $o .= '<h1>' . $title . '</h1>' . rf($pth['folder']['cmsimple'] . 'legal.txt');
}

if (!include($pth['file']['adm'])) 
{
    if ($login)
        e('missing', 'file', $pth['file']['adm']);
    if ($s == -1 && !$f && $o == '' && $su == '')
        $s = 0;
}

// CMSimple scripting
if (!($edit && $adm) && $s > -1) 
{
    $c[$s] = evaluate_cmsimple_scripting($c[$s]);
    if (isset($keywords))
	$cf['meta']['keywords'] = $keywords;
    if (isset($description))
	$cf['meta']['description'] = $description;
}

if ($s == -1 && !$f && $o == '')
{
    shead('404');
}

if (function_exists('loginforms'))
    loginforms();

foreach (array('content', 'pagedata', 'config', 'language', 'stylesheet', 'template', 'log') as $i)
    chkfile($i, (($login || $settings) && $adm));
if ($e)
    $o = '<div class="cmsimplecore_warning cmsimplecore_center">' . "\n" . '<b>' . $tx['heading']['warning'] . '</b>' . "\n" . '</div>' . "\n" . '<ul>' . "\n" . $e . '</ul>' . "\n" . $o;

if ($title == '') 
{
    if ($s > -1)
        $title = $h[$s];
    else if ($f != '')
        $title = ucfirst($f);
}

if ($retrieve) 
{
    echo '<html><head>' . head() . '</head><body class="retrieve">' . $c[$s] . '</body></html>';
    exit;
}

if ($print && $cf['site']['content_visible'] == 'true') 
{
    echo '<!DOCTYPE html>' . "\n";
    echo '<head>' . "\n" . head(), '<meta name="robots" content="noindex">' . "\n" . '</head>' . "\n" . '
<body class="cmsimplecore_print"', onload(), '>';

    if($cf['site']['printview_with_backlink'] == 'true')
    {
        echo '<div class="cmsimplecore_printinfo">
<p>' . $cf['meta']['author'] . '</p>
<p><b>URL:</b> <a href="' . $sn . '?' . str_replace('&print','',$_SERVER['QUERY_STRING']) . '">' . $_SERVER['SERVER_NAME'] . $sn . '?' . str_replace('&print','',$_SERVER['QUERY_STRING']) . '</a></p>
</div>';
    }

    echo content(), '</body>' . "\n" . '</html>' . "\n";
    exit;
}

ob_start('final_clean_up');
$cmsimple_debugMode = CMSimpleDebugMode();

// pluginmanager (added) - Array $active_plugins 

$active_plugins = array();
$handle = opendir($pth['folder']['plugins']);

if ($handle) 
{
    while ($plugin = readdir($handle)) 
	{
        if (strpos($plugin, '.') === false 
		&& !stristr(file_get_contents($pm_datafile_path), '|||'.$plugin.'|||'))
		{
			$active_plugins[] = $plugin;
        }
    }
    closedir($handle);
}
sort($active_plugins);

// END pluginmanager (added) - Array $active_plugins 

// activate maintenance template
if($adm != 1 && $cf['site']['content_visible'] != 'true')
{
	$pth['folder']['template'] = $pth['folder']['templates'].'__maintenance__/';
	$pth['file']['template'] = $pth['folder']['template'].'template.htm';
	$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
	$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
	$cf['meta']['robots'] = 'noindex, nofollow';
}

// activate backend template
if
(
	($adm && $cf['use']['backend_template'] == 'true' && 
		(
			isset($sysinfo) || 
			isset($cmsimple_pluginmanager) || 
			(isset($_REQUEST['images']) && $cf['filebrowser']['show_images_permanent'] == 'true')||
			$file == 'language' || 
			$file == 'config' || 
			$file == 'template' || 
			$file == 'stylesheet' || 
			$file == 'log' || 
			@$admin == 'plugin_config' || 
			@$admin == 'plugin_language' || 
			@$admin == 'plugin_stylesheet' || 
			isset($_GET['userfiles']) || 
			isset($_GET['images'])
		)
	)
	|| 
	(
		$adm && !$normal && $cf['use']['backend_template_always'] == 'true'
	)
)
{
	$pth['folder']['template'] = $pth['folder']['templates'].'__cmsimple_backend__/';
	$pth['file']['template'] = $pth['folder']['template'].'template.htm';
	$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
	$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
	$cf['meta']['robots'] = 'noindex, nofollow';
}

// activate filebrowser template
if
($adm && isset($_GET['userfiles']))
{
	$pth['folder']['template'] = $pth['folder']['templates'].'__cmsimple_filebrowser__/';
	$pth['file']['template'] = $pth['folder']['template'].'template.htm';
	$pth['file']['stylesheet'] = $pth['folder']['template'].'stylesheet.css';
	$pth['folder']['menubuttons'] = $pth['folder']['template'].'menu/';
	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
	$cf['meta']['robots'] = 'noindex, nofollow';
}


$poweredLinkCheck = '
<div style="width: 314px; background: #fc0; color: #000; font-size: 14px; font-family: arial, sans-serif; border: 3px solid #000; padding: 6px 20px; position: fixed; z-index: 9999;">
<p><img src="userfiles/images/flags/en.gif">&nbsp; <b>Wrong CMS Link detected in template!</b></p>
<p>Please <b>remove</b> following strings from your template (template.htm):</p>
<p>-xh &nbsp;&nbsp;&nbsp; _XH &nbsp;&nbsp;&nbsp; XH &nbsp;&nbsp;&nbsp; xh</p>
<p>Please <b>replace</b> following strings in your template (template.htm):</p>
<p>cmsimple.name => cmsimple.org<br>cmsimple.eu => cmsimple.org</p>
<p>Without or with a correct CMS link (cmsimple.org), this message will disappear.</p>
<p><img src="userfiles/images/flags/de.gif">&nbsp; <b>Falscher CMS Link im Template erkannt!</b></p>
<p>Bitte <b>entfernen</b> Sie folgende Zeichenketten aus Ihrem Template (template.htm):</p>
<p>-xh &nbsp;&nbsp;&nbsp; _XH &nbsp;&nbsp;&nbsp; XH &nbsp;&nbsp;&nbsp; xh</p>
<p>Bitte <b>ersetzen</b> Sie folgende Zeichenketten aus Ihrem Template (template.htm):</p>
<p>cmsimple.name => cmsimple.org<br>cmsimple.eu => cmsimple.org</p>
<p>Ohne oder mit einem korrekten CMS Link (zu cmsimple.org) wird diese Meldung nicht mehr erscheinen.</p>
</div>
';

if(preg_match('/CMSimple_XH|cmsimple-xh|CMSimple XH|&nbsp;XH|cmsimple\.name|cmsimple\.eu/i',file_get_contents($pth['file']['template'])))
{
	echo($poweredLinkCheck);
}

// template fallback
if (!include($pth['file']['template'])) 
{
	echo '<!DOCTYPE html>
<html>
<head>';
	echo head();
	echo '</head>';
	echo '<body>
<div style="width: 480px; margin: 0 auto;">';
	echo '<div style="background: #eee; color: #c00; font-size: 20px; letter-spacing: 2px; text-align: center; font-weight: 700; border: 5px solid #c00; padding: 24px; margin: 20px 0;">
<p><b>!!! Template is missing !!!</b></p>' 
. loginlink() . '</div>';
	echo content();
	echo '</div>
</body>
</html>';
}

?>