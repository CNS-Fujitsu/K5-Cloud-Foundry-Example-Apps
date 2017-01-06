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


if (preg_match('/functions.php/i', $_SERVER['SCRIPT_NAME']))
	die('Access Denied');

// #CMSimple functions to use within content

function geturl($u) 
{
	$t = '';
	if ($fh = @fopen(preg_replace("/\&amp;/is", "&", $u), "r")) 
	{
		while (!feof($fh))
			$t .= fread($fh, 1024);
		fclose($fh);
		return preg_replace("/.*<body[^>]*>(.*)<\/body>.*/is", "\\1", $t);
	}
}

function geturlwp($u) 
{
	global $su;
	$t = '';
	if ($fh = @fopen(($u . '?' . preg_replace("/^" . preg_replace("/\+/s", "\\\+", preg_replace("/\//s", "\\\/", $su)) . "(\&)?/s", "", sv('QUERY_STRING'))), "r")) 
	{
		while (!feof($fh))
			$t .= fread($fh, 1024);
		fclose($fh);
		return $t;
	}
}

function autogallery($u) 
{
	global $su;
	return preg_replace("/.*<!-- autogallery -->(.*)<!-- \/autogallery -->.*/is", "\\1", preg_replace("/(option value=\"\?)(p=)/is", "\\1" . $su . "&\\2", preg_replace("/(href=\"\?)/is", "\\1" . $su . amp(), preg_replace("/(src=\")(\.)/is", "\\1" . $u . "\\2", geturlwp($u)))));
}

function h($n) 
{
	global $h;
	return $h[$n];
}

function l($n) 
{
	global $l;
	return $l[$n];
}


/**
 * Returns $__text with CMSimple scripting evaluated.
 *
 * @param string $__text
 * @param bool $__compat  Wether only last CMSimple script should be evaluated.
 * @return string
 */
function evaluate_cmsimple_scripting($__text, $__compat = TRUE) 
{
	global $output;
	foreach ($GLOBALS as $__name => $__dummy) {global $$__name;}

	$__scope_before = NULL;
	$__scripts = array();
	preg_match_all('~'.$cf['scripting']['regexp'].'~is', $__text, $__scripts);
	if (count($__scripts[1]) > 0) 
	{
		//$output = preg_replace('~'.$cf['scripting']['regexp'].'~is', '', $__text);
		$output = preg_replace('~#CMSimple (?!hide)(.*?)#~is', '', $__text);
		if ($__compat) 
		{
			$__scripts[1] = array_reverse($__scripts[1]);
		}
		foreach ($__scripts[1] as $__script) 
		{
			if (strtolower($__script) !== 'hide' && strtolower($__script) !== 'remove') 
			{
                $__script = preg_replace(
				array("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(apos|#39);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'%anchor%'", "'%hashtag%'"),
				array("\"", "&", "'", "<", ">", " ", "#", "#"),
				$__script);
				$__scope_before = array_keys(get_defined_vars());
				eval($__script);
				$__scope_after = array_keys(get_defined_vars());
				$__diff = array_diff($__scope_after, $__scope_before);
				foreach ($__diff as $__var) 
				{
					$GLOBALS[$__var] = $$__var;
				}
				if ($__compat) 
				{
				break;
				}
			}
		}
		$eval_script_output = $output;
		$output = '';
		return $eval_script_output;
	}
	return $__text;
}


/**
 * Returns $__text with all plugin calls evaluatated.
 * see plugins/index.php preCallPlugins()
 *
 * @param string $__text
 * @return string
 */
function evaluate_plugincall($__text) 
{
	global $u;

	$__text = preg_replace('|{{(.*)#CMSimple(.*)}}|i','<p><span class="cmsimplecore_warning">CMSimple Scripting in user input detected!</span></p>',$__text);
	
	$error = ' <span style="color:#5b0000; font-size:14px;">
	{{CALL TO:<span style="color:#c10000;">{{%1}}</span> FAILED}}</span> '; //use this for debugging of failed plugin-calls
	
	$pl_regex = '"{{{RGX:CALL(.*?)}}}"is'; //general CALL-RegEx (Placeholder: "RGX:CALL")
	
	$pl_calls = array('PLUGIN:' => 'return {{%1}}', 'function:' => 'return {{%1}}');

	$fd_calls = array();
	
	foreach ($pl_calls AS $regex => $call) 
	{
		preg_match_all(str_replace("RGX:CALL", $regex, $pl_regex), $__text, $fd_calls[$regex]); //catch all PL-CALLS
		foreach ($fd_calls[$regex][0] AS $call_nr => $replace) 
		{
			$call = str_replace("{{%1}}", $fd_calls[$regex][1][$call_nr], $pl_calls[$regex]);
			$fnct_call = preg_replace('"(?:(?:return)\s)*(.*?)\(.*?\);"is', '$1', $call);
			$fnct = function_exists($fnct_call) ? TRUE : FALSE; //without object-calls; functions-only!!
			if ($fnct) 
			{
				preg_match_all("/\\$([a-z_0-9]*)/i", $call, $matches);
				foreach ($matches[1] as $var) 
				{
					global $$var;
				}
			}
			
			$__text = str_replace($replace,
			($fnct
			? eval(str_replace('{{%1}}', $fd_calls[$regex][1][$call_nr], $pl_calls[$regex]))
			: str_replace('{{%1}}', $regex . $fd_calls[$regex][1][$call_nr], $error)),
			$__text); //replace PL-CALLS (String only!!)
		}
	}

    return $__text;
}


/**
 * Returns $text with CMSimple scripting and plugin calls evaluated.
 *
 * @param string $text
 * @param bool $compat  Wheter only last CMSimple script will be evaluated.
 * @return void
 */
