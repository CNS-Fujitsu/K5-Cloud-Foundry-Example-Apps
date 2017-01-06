<?php

if (!defined('CMSIMPLE_VERSION') || preg_match('#/filebrowser/admin.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* utf-8 marker: äöü */

if (!$adm || $cf['filebrowser']['external']) {
    return true;
}

initvar('filebrowser');

function filebrowserSafeQS($querystring)
{
	return(htmlspecialchars(strip_tags($querystring), ENT_QUOTES, 'UTF-8'));
}

if ($filebrowser) {
    $plugin = basename(dirname(__FILE__));
    $plugin = basename(dirname(__FILE__), "/");
	$o .= '<div class="plugintext">
<div class="plugineditcaption">
Filebrowser for CMSimple
</div>
<hr />
<p>' . $tx['message']['plugin_standard1'] . '</p><p>' . $tx['message']['plugin_standard2'] . ' <a href="./?file=config&amp;action=array"><b>' . $tx['filetype']['config'] . '</b></a></p>
<hr />
<p>Author: <a href="http://zeichenkombinat.de/" target="_blank">Martin Damken</a></p>
<p>Adapted for CMSimple 4.0 and higher by <a href="http://www.ge-webdesign.de/" target="_blank">ge-webdesign.de</a></p>
</div>';
    return;
}

if (!($images || $downloads || $userfiles || $media)) {
    return true;
}

if (strstr(@$_GET['subdir'],'userfiles/images/')) {
    $f = 'images';
}

if (strstr(@$_GET['subdir'],'userfiles/downloads/')) {
    $f = 'downloads';
}

if ($userfiles && !strstr(@$_GET['subdir'],'userfiles/images/') && !strstr(@$_GET['subdir'],'userfiles/downloads/') && !strstr(@$_GET['subdir'],'userfiles/media/')) {
    $f = 'userfiles';
}

if (strstr(@$_GET['subdir'],'userfiles/media/')) {
    $f = 'media';
}

$browser = $_SESSION['fb_browser'];
define('XHFB_PATH', $pth['folder']['plugins'] . 'filebrowser/');
$hjs .= '<script type="text/javascript" src="' . XHFB_PATH . 'js/filebrowser.js"></script>';

$subdir = isset($_GET['subdir']) ? str_replace(array('..', '.'), '', $_GET['subdir']) : '';

if (strpos($subdir, $browser->baseDirectories['userfiles']) !== 0) {
    $subdir = $browser->baseDirectories[$f];
}

$browser->baseDirectory = $browser->baseDirectories['userfiles'];
$browser->currentDirectory = filebrowserSafeQS(rtrim($subdir, '/')) . '/';
$browser->linkType = $f;
$browser->setLinkParams('userfiles');

if (isset($_POST['deleteFile']) && isset($_POST['file'])) {
    $browser->deleteFile($_POST['file']);
}
if (isset($_POST['deleteFolder']) && isset($_POST['folder'])) {
    $browser->deleteFolder($_POST['folder']);
}
if (isset($_POST['upload'])) {
    $browser->uploadFile();
}
if (isset($_POST['createFolder'])) {
    $browser->createFolder();
}
if (isset($_POST['renameFile'])) {
    $browser->renameFile();
}

$browser->readDirectory();

$o .= $browser->render('cmsbrowser');

$f = 'filebrowser';
$images = $downloads = $userfiles = $media = false;
/*
 * EOF filebrowser/admin.php
 */
 
?>