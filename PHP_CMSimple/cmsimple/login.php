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

global $pth;

if (preg_match('/login.php/i', $_SERVER['SCRIPT_NAME']) || preg_match('/\/2author\//i', $sn) || preg_match('/\/2lang\//i', $sn) || preg_match('/\/2site\//i', $sn) || preg_match('/\/2sit2lang\//i', $sn))
	die('Access Denied');

require $pth['folder']['cmsimple'] . 'PasswordHash.php'; 
$xh_hasher = new PasswordHash(8, true);

if(isset($cf['security']['type']))
{
	unset($cf['security']['type']);
}

function gc($s) 
{
	if (!isset($_COOKIE)) 
	{
		global $_COOKIE;
		$_COOKIE = $GLOBALS['HTTP_COOKIE_VARS'];
	}
	if (isset($_COOKIE[$s]))
		return $_COOKIE[$s];
}

function logincheck() 
{
	global $cf;
	return (gc('passwd') == $cf['security']['password']);
}

function writelog($m) 
{
	global $pth, $e;
	
	$newLogfilePHP = '<?php // utf-8 marker: äöü
if(!defined(\'CMSIMPLE_VERSION\') || preg_match(\'/log.php/i\', $_SERVER[\'SCRIPT_NAME\'])){die(\'No direct access\');} ?>
==============================
';
	
	$logfileContent = str_replace($newLogfilePHP,'',file_get_contents($pth['file']['log']));
	
	if ($fh = @fopen($pth['file']['log'], "w")) 
	{
		fwrite($fh,$newLogfilePHP . $m . $logfileContent);
		fclose($fh);
	}
}

function loginforms() 
{
	global $adm, $cf, $print, $hjs, $tx, $onload, $f, $o, $s, $sn, $u;

	if ($f == 'login') 
	{
		$cf['meta']['robots'] = "noindex";
		$onload .= "self.focus();document.login.passwd.focus();";
		$f = $tx['menu']['login'];
		$o .= '<div id="cmsimple_loginform" class="cmsimple_loginform">
<h1>' . $tx['menu']['login'] . '</h1>
<div style="padding: 0 0 24px 0; font-weight: 700;">
' . str_replace(tag('br').tag('br').tag('br').tag('br'),tag('br').tag('br'), str_replace("\r",tag('br'),str_replace("\n",tag('br'),$tx['login']['warning']))) . '
</div>
<form id="login" name="login" action="' . $sn . '?' . $u[$s] . '" method="post">' . "\n"
 . tag('input type="hidden" name="login" value="true"') . "\n"
 . tag('input type="hidden" name="selected" value="' . @$u[$s] . '"') . "\n" . 'User (optional): ' . tag('br')
 . tag('input type="text" name="user" id="user" value=""') . ' ' . "\n" . tag('br') . 'Password: ' . tag('br')
 . tag('input type="password" name="passwd" id="passwd" value=""') . ' ' . "\n" . tag('br') . tag('br')
 . tag('input type="submit" name="submit" id="submit" value="' . $tx['menu']['login'] . '"') . '
</form>
</div>';
		$s = -1;
	}
}

// LOGIN & BACKUP

$adm = (gc('status' . str_replace('.','xc6oMd3Rs689',str_replace('index.php','',$sn))) == 'adm' && logincheck());

if ($login && $passwd == '' && !$adm) 
{
	$login = null;
	$f = 'login';
}

if ($login && !$adm) 
{
	if ($xh_hasher->CheckPassword($passwd, $cf['security']['password']))
	{
		setcookie('status' . str_replace('.','xc6oMd3Rs689',str_replace('index.php','',$sn)), 'adm', 0, CMSIMPLE_ROOT);
		setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
		setcookie('passwd', $cf['security']['password'], 0);
		$adm = true;
		$edit = true;
		writelog(date("Y-m-d H:i:s") . " from " . sv('REMOTE_ADDR') . " logged_in: $sn" . ' - "' . strip_tags($_POST['user']) ."\"\n");
	}
	else
	{
		writelog(date("Y-m-d H:i:s")." from ".sv('REMOTE_ADDR')." login failed: $sn ##### \"" . strip_tags($_POST['user']) . "\" ##### \n");
		shead('401');
	}
} 
else if ($logout && $adm) 
{
	$backupDate = date("Ymd_His");
	$fn = $backupDate . '_content.php'; // 4.5
	if (@copy($pth['file']['content'], $pth['folder']['content'] . $fn)) 
	{
		$o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
		$fl = array();
		$fd = @opendir($pth['folder']['content']);
		while (($p = @readdir($fd)) == true) 
		{
			if (preg_match("/\d{3}\_content.php/", $p) || preg_match("/\d{3}\_content.htm/", $p)) // 4.5
				$fl[] = $p;
		}
		if ($fd == true)
			closedir($fd);
		@sort($fl, SORT_STRING);
		$v = count($fl) - $cf['backup']['numberoffiles'];
		for ($i = 0; $i < $v; $i++) 
		{
			if (@unlink($pth['folder']['content'] . '/' . $fl[$i]))
				$o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
			else
				e('cntdelete', 'backup', $fl[$i]);
		}
	}
	else
	{
		e('cntsave', 'backup', $fn);
	}

// SAVE function for pagedata.php added

	if (file_exists($pth['folder']['content'] . 'pagedata.php')) 
	{
		$fn = $backupDate . '_pagedata.php';
		if (@copy($pth['file']['pagedata'], $pth['folder']['content'] . $fn)) 
		{
			$o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fn . ' ' . $tx['result']['created'] . '</p>';
			$fl = array();
			$fd = @opendir($pth['folder']['content']);
			while (($p = @readdir($fd)) == true) 
			{
				if (preg_match("/\d{3}\_pagedata.php/", $p))
					$fl[] = $p;
			}
			if ($fd == true)
				closedir($fd);
			@sort($fl, SORT_STRING);
			$v = count($fl) - $cf['backup']['numberoffiles'];
			for ($i = 0; $i < $v; $i++) 
			{
				if (@unlink($pth['folder']['content'] . $fl[$i]))
					$o .= '<p>' . ucfirst($tx['filetype']['backup']) . ' ' . $fl[$i] . ' ' . $tx['result']['deleted'] . '</p>';
				else
					e('cntdelete', 'backup', $fl[$i]);
			}
		}
		else
		{
			e('cntsave', 'backup', $fn);
		}
	}

// END save function for pagedata.php


    $adm = false;
	setcookie('status' . str_replace('.','xc6oMd3Rs689',str_replace('index.php','',$sn)), '', 0, CMSIMPLE_ROOT);
	setcookie('status', '', 0, CMSIMPLE_ROOT);
	setcookie('passwd', '', 0);
	$o .= '<p class="cmsimplecore_warning" style="text-align: center; font-weight: 700; padding: 8px;">' . $tx['login']['loggedout'] . '</p>';
}

// SETTING FUNCTIONS AS PERMITTED

if ($adm) 
{
    if ($edit)
        setcookie('mode', 'edit', 0);
    if ($normal)
        setcookie('mode', '', 0);
    if (gc('mode') == 'edit' && !$normal)
        $edit = true;
} 
else 
{
    if (gc('status' . str_replace('.','xc6oMd3Rs689',str_replace('index.php','',$sn))) != '')
        setcookie('status' . str_replace('index.php','',$sn), '', 0);
    if (gc('status') != '')
        setcookie('status', '', 0, CMSIMPLE_ROOT);
    if (gc('passwd') != '')
        setcookie('passwd', '', 0);
    if (gc('mode') == 'edit')
        setcookie('mode', '', 0);
}
?>