function evaluate_scripting($text, $compat = TRUE) 
{
	return evaluate_cmsimple_scripting(evaluate_plugincall($text), $compat);
}


/**
 * Returns content of the first CMSimple page with the heading $heading
 * with the heading removed and all scripting evaluated.
 * Returns FALSE, if the page doesn't exist.
 *
 * @param string $heading
 * @return mixed
 */
function newsbox($heading) 
{
	global $c, $cl, $h, $cf, $edit, $su, $adm, $txc;
	for ($i = 0; $i < $cl; $i++) 
	{
		if ($h[$i] == $heading) 
		{
			$body = preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/isu", "", $c[$i]);
			return $edit ? $body : preg_replace("/".$cf['scripting']['regexp']."/is", "", evaluate_scripting($body, FALSE));
		}
	}
	return FALSE;
}


// EDITOR CALL

function init_editor($elementClasses = array(),  $initFile = false)
{
	global $pth, $cf;
	if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) 
	{
		return false;
	}
    include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
    $function = 'init_' . $cf['editor']['external'];

    if (!function_exists($function))
	{
		return false;
	}
	$function($elementClasses, $initFile);
	return true;
}

function include_editor()
{
	global $pth, $cf;
	if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) 
	{
		return false;
	}
	include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
	$function = 'include_' . $cf['editor']['external'];

	if (!function_exists($function))
	{
		return false;
	}

	$function();
	return true;
}

