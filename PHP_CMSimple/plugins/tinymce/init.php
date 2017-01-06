<?php 

/*
============================================================
CMSimple Plugin TinyMCE4
============================================================
Version:    TinyMce4 v2.0
Released:   11/2015
============================================================
utf-8 check: äöü
*/

if (!defined('CMSIMPLE_VERSION') || preg_match('#/plugins/tinymce/init.php#i', $_SERVER['SCRIPT_NAME']))
{
    die('no direct access');
}

// utf-8-marker: äöüß
function tinymce4_getInternalLinks()
{
    global $h, $u, $l, $pth;
    $downloadPath = $pth['folder']['downloads'];

    $items     = array();
    global $tx;

    $pages    = '{title : "' . $tx['adminmenu']['pagemanager'] . '", menu : [';
    $pageList = array();
    for ($i = 0; $i < count($h); $i++)
    {
        $spacer = '';
		
        if ($l[$i] > 1)
        {
            $spacer = str_repeat(' >', $l[$i] - 1);  // just for indenting lower level "pages"
        }
		else
		{
			$spacer = '##### ';  // just for mark level1 pages
		}
        
		$title = addcslashes(html_entity_decode($spacer . '&nbsp;' . $h[$i]), "\n\r\t\"\'\\");

        $pageList[] = "{title : '{$title}', value : '?{$u[$i]}'}";
    }

    $pages .= implode(', ', $pageList) . ']}';
    $items[] = $pages;
    if (is_dir($downloadPath))
    {
        $downloads = '{title : "DOWNLOADS", menu : [';
        $dlList    = array();
        $fs        = sortdir($downloadPath);
        foreach ($fs as $p)
        {
            if (preg_match("/.+\..+$/", $p))
            {
                $dlList[] = '{title : "' . substr($p, 0, 25) . ' (' . (round(filesize($downloadPath . '/' . $p) / 102.4) / 10) . ' KB)", value : "./?download=' . $p . '"}';
            }
        }
        $downloads .= implode(', ', $dlList) . ']}';
        $items[] = $downloads;
    }


    return '[' . implode(', ', $items) . ']';
}

function include_tinymce4()
{
    global $pth, $hjs, $adm;
    static $loaded = 0;
    if (!$loaded)
    {
        $cmsUrl = CMSIMPLE_ROOT . 'plugins/filebrowser/editorbrowser.php?editor=tinymce&prefix=' . CMSIMPLE_BASE . '&base=./';

        if ($adm)
        {
            $hjs .= '<script src="' . $pth['folder']['plugins'] . 'tinymce/' . 'tinymce/tinymce.min.js"></script>
<script src="' . $pth['folder']['plugins'] . 'tinymce/' . 'filebrowser.js"></script>
<script>function getCmsUrl(){return "' . $cmsUrl . '";}</script>
';
        }
        $loaded++;
    }
}

/*
Returns the config object.
@return string
*/

function tinymce4_config($config = NULL)
{
    global $pth, $cf, $sl, $adm;

    if ($config)
    {
        $options = array();
        foreach (glob(dirname(__FILE__) . '/inits/init_*.js') as $file)
        {
            $options[substr(basename($file, '.js'), 5)] = $file;
        }

        if (key_exists($config, $options))
        {
            $config = file_get_contents($options[$config]);
        } 
		else
        {
            $config .= "var tinyConfig = {$config};";
        }
    } 
	else
    {
        $tinyMode = isset($cf['editor']['tinymce_toolbar']) && file_exists($pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $cf['editor']['tinymce_toolbar'] . '.js')
                ? $cf['editor']['tinymce_toolbar']
                : 'full';
        $initFile = $pth['folder']['plugins'] . 'tinymce/' . 'inits/init_' . $tinyMode . '.js';
        $config    = file_get_contents($initFile);
    }

    $lang = file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tinymce/langs/' . $sl . '.js')
            ? $sl
            : (file_exists($pth['folder']['plugins'] . 'tinymce/' . 'tinymce/langs/' . $cf['language']['default'] . '.js')
                    ? $cf['language']['default']
                    : 'en');

    $cssFile  = $pth['folder']['template'] . 'stylesheet.css';
    $linkList = tinymce4_getInternalLinks();

    $script = "{$config}"
            . "tinyConfig.language = '$lang';"
            . "tinyConfig.content_css = '{$cssFile}';";
    if ($adm)
    {
        $script .= "tinyConfig.link_list = {$linkList};"
                . "tinyConfig.file_browser_callback = tinyFileBrowser;";
    }
    return $script;
}

function tinymce4_replace($elementID = false, $config = '{}')
{
    if (!$elementID)
    {
        return '';
    }
    include_tinymce4();
    $temp = tinymce4_config($config);

    echo '<script>' . $temp . ';new tinymce.Editor("' . $elementID . '", tinyConfig, tinymce.EditorManager).render();</script>';
}

function init_tinymce($classes = array(), $config = false)
{
    global $hjs, $onload;

    include_tinymce4();

    array_unshift($classes, 'cmsimple-editor');
    $script      = tinymce4_config($config);
    $initClasses = array();

    foreach ($classes as $class)
    {
        $initClasses[] = '.' . trim($class, '.');
    }

    $script .= "tinyConfig.selector = '" . implode(',', $initClasses) . "';";

    $hjs .= "
<script>{$script}tinymce.init(tinyConfig);
</script>

";
}
?>