function editor_replace($elementID = false, $config = '')
{
	global $pth, $cf;

	if(!$elementID) 
	{
		trigger_error('No elementID given', E_USER_NOTICE);
		return false;
	}

	if (!file_exists($pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php')) 
	{
		return false;
	}
	include_once $pth['folder']['plugins'] . $cf['editor']['external'] . '/init.php';
	$function = $cf['editor']['external'] . '_replace';

	if (!function_exists($function))
	{
		return false;
	}
	return $function($elementID, $config);
}

function final_clean_up($html) 
{
    global $adm, $s, $o, $cmsimple_debugMode, $plugins, $errors, $cf, $bjs, $file;

    if ($adm === true) 
    {
        $debugHint = '';
        $errorList = '';
        $margin = 34;
        $style = '';

        if ($cmsimple_debugMode) 
        {
            $debugHint .= '<div class="cmsimplecore_debug">' . "\n" . '<b>Notice:</b> Debug-Mode is enabled!' . "\n" . '</div>' . "\n";
            $margin += 25;
        }

        global $errors;
        if(count($errors) > 0)
        {

            $errorList .= '
                <div class="cmsimplecore_warning" style="margin: 0; border-width: 0;">
                  <ul>
                  ';
            $errors =  array_unique($errors);
            foreach($errors as $error){
                $errorList .= '<li>' . $error . '</li>';
            }
            $errorList .= '</ul></div>';
        }
        if (isset($cf['adminmenu']['scroll']) && $cf['adminmenu']['scroll'] == 'true')
        {
            $style = ' style="z-index: 9999;"';
            $margin = 0;
        }
        else 
        {
             $style =' style="position: fixed; top: 0; left: 0; width: 100%; z-index: 9990;"';
             $html = preg_replace('~</head>~','<style type="text/css">html {margin-top: ' . $margin . 'px;}</style>' ."\n" . '$0', $html, 1);
        }

        $html = preg_replace('~<body[^>]*>~',
        '$0' . '<div' . $style . '>' . $debugHint. admin_menu($plugins, $cmsimple_debugMode) . '</div>' ."\n" .  $errorList,
        $html, 1);
    }

	if (!empty($bjs) && $file != 'template') 
	{
		if (function_exists('str_ireplace'))
		{
			$html = str_ireplace('</body', $bjs . "\n" . '</body', $html);
		}
		else
		{
			$html = str_replace('</body', $bjs . "\n" . '</body', $html);
			$html = str_replace('</BODY', $bjs . "\n" . '</BODY', $html);
		}
    }
    return $html;
}

// GLOBAL INTERNAL FUNCTIONS

function initvar($name) 
{
    if (!isset($GLOBALS[$name])) 
	{
        if (isset($_GET[$name]))
            $GLOBALS[$name] = $_GET[$name];
        else if (isset($_POST[$name]))
            $GLOBALS[$name] = $_POST[$name];
        else
            $GLOBALS[$name] = @preg_replace("/.*?(" . $name . "=([^\&]*))?.*?/i", "\\2", sv('QUERY_STRING'));
    }
}

function sv($s) 
{
    if (!isset($_SERVER)) 
	{
        global $_SERVER;
        $_SERVER = $GLOBALS['HTTP_SERVER_VARS'];
    }
    if (isset($_SERVER[$s]))
        return $_SERVER[$s];
    else
        return'';
}

function rmnl($t) 
{
    return preg_replace("/(\r\n|\r|\n)+/", "\n", $t);
}

function rmanl($t) 
{
    return preg_replace("/(\r\n|\r|\n)+/", "", $t);
}

function stsl($t) 
{
    if (get_magic_quotes_gpc())
        return stripslashes($t); else
        return $t;
}

function download($fl) 
{
    global $sn, $download, $tx;
    if (!is_readable($fl) || ($download != '' && !chkdl($sn . '?download=' . basename($fl)))) 
	{
        global $o, $text_title;
        shead('404');
        $o .= '<p>File ' . $fl . '</p>';
        return;
    } 
	else 
	{
        header('Content-Type: application/save-as');
        header('Content-Disposition: attachment; filename="' . basename($fl) . '"');
        header('Content-Length:' . filesize($fl));
        header('Content-Transfer-Encoding: binary');
        if ($fh = @fopen($fl, "rb")) 
		{
            while (!feof($fh))
                echo fread($fh, filesize($fl));
            fclose($fh);
        }
        exit;
    }
}

function chkdl($fl) 
{
    global $pth, $sn;
    $m = false;
    if (@is_dir($pth['folder']['downloads'])) 
	{
        $fd = @opendir($pth['folder']['downloads']);
        while (($p = @readdir($fd)) == true) 
		{
            if (preg_match("/.+\..+$/", $p)) 
			{
                if ($fl == $sn . '?download=' . $p)
                    $m = true;
            }
        }
        if ($fd == true)
            closedir($fd);
    }
    return $m;
}

function rf($fl) 
{
    $fl = @rp($fl);
    if (!file_exists($fl))
        return;
    clearstatcache();
    if (function_exists('file_get_contents'))
        return file_get_contents($fl);
    else 
	{
        return join("\n", file($fl));
    }
}

function chkfile($fl, $writable) 
{
    global $pth, $tx;
    $t = @rp($pth['file'][$fl]);
    if ($t == '')
        e('undefined', 'file', $fl);
    else if (!file_exists($t))
        e('missing', $fl, $t);
    else if (!is_readable($t))
        e('notreadable', $fl, $t);
    else if (!is_writable($t) && $writable)
        e('notwritable', $fl, $t);
}

function e($et, $ft, $fn) 
{
    global $e, $tx;
    $e .= '<li><b>' . $tx['error'][$et] . ' ' . $tx['filetype'][$ft] . '</b>' . tag('br') . $fn . '</li>' . "\n";
}

function rfc() 
{
    global $c, $cl, $h, $u, $l, $su, $s, $pth, $tx, $edit, $adm, $cf;

    $c = array();
    $h = array();
    $u = array();
    $l = array();
    $empty = 0;
    $duplicate = 0;

    $content = file_get_contents($pth['file']['content']);
	$content = preg_replace('/<\/html>/isu', '', $content);
    $stop = $cf['menu']['levels'];

    $content = preg_split('/(?=<h[1-' . $stop . '])/i', $content);
    $content[] = preg_replace('/(.*?)<\/body>(.*?)/isu', '$1', array_pop($content));
    array_shift($content);

    foreach ($content as $page) 
    {
        $c[] = $page;
        preg_match('~<h([1-' . $stop . ']).*>(.*)</h~isU', $page, $temp);
        $l[] = $temp[1];
        $temp_h[] = preg_replace('/[ \f\n\r\t\xa0]+/isu', ' ', trim(strip_tags($temp[2])));
    }

    $cl = count($c);
    $s = -1;

    if ($cl == 0) 
    {
        $c[] = '<h1>' . $tx['toc']['newpage'] . '</h1>';
        $h[] = trim(strip_tags($tx['toc']['newpage']));
        $u[] = uenc($h[0]);
        $l[] = 1;
        $s = 0;
        return;
    }

    $ancestors = array();  /* just a helper for the "url" construction:
     * will be filled like this [0] => "Page"
     *                          [1] => "Subpage"
     *                          [2] => "Sub_Subpage" etc.
     */

    foreach ($temp_h as $i => $heading) 
	{
        $temp = trim(strip_tags($heading));
        if ($temp == '') 
		{
            $empty++;
            $temp = $tx['toc']['empty'] . ' ' . $empty;
        }
        $h[] = $temp;
        $ancestors[$l[$i] - 1] = uenc($temp);
        $ancestors = array_slice($ancestors, 0, $l[$i]);
        $url = implode($cf['uri']['seperator'], $ancestors);
        $u[] = substr($url, 0, $cf['uri']['length']);
    }

    foreach ($u as $i => $url) 
	{
        if ($su == $u[$i] || $su == urlencode($u[$i])) 
		{
            $s = $i;
        } // get index of selected page

        for ($j = $i + 1; $j < $cl; $j++) {   //check for duplicate "urls"
            if ($u[$j] == $u[$i]) 
			{
                $duplicate++;
                $h[$j] = $tx['toc']['dupl'] . ' ' . $duplicate;
                $u[$j] = uenc($h[$j]);
            }
        }
    }
    if (!($edit && $adm)) 
	{
        foreach ($c as $i => $j) 
		{
            if (cmscript('remove', $j)) 
			{
                $c[$i] = '#CMSimple hide#';
            }
        }
    }
}

function a($i, $x) 
{
    global $sn, $u, $cf, $adm;
    if ($i == 0 && !$adm) {
        if ($x == '' && $cf['locator']['show_homepage'] == 'true') 
		{
            return '<a href="' . $sn . '?' . $u[0] . '">';
        }
    }
    return isset($u[$i]) ? '<a href="' . $sn . '?' . $u[$i] . $x . '">' : '<a href="' . $sn . '?' . $x . '">';
}

function meta($n) 
{
    global $cf, $print;
    $exclude = array('robots', 'keywords', 'description');
    if ($cf['meta'][$n] != '' && !($print && in_array($n, $exclude)))
        return tag('meta name="' . $n . '" content="' . htmlspecialchars($cf['meta'][$n], ENT_COMPAT, 'UTF-8') . '"') . "\n";
}

function ml($i, $img_src = '') 
{
    global $pth, $tx, $f, $sn;
    $t = '';
    $title = $tx['menu'][$i];
    if ($f != $i) $t .= '<a href="'.$sn.'?'.amp().$i.'">';
    if ($img_src != '') 
	{
        $t .= tag('img title="' . $title . '" src="' . $pth['folder']['template'] . 'images/' . $img_src . '" alt="' . $title . '"');
    }
    else 
	{ 
		$t .= $title; 
	}

    if ($f != $i)$t .= '</a>';
    return $t;
} 

function homelink($img_src = '') 
{
    global $pth, $s, $u;
    $homepage = $u[0];
    $img = tag('img title="' . $homepage . '" src="' . $pth['folder']['template'] . 'images/' . $img_src . '" alt="' . $homepage . '"');
    if ($s == 0) $t = $img;
    else $t = a(0, '') . $img . '</a>';
    return $t;
} 

function uenc($s) 
{
    global $tx;
    if (isset($tx['urichar']['org']) && isset($tx['urichar']['new']))
        $s = str_replace(explode(",", $tx['urichar']['org']), explode(",", $tx['urichar']['new']), $s);
    return str_replace('+', '_', urlencode($s));
}

function rp($p) 
{
    if (@realpath($p) == '')
        return $p;
    else
        return realpath($p);
}

function sortdir($dir) 
{
    $fs = array();
    $fd = @opendir($dir);
    while (false !== ($fn = @readdir($fd))) 
	{
        $fs[] = $fn;
    }
    if ($fd == true)
        closedir($fd);
    @sort($fs, SORT_STRING);
    return $fs;
}

function cmscript($s, $i) 
{
    global $cf;
    return preg_match(preg_replace("/\(\.\*\?\)/", $s, "/" . $cf['scripting']['regexp'] . "/is"), $i);
}

function hide($i) 
{
    global $c, $edit, $adm;
    if ($i < 0) 
	{
        return false;
    }
    return (!($edit && $adm) && cmscript('hide', $c[$i]));
}

// For valid XHTML
function tag($s) 
{
    global $cf;
    $t = '';
    if ($cf['xhtml']['endtags'] == 'true')
        $t = ' /';
    return '<' . $s . $t . '>';
}

function amp() 
{
    global $cf;
    if ($cf['xhtml']['amp'] == 'true')
        return '&amp;';
    else
        return('&');
}

function shead($s) 
{
    global $iis, $cgi, $tx, $cf, $title, $o;
    if ($s == '401')
        header(($cgi || $iis) ? 'status: 401 Unauthorized' : 'HTTP/1.0 401 Unauthorized');
    if ($s == '404') 
	{
		if (function_exists('custom_404')) 
		{
			custom_404();
		} 
		else 
		{
			header(($cgi || $iis) ? 'status: 404 Not Found' : 'HTTP/1.0 404 Not Found');
			$o.= "\n" . '<h4>' . $tx['menu']['sitemap'] . '</h4>';
			$o.= "\n" . '<div id="error404">';
			$o.= "\n" . str_replace('menulevel','sitemaplevel',toc(1,$cf['menu']['levels'],1));
			$o.= "\n" . '</div>';
		}
    }
    $title = $tx['error'][$s];
    $o = '<h1>' . $title . '</h1>' . $o;
}

/**
 * Debug-Mode
 * Check if file "_CMSimpleDebug.txt" exists to turn on debug-mode
 * with default setting E_ERROR | E_USER_WARNING | E_PARSE.
 * Level of debug mode can be adjusted by placing an
 * integer-value within the file using following values:
 *
 * Possible values of $dbglevel:
 *   0 - Turn off all error reporting
 *   1 - Running errors except warnings
 *   2 - Running errors
 *   3 - Running errors + notices
 *   4 - All errors except notices and warnings
 *   5 - All errors except notices
 *   6 - All errors
 *
 * @author Holger
 *
 * @global array $pth CMSimple's pathes
 * @return boolean Returns true/false if error_reporting was enabled or not
 */
function CMSimpleDebugMode() 
{
    global $pth;
    $dbglevel = '';

    # possible values of $dbglevel:
    # 0 - Turn off all error reporting
    # 1 - Running errors except warnings
    # 2 - Running errors
    # 3 - Running errors + notices
    # 4 - All errors except notices and warnings
    # 5 - All errors except notices
    # 6 - All errors

    if (file_exists($pth['folder']['downloads'] . '_CMSimpleDebug.txt')) 
	{
        ini_set('display_errors', 1);
        $dbglevel = rf($pth['folder']['downloads'] . '_CMSimpleDebug.txt');
        if (strlen($dbglevel) == 1) 
		{
            set_error_handler('CMSimpleDebug');

            switch ($dbglevel) 
			{
                case 0: error_reporting(0);
                    break;
                case 1: error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
                    break;
                case 2: error_reporting(E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE);
                    break;
                case 3: error_reporting(E_ERROR | E_WARNING | E_USER_WARNING | E_PARSE | E_NOTICE);
                    break;
                case 4: error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_USER_WARNING));
                    break;
                case 5: error_reporting(E_ALL ^ E_NOTICE);
                    break;
                case 6: error_reporting(E_ALL);
                    break;
                default:
                    error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
            }
        } 
		else 
		{
            error_reporting(E_ERROR | E_USER_WARNING | E_PARSE);
        }
    } 
	else 
	{
        ini_set('display_errors', 0);
        error_reporting(0);
    }
    if (error_reporting() > 0) 
	{
        return true;
    } 
	else 
	{
        return false;
    }
}

function CMSimpleDebug($errno, $errstr, $errfile, $errline, $context)
{
    global $errors;

    if (!(error_reporting() & $errno)) 
	{
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) 
	{
		case E_USER_ERROR:
        $errors[] = "<b>CMSimple-ERROR:</b> [$errno] $errstr <br /> $errfile:$errline<br />\n";
        break;

		case E_USER_WARNING:
        echo "<b>CMSimple WARNING:</b>  $errstr <br /> $errfile: $errline<br />\n";
        break;

		case E_USER_NOTICE:
        $errors[] = "<b>CMSimple-NOTICE:</b> [$errno] $errstr <br />$errfile:$errline<br />\n";
        break;

		case E_WARNING:
        $errors[] = "<b>WARNING:</b> $errno $errstr <br />$errfile:$errline<br />\n";
        break;

		case E_NOTICE:
        $errors[] = "<b>NOTICE:</b> $errstr <br />$errfile:$errline<br />\n";
        break;


		case E_ERROR:
        $errors[] = "<b>ERROR:</b> $errstr <br />$errfile:$errline<br />\n";
        break;

		default:
        echo "Unknown error type: [$errno] $errstr<br />$errfile:$errline<br />\n";
        break;
    }
    return true;
}


// TEMPLATE FUNCTIONS

function head() 
{
    global $title, $cf, $pth, $tx, $sn, $hjs;
    $t = htmlspecialchars($cf['site']['title'], ENT_COMPAT, 'UTF-8');
    if (!empty($t) && !empty($title)) 
    {
        $t .= ' - ';
    }
    $t .= $title; 
    $t = '<title>' . strip_tags($t) . '</title>' . "\n";
    foreach ($cf['meta'] as $i => $k)
	{
        $t .= meta($i);
	}

	if(file_exists('./favicon.ico'))
	{
		$t.=tag('link rel="shortcut icon" type="image/x-icon" href="' . $sn . 'favicon.ico"') . "\n";
	}

    $t = tag('meta http-equiv="content-type" content="text/html; charset=utf-8"') . "\n" . $t;
    return $t . tag('meta name="generator" content="' . CMSIMPLE_VERSION . ' ' . CMSIMPLE_RELEASE . ' - www.cmsimple.org"') . "\n" . tag('link rel="stylesheet" href="' . $pth['file']['corestyle'] . '" type="text/css"') . "\n" . tag('link rel="stylesheet" href="' . $pth['file']['stylesheet'] . '" type="text/css"') . "\n" . $hjs;
}

function sitename() 
{
    global $cf;
    return isset($cf['site']['title']) ? $cf['site']['title'] : '';
}

function pagename() 
{
    global $pd_current, $h, $s;
    return($pd_current['show_heading'] == '1' ? $pd_current['heading'] : $h[$s]);
}

function onload() 
{
    global $onload;
    return ' onload="' . $onload . '"';
}

function toc($start = NULL, $end = NULL, $openToc = 0)
{
	global $c, $cl, $s, $l, $cf, $adm, $su;
	if (isset($start)) 
	{
		if (!isset($end))
		{
			$end = $start;
		}
	}
	else
	{
		$start = 1;
	}
	
	if (!isset($end))
	{
		$end = $cf['menu']['levels'];
	}
	
	$ta = array();
	
	if($openToc === 0) // dynamic toc
	{
		if ($s > -1) 
		{
			$tl = $l[$s];
			for ($i = $s; $i > -1; $i--) 
			{
				if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)
				{
					if (!hide($i) || ($i == $s && $cf['hidden']['pages_toc'] == 'true'))
					{
						$ta[] = $i;
					}
				}
				if ($l[$i] < $tl)
				{
					$tl = $l[$i];
				}
			}
			@sort($ta);
			$tl = $l[$s];
		}
		else
		{
			$tl = 0;
		}
		
		$tl += 1 + $cf['menu']['levelcatch'];
		for ($i = $s + 1; $i < $cl; $i++) 
		{
			if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end)
			{
				if (!hide($i))
				{
					$ta[] = $i;
				}
			}
			if ($l[$i] < $tl)
			{
				$tl = $l[$i];
			}
		}
		return li($ta, $start);
	}
	else //open toc
	{
		if ($s > -1) 
		{
			for ($i = $s; $i > -1; $i--) 
			{
				if ($l[$i] >= $start && $l[$i] <= $end)
				{
					if (!hide($i) || ($i == $s && $cf['hidden']['pages_toc'] == 'true'))
					{
						$ta[] = $i;
					}
				}
			}
			@sort($ta);
		}
		
		for ($i = $s + 1; $i < $cl; $i++) 
		{
			if ($l[$i] >= $start && $l[$i] <= $end)
			{
				if (!hide($i))
				{
					$ta[] = $i;
				}
			}
		}
		return li($ta, $start);
	}
}

function expToc()
{
	global $hc;
	return(li($hc, 'menulevel'));
}

function li($ta, $st) 
{
	global $s, $l, $h, $cl, $cf, $u;
	$tl = count($ta);
	if ($tl < 1)
		return;
	$t = '';
	if ($st == 'submenu' || $st == 'search')
		$t .= '<ul class="' . $st . '">' . "\n";
	$b = 0;
	if ($st > 0) 
	{
		$b = $st - 1;
		$st = 'menulevel';
	}
	$lf = array();
	for ($i = 0; $i < $tl; $i++) 
	{
		$tf = ($s != $ta[$i]);
		
		$splitCssClass = str_replace('%','m',uenc($u[$ta[$i]]));
		
		if ($st == 'menulevel' || $st == 'sitemaplevel') 
		{
			for ($k = (isset($ta[$i - 1]) ? $l[$ta[$i - 1]] : $b); $k < $l[$ta[$i]]; $k++)
			{
				$t .= "\n" . '<ul class="' . $st . ($k + 1) . '">' . "\n";
			}
		}
		
		if(strstr($h[$ta[$i]],'_splitToc_'))
		{
			$t.= '<li class="splitToc ' . $splitCssClass . '">' . str_replace('_splitToc_','',$h[$ta[$i]]) . '</li>' . "\n";
			
			for ($k = $l[$ta[$i]]; $k > (isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b); $k--) 
			{
				$t .= '</ul>' . "\n";
			}
		}
		else
		{
			if ($st == 'menulevel') 
			{
				$t .= '<li class="' . $splitCssClass . ' ';
			}
			else
			{
				$t .= '<li class="';
			}
			
			if (!$tf)
				$t .= 's';
			else if (@$cf['menu']['sdoc'] == "parent" && $s > -1) 
			{
				if ($l[$ta[$i]] < $l[$s]) 
				{
					if (@substr($u[$s], 0, strlen($cf['uri']['seperator']) + strlen($u[$ta[$i]])) == $u[$ta[$i]] . $cf['uri']['seperator'])
						$t .= 's';
				}
			}
			$t .= 'doc';
			for ($j = $ta[$i] + 1; $j < $cl; $j++)
				if (!hide($j) && $l[$j] - $l[$ta[$i]] < 2 + $cf['menu']['levelcatch']) 
				{
					if ($l[$j] > $l[$ta[$i]])
						$t .= 's';
					break;
				}
			$t .= '">';
			
			if(!$tf)
			{
				$t.='<span>';
			}
			
			if ($tf)
				$t .= a($ta[$i], '');
			$t .= $h[$ta[$i]];
			
			if ($tf)
			{
				$t .= '</a>';
			}
			else
			{
				$t .= '</span>';
			}
			
			if ($st == 'menulevel' || $st == 'sitemaplevel') 
			{
				if ((isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b) > $l[$ta[$i]])
					$lf[$l[$ta[$i]]] = true;
				else 
				{
					$t .= '</li>' . "\n";
					$lf[$l[$ta[$i]]] = false;
				}
				for ($k = $l[$ta[$i]]; $k > (isset($ta[$i + 1]) ? $l[$ta[$i + 1]] : $b); $k--) 
				{
					$t .= '</ul>' . "\n";
					if (isset($lf[$k - 1]))
					{
						if ($lf[$k - 1]) 
						{
							$t .= '</li>' . "\n";
							$lf[$k - 1] = false;
						}
					}
				}
			}
			else
			{
				$t .= '</li>' . "\n";
			}
		}
	}
	if ($st == 'submenu' || $st == 'search')
	{
		$t .= '</ul>' . "\n";
	}
	return $t;
}

function searchbox() 
{
	global $sn, $tx;
	return '<form action="' . $sn . '" method="get">' . "\n" 
	. '<div id="searchbox">' . "\n" 
	. tag('input type="text" class="text" name="search" size="12"') . "\n" 
	. tag('input type="hidden" name="function" value="search"') . "\n" . ' ' 
	. tag('input type="submit" class="submit" value="' . $tx['search']['button'] . '"') . "\n" 
	. '</div>' . "\n" . '</form>' . "\n";
}

function sitemaplink($img_src = '') 
{
    return ml('sitemap', $img_src);
} 

function printlink($img_src = '') 
{
    global $f, $search, $file, $sn, $tx, $pth;
    $t = amp().'print';
    $title = $tx['menu']['print'];

    if ($f == 'search')
        $t .= amp() . 'function=search' . amp() . 'search=' . htmlspecialchars(stsl($search));
    else if ($f == 'file')
        $t .= amp() . 'file=' . $file;
    else if ($f != '' && $f != 'save')
        $t .= amp() . $f;
    else if (sv('QUERY_STRING') != '')
        $t = htmlspecialchars(sv('QUERY_STRING'), ENT_QUOTES, "UTF-8") . $t;
	
	$link = '';

    if ($img_src != '') 
	{
        $link = tag('img title="' . $title . '" src="' . $pth['folder']['template'] . 'images/' . $img_src . '" alt="' . $title . '"');
    }
    else 
	{ 
		$link .= $title; 
	}

    return '<a href="'.$sn.'?'.$t.'">' . $link . '</a>';
} 

function mailformlink($img_src = '') 
{
    global $cf;
    if ($cf['mailform']['email'] != '')return ml('mailform', $img_src);
}

function guestbooklink(){}

function loginlink()
{
    global $cf, $adm, $sn, $u, $s, $tx;
    if (!$adm) 
	{
		return a($s > -1 ? $s : 0, '&amp;login') . $tx['menu']['login'] . '</a>';
    }
}

function lastupdate($br = NULL, $hour = NULL) {
    global $tx, $pth;
    $t = $tx['lastupdate']['text'] . ':';
    if (!(isset($br)))
        $t .= tag('br');
    else
        $t .= ' ';
    return $t . date($tx['lastupdate']['dateformat'], filemtime($pth['file']['content']) + (isset($hour) ? $hour * 3600 : 0));
}

function legallink() {}

function locator() 
{
    global $title, $h, $s, $f, $c, $l, $tx, $adm, $cf;
	if (hide($s) && $cf['hidden']['path_locator'] != 'true')
		return str_replace('_splitToc_','',$h[$s]);
	if ($title != '' && (!isset($h[$s]) || $h[$s] != $title))
		return str_replace('_splitToc_','',$title);
	$t = '';
	if ($s == 0)
		return str_replace('_splitToc_','',$h[$s]);
	elseif ($f != '')
		return ucfirst($f);
	elseif ($s > 0) {
		$tl = $l[$s];
		if ($tl > 1) {
			for ($i = $s - 1; $i >= 0; $i--) {
				if ($l[$i] < $tl) {
					$t = '<span style="white-space: nowrap;">' . a($i, '') . $h[$i] . '</a> &gt; </span> <span style="white-space: nowrap;">' . $t . '</span> ';
					$tl--;
				}
				if ($tl < 2)
					break;
			}
		}
		if ($cf['locator']['show_homepage'] == 'true') {
		return'<span class="cmsimpleLocatorElement">' .  a(0, '') . $tx['locator']['home'] . '</a> &gt; </span> ' . $t . ' <span class="cmsimpleLocatorElement">' . str_replace('_splitToc_','',$h[$s]) . '</span> ';
		} 
		else 
		{
			return $t . str_replace('_splitToc_','',$h[$s]);
		}
	}
	else
	{
		return '&nbsp;';
	}
}

function editmenu() 
{
    return '';
}

function admin_menu($plugins = array(), $debug = false)
{
	global $adm, $edit, $s, $u, $sn, $tx, $sl, $cf, $su, $pth, $disabled_plugins, $active_plugins, $userfiles_path_images, $userfiles_path_downloads, $userfiles_path_media; // pluginmanager (added "$pth, $disabled_plugins")

	if ($adm)
	{
		$pluginMenu = '';
		if ((bool) $plugins)
		{
		
// pluginmanager (changed)

			$pluginMenu .= '<li>';
			
			$pluginMenu .= '<a href="?&amp;normal&amp;cmsimple_pluginmanager">' . ucfirst($tx['adminmenu']['plugins']) . '</a>' . "\n";
			
			if(count($active_plugins) > $cf['adminmenu']['narrow_max'])
			{
				$pluginMenu .= '<ul style="width: 460px; margin-left: -325px;">';
			}
			else
			{
			$pluginMenu .= '<ul>';
			}

			foreach ($active_plugins as $plugin)
			{
				if(count($active_plugins) > $cf['adminmenu']['narrow_max'])
				{
					if(!stristr($disabled_plugins, '§'.$plugin.'§') && strpos($plugin, 'pluginloader') === false )
					{
						$pluginMenu .= "\n" . '     <li style="width: 150px; float: left;"><a href="?' . $plugin . '&amp;normal">' . $plugin . '</a></li>';
					}
				}
				else
				{
					if(!stristr($disabled_plugins, '§'.$plugin.'§') && strpos($plugin, 'pluginloader') === false )
					{
						$pluginMenu .= "\n" . '     <li><a href="?' . $plugin . '&amp;normal">' . $plugin . '</a></li>';
					}
				}
			}
			$pluginMenu .= "\n" . '</ul>';

// END pluginmanager (changed)
		}


		$t .= "\n" . '<div id="adminmenu">';

		$t .= "\n" . '<ul id="admin_menu">' . "\n";

		if ($s < 0)
		{
			$su = $u[0];
		}
		$changeMode = $edit ? 'normal' : 'edit';
		$changeText = $edit ? ucfirst($tx['adminmenu']['normal']) : ucfirst($tx['adminmenu']['edit']);

		$t.= '<li><a href="' . $sn . '?' . $su . '&amp;' . $changeMode . '">' . $changeText . '</a></li>
<li><a href="' . $sn . '?&amp;pagemanager&amp;edit" class="">' . ucfirst($tx['adminmenu']['pagemanager']) . '</a></li>
<li><a href="' . $sn . '?&amp;normal&amp;userfiles">' . ucfirst($tx['adminmenu']['files']) . '</a>
    <ul>
    <li><a href="' . $sn . '?userfiles&amp;subdir=' . $userfiles_path_images . '">' . ucfirst($tx['adminmenu']['images']) . '</a></li>
    <li><a href="' . $sn . '?userfiles&amp;subdir=' . $userfiles_path_downloads . '">' . ucfirst($tx['adminmenu']['downloads']) . '</a></li>
    <li><a href="' . $sn . '?userfiles&amp;subdir=' . $userfiles_path_media . '">' . ucfirst($tx['adminmenu']['media']) . '</a></li>
    </ul>
</li>
<li><a href="' . $sn . '?&amp;settings">' . ucfirst($tx['adminmenu']['settings']) . '</a>
    <ul>
';

		if($cf['site']['full_settings_menu'] == 'true') // CoAuthors
		{
				$t .='    <li><a href="?file=config&amp;action=array">' . ucfirst($tx['adminmenu']['configuration']) . '</a></li>
    <li><a href="?file=language&amp;action=array">' . ucfirst($tx['adminmenu']['language']) . '</a></li>
    <li><a href="?file=template&amp;action=edit">' . ucfirst($tx['adminmenu']['template']) . '</a></li>
    <li><a href="?file=stylesheet&amp;action=edit">' . ucfirst($tx['adminmenu']['stylesheet']) . '</a></li>
    <li><a href="?file=log&amp;action=view" target="_blank">' . ucfirst($tx['adminmenu']['log']) . '</a></li>
';
		}

		$t .='    <li><a href="' . $sn . '?&amp;validate">' . ucfirst($tx['adminmenu']['validate']) . '</a></li>
    <li><a href="' . $sn . '?&amp;sysinfo">' . ucfirst($tx['adminmenu']['sysinfo']) . '</a></li>
    </ul>
</li>
' . $pluginMenu . '
</li>
</ul>
<ul id="adminmenu_logout">
<li id="admin_menu_logout"><a href="?&amp;logout">' . ucfirst($tx['adminmenu']['logout']) . '</a></li>
</ul>
';

		return $t . '<div style="float:none;clear:both;padding:0;margin:0;width:100%;height:0px;"></div>' . "\n" . '</div>' . "\n";
	}
}

function sortArrayLength($x,$y)
{
	return strlen($y) - strlen($x);
}

function content($cmsimple_highlight_bg = NULL, $cmsimple_highlight_tx = NULL) 
{
    global $s, $o, $c, $edit, $adm, $cf, $tx;
	if (!($edit && $adm) && $s > -1) 
	{
		if (isset($_GET['search'])) 
		{
			$words = explode(',', urldecode($_GET['search']));
			$words = array_map(create_function('$w', 'return "&".$w."(?!([^<]+)?>)&isU";'), $words);
			usort($words, 'sortArrayLength');
 
			if($cmsimple_highlight_bg && $cmsimple_highlight_tx)
			{
				$c[$s] = preg_replace($words, '<span style="background: ' . $cmsimple_highlight_bg . '; color: ' . $cmsimple_highlight_tx . ';">\\0</span>', $c[$s]);
			}
			else
			{
				$c[$s] = preg_replace($words, '<span class="highlight_search">\\0</span>', $c[$s]);
			}
		}
		return $o . preg_replace("/" . $cf['scripting']['regexp'] . "/is", "", $c[$s]);
	}
	else 
	{
		return $o;
	}
}

function submenu() 
{
	global $s, $cl, $l, $tx, $cf, $adm;
	$ta = array();
	if ($s > -1) {
		$tl = $l[$s] + 1 + $cf['menu']['levelcatch'];
		for ($i = $s + 1; $i < $cl; $i++) {
			if ($l[$i] <= $l[$s])
				break;
			if ($l[$i] <= $tl)
				if (!hide($i))
					$ta[] = $i;
			if ($l[$i] < $tl)
				$tl = $l[$i];
		}
		if (count($ta) != 0)
		{
			return '<h4>' . $tx['submenu']['heading'] . '</h4>' . "\n" . li($ta, 'submenu');
		}
	}
}

function previouspage($cmsimplePrevIcon='') 
{
	global $s, $cl, $tx, $adm, $pth;
	for ($i = $s - 1; $i > -1; $i--)
		if (!hide($i))
		{
			if(isset($cmsimplePrevIcon) && $cmsimplePrevIcon != '')
			{
				return a($i, '') . '<img src="' . $pth['folder']['templateimages'] . $cmsimplePrevIcon . '" alt="" title="' . $tx['navigator']['previous'] . '">' . '</a>';
			}
			else
			{
				return a($i, '') . $tx['navigator']['previous'] . '</a>';
			}
		}
}

function nextpage($cmsimpleNextIcon='') 
{
	global $s, $cl, $tx, $adm, $pth;
	for ($i = $s + 1; $i < $cl; $i++)
		if (!hide($i))
		{
			if(isset($cmsimpleNextIcon) && $cmsimpleNextIcon != '')
			{
				return a($i, '') . '<img src="' . $pth['folder']['templateimages'] . $cmsimpleNextIcon . '" alt="" title="' . $tx['navigator']['next'] . '">' . '</a>';
			}
			else
			{
				return a($i, '') . $tx['navigator']['next'] . '</a>';
			}
		}
}

function top($cmsimpleTopIcon='') 
{
	global $tx, $adm, $pth;
	
	if(isset($cmsimpleTopIcon) && $cmsimpleTopIcon != '')
	{
		return '<a href="#TOP"><img src="' . $pth['folder']['templateimages'] . $cmsimpleTopIcon . '" alt="" title="' . $tx['navigator']['top'] . '">' . '</a>';
	}
	else
	{
		return '<a href="#TOP">' . $tx['navigator']['top'] . '</a>';
	}
}

function languagemenu() 
{
	global $pth, $cf, $sl, $sn;
	
	$lang_short_temp = explode(",", $cf['language']['short']);
	foreach($lang_short_temp as $lang_short_element){$lang_short[] = '|' . $lang_short_element . '|';}
	$lang_long = explode(",", stripslashes(strip_tags($cf['language']['long'])));
	
	$mainLanguage = './';
	if(file_exists('./cmsimplelanguage.htm')){$mainLanguage = '../';}
	
	$t = '';
	
	// Zweitsprachen Array erzeugen
	$r = array();
	$fd = opendir($mainLanguage);
	while (($p = @readdir($fd)) == true ) 
	{
		if ($p != '..' && $p != '.' && is_dir($mainLanguage . $p)) 
		{
			if (file_exists($mainLanguage . $p . '/cmsimplelanguage.htm') && $p != '2lang' && $p != '2site2lang')
			{
				$r[] = '|' . $p . '|';
			}
		}
	}
	sort($r);
	if ($fd == true)closedir($fd); 
	if(count($r) == 0)return ''; 
	
	// Link zur Hauptsprache in Zweitsprachen
	if($cf['language']['default'] != $sl) 
	{
		if (is_file($pth['folder']['flags'] . '/' . $cf['language']['default'] . '.gif')) 
		{
			$t .= "\n" . '<a href="' . $mainLanguage . '">' . tag('img src="' . $pth['folder']['flags'] . $cf['language']['default'] . '.gif" class="cmsimple_language_flag flag" alt="' . str_replace('|','',str_replace($lang_short,$lang_long,'|' . $cf['language']['default'] . '|')).'" title="&nbsp;' . str_replace('|','',str_replace($lang_short,$lang_long,'|' . $cf['language']['default'] . '|')) . '&nbsp;"').'</a> '; 
		}
		else
		{
			$t .= "\n" . '<a href="' . $mainLanguage . '"><span class="cmsimple_language_menuitem">' . str_replace('|','',str_replace($lang_short,$lang_long,'|' . $cf['language']['default'] . '|')) . '</span></a> ';
		}
	}
	
	// Links zu Zweitsprachen
	$v = count($r); 
	for($i = 0;$i < $v;$i++)
	{
		if ($sl != str_replace('|','',$r[$i]))
		{
			if (is_file($pth['folder']['flags'] . '/' . str_replace('|','',$r[$i]) . '.gif')) 
			{
				$t.= "\n" . '<a href="' . $mainLanguage . str_replace('|','',$r[$i]).'/">' . tag('img src="' . $pth['folder']['flags'] . str_replace('|','',$r[$i]) . '.gif" class="cmsimple_language_flag flag" alt="' . str_replace('|','',str_replace($lang_short,$lang_long,$r[$i])) . '" title="&nbsp;' . str_replace('|','',str_replace($lang_short,$lang_long,$r[$i])) . '&nbsp;"') . '</a> ';
			} 
			else
			{
				$t .= "\n" . '<a href="' . $mainLanguage . str_replace('|','',$r[$i]).'/"><span class="cmsimple_language_menuitem">' . str_replace('|','',str_replace($lang_short,$lang_long,$r[$i])) . '</span></a> ';
			}
		}
	}
	$t.= "\n";
	return ''.$t.'';
}

?>