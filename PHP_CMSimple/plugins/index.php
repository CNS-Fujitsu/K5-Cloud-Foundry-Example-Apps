<?php

if (!defined('CMSIMPLE_VERSION') || preg_match('#/plugins/index.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* utf8-marker = äöüß */
/*
=========================================================
MODIFIED FOR CMSimple 4.1 by ge-webdesign.de 2013-03
$pluginloader_cfg['folder_down'] => $pth['folder']['base']

MODIFIED FOR CMSimpleSubsites by ge-webdesign.de 2012-09
Search modifications by "CMSimpleSubsites"

MODIFIED FOR CMSimpleCoAuthors by ge-webdesign.de 
Moved styles of standard plugins to core.css 2012-07

MODIFIED FOR PLUGINMANAGER by ge-webdesign.de 2012-05
Search modifications by "pluginmanager ("
=========================================================

 * Pluginloader Version 2.1 beta 12 (V.2.1.12)
 * For the usage in CMSimple up from version V.2.6 unto V.3.2
 * Handles loading of pluginloader-2.0 and -2.1 compatible plugins.
 *
 * Created after discussion at CMSimpleforum.com with:
 * Martin, mvwd, Till, johnjdoe, Holger and Gert in May 2009.
 * 
 * @author Developer-Team at CMSimpleforum.com
 * @link http://www.cmsimpleforum.com
 * @version 2.1.11
 * @package pluginloader
 *
 * Download: http://www.cmsimplewiki.com/doku.php/pluginloader/start
 * 
 * Modified after a long discussion at CMSimple forum with Martin, mvwd, 
 * Till, johnjdoe, Holger and Gert (Mai 2009)
 * Changes:
 * - The pluginloader contains a new subfolder "page_data" which holds 
 *   synchronised page based data for new plugins like the "meta_tags" 
 *   and "page_params" plugins, written by Martin
 * - OOP-written plugins can load a "required_classes.php" from a new 
 *   plugin subfolder "classes"
 * - Order of loading plugins changed: 1. classes, 2. config, 3. languages, 
 *   4. index, 5. css
 * - Direct access check changed (code mentioned by Martin & mvwd)
 * - For more security, a new constant "PLUGINLOADER_VERSION" included:
 *   Plugins can check the Pluginloader version for compatibility or to 
 *   perform a direct access check with code like below
 *       if(!defined('PLUGINLOADER_VERSION') or !constant('PLUGINLOADER_VERSION'))
 *        die('Direct access not allowed!');
 *       if(PLUGINLOADER_VERSION < 2.101) die('Your Pluginloader is outdated!');
 * - Plugin-Pre-Call: can be used for calling a plugin without CMSimple-Scripting.
 *   Comes with following improvements:
 *   - no limitations in size of page (instead of scripting gets not parsed in 
 *     pages >100kB).
 *   - checks if function is available, prints out an error-message (instead of 
 *     website is dying) or simply nothing.
 *   - use multiple plugins on one page (instead of just use one script per page).
 *   - place your plugin wherever you want in content (instead of plugins will be 
 *     returned by standard on bottom of page).
 *   - you can use the classic CMSimple-Scripting AND pluginPreCall.
 * 
 * Plugin Loader version 2.0 beta 11 for CMSimple v2.6, v2.7 . . . 3.0, 3.2 .....
 * Modified by Till after discussion in the German CMSimple forum with Holger and 
 * Gert (October 2008)
 * 
 * Edited by © Jan Neugebauer (December, 1st 2006) 
 * http://www.internet-setup.de/cmsimple/
 * 
 * Based on original script by © Michael Svarrer 
 * http://cmsimpleplugins.svarrer.dk
 * 
 * Updated 22. September 2006 by Peter Andreas Harteg and released under the CMSimple 
 * license in the CMSimple distrubution with permission by copyright holders.
 * 
 * For licence see notice in /cmsimple/cms.php and http://www.cmsimple.com/?Licence
 * 
 * 
 * Usage: This plugin loader will load compatible plugins avaliable under 
 *        the plugins folder into CMSimple.
 * Step 1 - Create a folder for the plugins (here we call it: "plugins")
 * Step 2 - Upload this pluginloader-package to the newly created folder. 
 * Step 3 - In /cmsimple/config.php set $cf['plugins']['folder']="plugins"; 
 *          (or set plugins_folder to plugins under CMSimple configuration settings)
 * Step 4 - Installation is done and you should be ready to install plugins
 *          Install plugins in an extra subfolder per plugin within the folder "plugins"
 */
/**
 * Catch all neccessary CMSimple-Variables as global
 *
 * @global array $cf CMSimple's Config-Array
 * @global string $hjs CMSimple's head-/javascript-section
 * @global string $sl CMSimple's selected language
 * @global string $sn CMSimple's script-name (relative adress including $_GET)
 */

global $cf, $pth, $hjs, $sl, $sn, $title, $pm_datafile_path, $disabled_plugins; // pluginmanager (added ", $pm_datafile_path, $disabled_plugins")

define('PLUGINLOADER', TRUE);
define('PLUGINLOADER_VERSION', 4.1);

// pluginmanager (added)

if(!file_exists('./disabled_plugins.txt'))
{
	$pm_datafile_path = $pth['folder']['base'] . 'disabled_plugins.txt';
}
else
{
	$pm_datafile_path = './disabled_plugins.txt';
}

if(file_exists($pm_datafile_path))
{
	$disabled_plugins = file_get_contents($pm_datafile_path);
}
else
{
	$disabled_plugins = ('');
}

// END pluginmanager (added)

/**
 * Debug-Mode
 * - first: turn off for compatibility with the "original" CMSimple
 * - second: call CMSimpleDebugMode(), which checks if debug was activated
 * @author Holger
 */
ini_set('display_errors', 0);
error_reporting(0);
if (function_exists('CMSimpleDebugMode')) 
{
    CMSimpleDebugMode();
}

/**
 * @global array $pluginloader_cfg Plugin-Loader's Config-Array
 */
global $pluginloader_cfg;
$pluginloader_cfg = array();
$pluginloader_tx = array();

if (!isset($hjs)) 
{
    $hjs = '';
}

if (!isset($cf['plugins']['folder']) OR empty($cf['plugins']['folder']) OR !is_dir($cf['plugins']['folder'])) 
{
    $cf['plugins']['folder'] = 'plugins';
}

$pluginloader_cfg['foldername_pluginloader'] = 'pluginloader';
$pluginloader_cfg['folder_pluginloader'] = $pth['folder']['base'] . $cf['plugins']['folder'] . '/' . $pluginloader_cfg['foldername_pluginloader'] . '/';
$pluginloader_cfg['folder_css'] = $pluginloader_cfg['folder_pluginloader'] . 'css/';
$pluginloader_cfg['folder_languages'] = $pluginloader_cfg['folder_pluginloader'] . 'languages/';
$pluginloader_cfg['file_css'] = $pluginloader_cfg['folder_css'] . 'stylesheet.css';
$pluginloader_cfg['form_namespace'] = 'PL3bbeec384_';


// If pluginloader language is missing, copy default.php or en.php
if (!file_exists($pluginloader_cfg['folder_languages'] . $sl . '.php')) 
{
	if (file_exists($pluginloader_cfg['folder_languages'] . 'default.php')) 
	{
		copy($pluginloader_cfg['folder_languages'] . 'default.php', $pluginloader_cfg['folder_languages'] . $sl . '.php');
	} 
	elseif 
	(file_exists($pluginloader_cfg['folder_languages'] . 'en.php')) 
	{
		copy($pluginloader_cfg['folder_languages'] . 'en.php', $pluginloader_cfg['folder_languages'] . $sl . '.php');
	}
}

// pluginloader language settings (main, second languages and subsites)

if(file_exists($pluginloader_cfg['folder_languages'] . $sl . '.php')) 
{
	$pluginloader_cfg['language'] = $sl;
} 
else 
{
	$pluginloader_cfg['language'] = 'en';
}

// END pluginloader language settings (main, second languages and subsites)

// Load default plugin language
if (file_exists($pth['folder']['plugins'] . $pluginloader_cfg['foldername_pluginloader'] . '/languages/default.php')) 
{
	include $pth['folder']['plugins'] . $pluginloader_cfg['foldername_pluginloader'] . '/languages/default.php';
}

// include Plugin Loader language file
$pluginloader_cfg['file_language'] = $pluginloader_cfg['folder_languages'] . $pluginloader_cfg['language'] . '.php';
if (!include_once($pluginloader_cfg['file_language'])) 
{
    echo 'Language file for Plugin Loader not found!' . tag('br') . 'File: ' . $pluginloader_cfg['file_language'] . tag('br') . 'Current Path: ' . $_SERVER['PHP_SELF'] . tag('hr');
}

/**
 * If admin is logged in, create an array with all plugins, having admin.php
 */
if ($adm) 
{
    $i = 0;
    $handle = opendir($pth['folder']['plugins']);
    $found_plugins = false;

    while (FALSE !== ($plugin = readdir($handle))) 
	{
        if ($plugin != '.' && $plugin != '..' && is_dir($pth['folder']['plugins'] . $plugin)) 
		{
            PluginFiles($plugin);
            if (file_exists($pth['file']['plugin_admin'])) 
			{
                $admin_plugins[$i] = $plugin;
                $found_plugins = true;
                $i++;
            }
        }
    }
	closedir($handle);
    natcasesort($admin_plugins);
    $o .= ' ';
}


/**
 * Bridge to page data
 */
require_once $pth['folder']['plugins'] . $pluginloader_cfg['foldername_pluginloader'] . '/page_data/index.php';

/**
 * Include plugin (and plugin files)
 */
 
$handle = opendir($pth['folder']['plugins']);
while (FALSE !== ($plugin = readdir($handle))) 
{
	if ($plugin != "." AND $plugin != ".." AND $plugin != $pluginloader_cfg['foldername_pluginloader'] AND is_dir($pth['folder']['plugins'] . $plugin) && !stristr($disabled_plugins, '|||'.$plugin.'|||')) // pluginmanager (added " && !stristr($disabled_plugins, '|||'.$plugin.'|||')")
	{
        PluginFiles($plugin);

        // Load plugin required_classes
        if (file_exists($pth['file']['plugin_classes']))
		{
            include($pth['file']['plugin_classes']);
        }
    }
}
closedir($handle);

$handle = opendir($pth['folder']['plugins']);
while (FALSE !== ($plugin = readdir($handle))) 
{
    if ($plugin != "." AND $plugin != ".." AND $plugin != $pluginloader_cfg['foldername_pluginloader'] AND is_dir($pth['folder']['plugins'] . $plugin) && !stristr($disabled_plugins, '|||'.$plugin.'|||')) // pluginmanager (added "&& !stristr($disabled_plugins, '|||'.$plugin.'|||')")
	{
        PluginFiles($plugin);

        // Load plugin config
		if (file_exists($pth['folder']['plugins'].$plugin.'/config/defaultconfig.php')) 
		{
	    include($pth['folder']['plugins'].$plugin.'/config/defaultconfig.php');
		}
        if (file_exists($pth['file']['plugin_config'])) 
		{
            include($pth['file']['plugin_config']);
        }
    
		// If plugin language is missing, copy default.php or en.php
		if (!file_exists($pth['file']['plugin_language'])) 
		{
			if (file_exists($pth['folder']['plugins'].$plugin.'/languages/default.php')) 
			{
				copy($pth['folder']['plugins'].$plugin.'/languages/default.php', $pth['file']['plugin_language']);
			} 
			elseif 
			(file_exists($pth['folder']['plugins'].$plugin.'/languages/en.php')) 
			{
				copy($pth['folder']['plugins'].$plugin.'/languages/en.php', $pth['file']['plugin_language']);
			}
		}
    
		// Load default plugin language
		if (file_exists($pth['folder']['plugins'] . $plugin . '/languages/default.php')) 
		{
			include $pth['folder']['plugins'] . $plugin . '/languages/default.php';
		}
        // Load plugin language
        if (file_exists($pth['file']['plugin_language'])) 
		{
            include($pth['file']['plugin_language']);
        }

        // Load plugin index.php or die
        if (file_exists($pth['file']['plugin_index']) AND !include($pth['file']['plugin_index'])) 
		{
            die($pluginloader_tx['error']['plugin_error'] . $pluginloader_tx['error']['cntopen'] . $pth['file']['plugin_index']);
        }

        // Add plugin css to the header of CMSimple/Template
        if (file_exists($pth['file']['plugin_stylesheet'])) 
		{
            $hjs .= tag('link rel="stylesheet" href="' . $pth['file']['plugin_stylesheet'] . '" type="text/css"') . "\n";
        }
    }
}
closedir($handle);


// Load admin functions (admin.php, if exists) of plugin
 
if ($adm) 
{
    if ($found_plugins == true) 
	{
        foreach ($admin_plugins as $plugin) 
		{
            PluginFiles($plugin);
			
// pluginmanager (changed) - prevent loading admin.php for disabled plugins
			
            if (file_exists($pth['file']['plugin_admin']) && !stristr($disabled_plugins, '|||'.$plugin.'|||')) 
			{
                include($pth['file']['plugin_admin']);
            }
			else
			{
				if($_SERVER['QUERY_STRING'] == $plugin.'&normal')
				{
					$o.='<p class="cmsimplecore_warning" style="font-weight: 300; text-align: center;">' . $pluginloader_tx['pluginmanager']['message_plugin_disabled1'] . ' <b>"' . $plugin . '"</b> ' . $pluginloader_tx['pluginmanager']['message_plugin_disabled2'] . '</p>
				<p><a href="./?&amp;normal&cmsimple_pluginmanager"><b>Pluginmanager &raquo;</b></a></p>
				<hr />
				<br />';
				}
			}

// END pluginmanager (changed)

        }
    }
    // ########## bridge to page data ##########
    $o .= $pd_router->create_tabs($s);
    // #########################################
}

// Pre-Call Plugins

preg_replace('|\#CMSimple|i','<span>#</span>CMSimple',preCallPlugins());

// Plugin functions
unset($plugin);

/**
 * Function PluginFiles()
 * Set plugin filenames.
 *
 * @param string $plugin Name of the plugin, the filenames 
 * will be set for.
 *
 * @global array $cf CMSimple's Config-Array
 * @global string $pth CMSimple's configured pathes in an array
 * @global string $sl CMSimple's selected language
 * @global array $pluginloader_cfg Plugin-Loader's Config-Array
 */
function PluginFiles($plugin) 
{
    global $cf, $pth, $sl;
    global $pluginloader_cfg;

    $pth['folder']['plugin'] = $pth['folder']['plugins'] . $plugin . '/';
    $pth['folder']['plugin_classes'] = $pth['folder']['plugins'] . $plugin . '/classes/';

// CMSimpleSubsites
	if(file_exists('./plugins/' . $plugin . '/config/config.php'))
	{
		$pth['folder']['plugin_config'] = './plugins/' . $plugin . '/config/';
	}
	else
	{
		$pth['folder']['plugin_config'] = $pth['folder']['plugins'] . $plugin . '/config/';
	}
	
	if(file_exists('./plugins/' . $plugin . '/languages/' . $sl . '.php'))
	{
		$pth['folder']['plugin_languages'] = './plugins/' . $plugin . '/languages/';
	}
	else
	{
		$pth['folder']['plugin_languages'] = $pth['folder']['plugins'] . $plugin . '/languages/';
	}
	
	if(file_exists('./plugins/' . $plugin . '/css/stylesheet.css'))
	{
		$pth['folder']['plugin_css'] = './plugins/' . $plugin . '/css/';
	}
	else
	{
		$pth['folder']['plugin_css'] = $pth['folder']['plugins'] . $plugin . '/css/';
	}
// END CMSimpleSubsites

    $pth['folder']['plugin_content'] = $pth['folder']['plugins'] . $plugin . '/content/';
    $pth['folder']['plugin_help'] = $pth['folder']['plugins'] . $plugin . '/help/';
    $pth['folder']['plugin_includes'] = $pth['folder']['plugins'] . $plugin . '/includes/';

    $pth['file']['plugin_index'] = $pth['folder']['plugin'] . 'index.php';
    $pth['file']['plugin_admin'] = $pth['folder']['plugin'] . 'admin.php';
    $pth['file']['plugin_language'] = $pth['folder']['plugin_languages'] . $sl . '.php';
    $pth['file']['plugin_classes'] = $pth['folder']['plugin_classes'] . 'required_classes.php';
    $pth['file']['plugin_config'] = $pth['folder']['plugin_config'] . 'config.php';
    $pth['file']['plugin_stylesheet'] = $pth['folder']['plugin_css'] . 'stylesheet.css';
    $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help_' . strtolower($pluginloader_cfg['language']) . '.htm';

    if (!file_exists($pth['file']['plugin_help'])) 
	{
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help_en.htm';
    }
    if (!file_exists($pth['file']['plugin_help']) AND file_exists($pth['folder']['plugin_help'] . 'help.htm')) 
	{
        $pth['file']['plugin_help'] = $pth['folder']['plugin_help'] . 'help.htm';
    }
}

/**
 * Function PluginMenu()
 * Create menu of plugin (add row, add tab), constructed as a table.
 *
 * @param string $add Add a ROW, a TAB or DATA (Userdefineable content). SHOW will return the menu.
 * @param string $link The link, the TAB will lead to.
 * @param string $target Target of the link (with(!) 'target=').
 * @param string $text Description of the TAB.
 * @param array $style Array with style-data for the containing table-cell
 */
function PluginMenu($add='', $link='', $target='', $text='', $style=ARRAY()) 
{
	global $adm;
	if($adm)
	{
		$add = strtoupper($add);

		if (!isset($GLOBALS['pluginloader']['plugin_menu'])) 
		{
			$GLOBALS['pluginloader']['plugin_menu'] = '';
		}
		if (!isset($style['row'])) 
		{
			$style['row'] = 'class="edit" style="width: 100%; border-collapse: collapse;"';
		}
		if (!isset($style['tab'])) 
		{
			$style['tab'] = '';
		}
		if (!isset($style['link'])) 
		{
			$style['link'] = '';
		}
		if (!isset($style['data'])) 
		{
			$style['data'] = '';
		}

		$menu_row = '<table {{STYLE_ROW}}>' . "\n" . '<tr>' . "\n" . '{{TAB}}</tr>' . "\n" . '</table>' . "\n" . "\n";
		$menu_tab = '<td {{STYLE_TAB}}><a{{STYLE_LINK}} href="{{LINK}}" {{TARGET}}>{{TEXT}}</a></td>' . "\n";
		$menu_tab_data = '<td {{STYLE_DATA}}>{{TEXT}}</td>' . "\n";

		// Add new row for menu of plugin (or Plugin Loader)
		if ($add == 'ROW') 
		{
			$new_menu_row = $menu_row;
			$new_menu_row = str_replace('{{STYLE_ROW}}', $style['row'], $new_menu_row);
			$GLOBALS['pluginloader']['plugin_menu'] .= $new_menu_row;
		}

		// Add a new tab to the menu row
		if ($add == 'TAB') 
		{
			$new_menu_tab = $menu_tab;
			$new_menu_tab = str_replace('{{STYLE_TAB}}', $style['tab'], $new_menu_tab);
			$new_menu_tab = str_replace('{{STYLE_LINK}}', $style['link'], $new_menu_tab);
			$new_menu_tab = str_replace('{{LINK}}', $link, $new_menu_tab);
			$new_menu_tab = str_replace('{{TARGET}}', $target, $new_menu_tab);
			$new_menu_tab = str_replace('{{TEXT}}', $text, $new_menu_tab);

			// Add tab to row
			$GLOBALS['pluginloader']['plugin_menu'] = str_replace('{{TAB}}', $new_menu_tab . '{{TAB}}', $GLOBALS['pluginloader']['plugin_menu']);
		}

		// Add a new tab to the menu row
		// Here: user defineable data
		if ($add == 'DATA') 
		{
			$new_menu_tab_data = $menu_tab_data;
			$new_menu_tab_data = str_replace('{{STYLE_DATA}}', $style['data'], $new_menu_tab_data);
			$new_menu_tab_data = str_replace('{{TEXT}}', $text, $new_menu_tab_data);

			// Add tab to row
			$GLOBALS['pluginloader']['plugin_menu'] = str_replace('{{TAB}}', $new_menu_tab_data . '{{TAB}}', $GLOBALS['pluginloader']['plugin_menu']);
		}

		// Show complete menu
		if ($add == 'SHOW') 
		{
			$GLOBALS['pluginloader']['plugin_menu'] = str_replace('{{TAB}}', '', $GLOBALS['pluginloader']['plugin_menu']);
			$menu = $GLOBALS['pluginloader']['plugin_menu'];
			$GLOBALS['pluginloader']['plugin_menu'] = '';
			return $menu;
		}
	}
}

/**
 * Function PluginReadFile()
 * Read content from a given file.
 *
 * @param string $file Name of the file to read.
 *
 * @return array If succesfull, return an array with data and success-message.
 */
function PluginReadFile($file='') 
{
    global $pluginloader_tx;

    $is_read = ARRAY();
    $is_read['success'] = FALSE;
    $is_read['msg'] = '';
    $is_read['content'] = '';

    if (!file_exists($file)) 
	{
        $is_read['msg'] = $pluginloader_tx['error']['cntopen'] . $file;
    } 
	else 
	{
        if (!is_readable($file)) 
		{
            $is_read['msg'] = $pluginloader_tx['error']['notreadable'] . $file;
        } 
		else 
		{
            if ($fh = fopen($file, "rb")) 
			{
                $is_read['content'] = fread($fh, filesize($file));
                fclose($fh);
                $is_read['content'] = str_replace("\r\n", "\n", $is_read['content']);
                $is_read['content'] = str_replace("\r", "\n", $is_read['content']);
                $is_read['msg'] = $pluginloader_tx['success']['saved'] . $file;
                $is_read['success'] = TRUE;
            }
        }
    }
    return $is_read;
}

/**
 * Function PluginWriteFile()
 * Write content to a given file.
 *
 * @global array $pluginloader_tx Plugin-loader's text-array
 *
 * @param string $file Name of the file to write.
 * @param string $content Content, that will be written in the file.
 * @param string $exists If set to TRUE, the function returns error-message if file is not available.
 * @param string $append If set to TRUE, data will be appended to existing data in the file.
 *
 * @return array If succesfull, return an array with success-message.
 */
function PluginWriteFile($file='', $content='', $exists=FALSE, $append=FALSE) 
{
    global $pluginloader_tx;

    if ($append == TRUE) 
	{
        $write_mode = 'a+b';
    } 
	else 
	{
        $write_mode = 'w+b';
    }

    $is_written = ARRAY();
    $is_written['success'] = FALSE;
    $is_written['msg'] = '';

    $do_write = TRUE;

    if (!file_exists($file) AND $exists == TRUE) 
	{
        $do_write = FALSE;
        $is_written['msg'] = $pluginloader_tx['error']['cntopen'] . $file;
    }

    if (file_exists($file) AND !is_writeable($file)) 
	{
        $do_write = FALSE;
        $is_written['msg'] = '<div class="pluginerror">' . $pluginloader_tx['error']['cntwriteto'] . $file . '</div>';
    }

    if ($do_write == TRUE) 
	{
        if ($fh = fopen($file, $write_mode)) 
		{
            fwrite($fh, $content);
            fclose($fh);
            $is_written['msg'] = $pluginloader_tx['success']['saved'] . $file;
            $is_written['success'] = TRUE;
        }
    }
    return $is_written;
}

/**
 * Function PluginPrepareConfigData()
 * Prepare config data for writing to file.
 *
 * @param string $var_name Name of the variable ($cf => $var_name = cf)
 * @param string $data Array of data, that will be converted to text for saving in (php-)textfile.
 * @param string $plugin If filled, the created variable will be extended by a sub-array with this index.
 *
 * @return string Returns the prepared data for saving.
 */
function PluginPrepareConfigData($var_name='', $data=ARRAY(), $plugin='') 
{
    $save_data = "<?php // utf-8 Marker: äöü \n\n"; 
    foreach ($data as $key => $value) 
	{
        $save_data .= "\t" . '$' . $var_name;
        if (!empty($plugin)) 
		{
            $save_data .= '[\'' . $plugin . '\']';
        }
        $save_data .= '[\'' . $key . '\']="' . trim(str_replace("\\'", "'", ((get_magic_quotes_gpc() === 1) ? $value : addslashes($value)))) . '";' . "\n";
    }
    $save_data .= "\n?>";
    return $save_data;
}

/**
 * Function PluginPrepareTextData()
 * Prepare text data for writing to file.
 *
 * @param string $data The text-data, that will be prepared for saving to file.
 *
 * @return string Returns the prepared data for saving.
 */
function PluginPrepareTextData($data) 
{
    return (get_magic_quotes_gpc() === 1) ? stripslashes($data) : $data;
}

/**
 * Function PluginSaveForm()
 * Creates form for config data.
 *
 * If $hint['mode_donotshowvarnames'] == TRUE, variable 
 * indexes are not shown, but text information.
 * (e.g. for index 'my_name' -> $plugin_tx['example_plugin']['cf_my_name'])
 *
 * @global array $pluginloader_tx Plugin-loader's text-array
 *
 * @param array $form Array, that contains data how the form will be created.
 * @param array $style Array, that contains style-data for the div and the form.
 * @param array $data Data, that will be shown in the form (in a textarea or in input-fields).
 * @param array $hint Array with hints in popups for the config-variables.
 *
 * @return string Returns the created form.
 */
function PluginSaveForm($form=ARRAY(), $style=ARRAY(), $data=ARRAY(), $hint=ARRAY()) 
{
    global $pluginloader_tx, $admin, $adm;
	
	if($adm)
	{
		$saveform = '';

		if (!isset($form['type']) OR ($form['type'] != 'TEXT' AND $form['type'] != 'CONFIG')) 
		{
			$saveform .= PluginDebugger('invalid_value', debug_backtrace(), $$data, $form['type']);
		} 
		elseif ($form['type'] == 'CONFIG' AND (!is_array($data) OR count($data) == 0)) 
		{
			$saveform .= PluginDebugger('empty', debug_backtrace(), $$data, '');
		} 
		else 
		{

			$form_keys = ARRAY('action', 'caption', 'errormsg', 'method', 'value_action', 'value_admin', 'value_submit');
			$style_keys = ARRAY('div', 'divcaption', 'form', 'submit', 'table', 'tdcaption', 'tdconfig', 'tdhint', 'textarea', 'input', 'inputmax');
			foreach ($form_keys AS $key) 
			{
				if (!isset($form[$key])) 
				{
					$form[$key] = '';
				}
			}
			foreach ($style_keys AS $key) 
			{
				if (!isset($style[$key])) 
				{
					$style[$key] = '';
				}
			}

			$saveform .= $form['errormsg'];
			$saveform .= '<div ' . $style['div'] . '>' . "\n";

			$saveform .= '<form ' . $style['form'] . ' action="' . $form['action'] . '" method="' . $form['method'] . '">' . "\n";
			
			$pluginLocator = str_replace('plugin_','',$admin);

			if (!empty($form['caption'])) 
			{
				$saveform .= '<div ' . $style['divcaption'] . '>' . "\n" . $form['caption'] . "\n" . '-&nbsp;' . $pluginloader_tx['menu']["tab_$pluginLocator"] . '</div>' . "\n";
			}

			$saveform .= tag('input type="hidden" name="admin" value="' . $form['value_admin'] . '"') . "\n";
			$saveform .= tag('input type="hidden" name="action" value="' . $form['value_action'] . '"') . "\n" . tag('br') . "\n" . tag('input type="submit" ' . $style['submit'] . ' name="plugin_submit" value="' . $form['value_submit'] . '"') . "\n";
			$saveform .= tag('br') . tag('br');

			if ($form['type'] == 'TEXT') 
			{
				$saveform .= '<textarea ' . $style['textarea'] . ' name="' . $form['textarea_name'] . '">' . $data . '</textarea>';
			}

			if ($form['type'] == 'CONFIG') 
			{
				$saveform .= '<table ' . $style['table'] . '>' . "\n";
				$last_cap = '';
				ksort($data);
				foreach ($data as $key => $value) 
				{
					global $pluginloader_cfg;
					$var_name = '';
					$val_cap = explode('_', $key);

					if (!isset($hint['mode_donotshowvarnames']) OR $hint['mode_donotshowvarnames'] == FALSE) 
					{
						if ($val_cap[0] != $last_cap) 
						{
							$last_cap = $val_cap[0];
							$saveform .= '<tr>' . "\n" . '<td colspan="2" ' . $style['tdcaption'] . '>' . $last_cap . '</td>' . "\n" . '</tr>' . "\n";
						}
					}
					if (isset($hint['mode_donotshowvarnames']) AND $hint['mode_donotshowvarnames'] == TRUE AND isset($hint['cf_' . $key]) AND !empty($hint['cf_' . $key])) 
					{
						$var_name = $hint['cf_' . $key];
					} 
					else 
					{
						$var_name = (isset($hint['cf_' . $key]) AND !empty($hint['cf_' . $key])) ? '<a href="#" class="pl_tooltip">' . tag('img src = "' . $pluginloader_cfg['folder_pluginloader'] . 'css/help_icon.png" alt="" class="helpicon"') . '<span style="padding: 10px 9px 12px 9px;">' . str_replace(tag('br').tag('br').tag('br').tag('br'),tag('br').tag('br'), str_replace("\r",tag('br'),str_replace("\n",tag('br'),$hint['cf_' . $key]))) . '</span></a> ' . str_replace("_", " ", $key) . ': ' : str_replace("_", " ", $key) . ':';
					}
					$saveform .= '<tr>' . "\n" . '<td ' . $style['tdconfig'] . '>' . $var_name . '</td>' . "\n" . '<td>';
					$style_textarea = $style['input'];
					if (strlen($value) > 50) 
					{
						$style_textarea = $style['inputmax'];
					}
					if($admin == 'plugin_language')
					{
						$saveform .= '<textarea ' . $style_textarea . ' name="' . $pluginloader_cfg['form_namespace'] . $key . '" rows="1" cols="40">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</textarea>';
					}
					else
					{
						$saveform .= '<input style="width: 99%; height: 24px; font-family: courier new, monospace; font-size: 15px; color: #444;" name="' . $pluginloader_cfg['form_namespace'] . $key . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" />';
					}
					
					$saveform .= '</td>' . "\n" . '</tr>' . "\n";
				}
				$saveform .= '</table>' . "\n" . "\n";
			}

			$saveform .= tag('input type="hidden" name="admin" value="' . $form['value_admin'] . '"') . "\n";
			$saveform .= tag('input type="hidden" name="action" value="' . $form['value_action'] . '"') . "\n" . tag('br') . "\n" . tag('input type="submit" ' . $style['submit'] . ' name="plugin_submit" value="' . $form['value_submit'] . '"') . "\n";
			$saveform .= '</form>' . "\n";
		}
		$saveform .= "\n" . '</div>' . "\n";
		return $saveform;
	}
}

/**
 * Function print_plugin_admin()
 * Create plugin menu tabs.
 *
 * @param string $main Set to OFF, if menu is not needed.
 *
 * @return string Returns the created plugin-menu.
 */
function print_plugin_admin($main) 
{
    global $sn, $plugin, $pth, $sl, $cf, $adm;
    global $pluginloader_tx, $plugin_tx;

    initvar('plugin_text');
    initvar('action');
    initvar('admin');

    $t = '';
    $css = '';
    $config = '';
    $language = '';
    $help = '';

	if($adm)
	{
	
    $main = strtoupper($main);

    PluginFiles($plugin);

    if (file_exists($pth['file']['plugin_stylesheet'])) 
	{
        $css = 'ON';
    }
    if (file_exists($pth['file']['plugin_config'])) 
	{
        //include($pth['file']['plugin_config']);
        $config = 'ON';
    }
    if (file_exists($pth['file']['plugin_language'])) 
	{
        //include($pth['file']['plugin_language']);
        $language = 'ON';
    }
    if (file_exists($pth['file']['plugin_help'])) 
	{
        $help = 'ON';
    }

    /**
     *  Use preset texts for the menu, if the plugin itself did not define text for the menu
     */
    if (isset($plugin_tx[$plugin]['menu_main']) AND !empty($plugin_tx[$plugin]['menu_main'])) 
	{
        $pluginloader_tx['menu']['tab_main'] = $plugin_tx[$plugin]['menu_main'];
    }
    if (isset($plugin_tx[$plugin]['menu_css']) AND !empty($plugin_tx[$plugin]['menu_css'])) 
	{
        $pluginloader_tx['menu']['tab_css'] = $plugin_tx[$plugin]['menu_css'];
    }
    if (isset($plugin_tx[$plugin]['menu_config']) AND !empty($plugin_tx[$plugin]['menu_config'])) 
	{
        $pluginloader_tx['menu']['tab_config'] = $plugin_tx[$plugin]['menu_config'];
    }
    if (isset($plugin_tx[$plugin]['menu_language']) AND !empty($plugin_tx[$plugin]['menu_language'])) 
	{
        $pluginloader_tx['menu']['tab_language'] = $plugin_tx[$plugin]['menu_language'];
    }
    if (isset($plugin_tx[$plugin]['menu_help']) AND !empty($plugin_tx[$plugin]['menu_help'])) 
	{
        $pluginloader_tx['menu']['tab_help'] = $plugin_tx[$plugin]['menu_help'];
    }

    $plugin_menu_style = ARRAY();
	
	PluginMenu('ROW', '', '', '', $plugin_menu_style);

    if ($main == 'ON') 
	{
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_main&amp;action=plugin_text', '', $pluginloader_tx['menu']['tab_main'], $plugin_menu_style);
    }

    if($css == 'ON') 
	{
        PluginMenu('TAB', $sn.'?&amp;'.$plugin.'&amp;admin=plugin_stylesheet&amp;action=plugin_text', '', $pluginloader_tx['menu']['tab_css'], $plugin_menu_style);	
    }

    if($config == 'ON') 
	{ 
        PluginMenu('TAB', $sn.'?&amp;'.$plugin.'&amp;admin=plugin_config&amp;action=plugin_edit', '', $pluginloader_tx['menu']['tab_config'], ''); 
    }

    if ($language == 'ON') 
	{
        PluginMenu('TAB', $sn . '?&amp;' . $plugin . '&amp;admin=plugin_language&amp;action=plugin_edit', '', $pluginloader_tx['menu']['tab_language'], '');
    }
    if ($help == 'ON') 
	{
        PluginMenu('TAB', $pth['file']['plugin_help'], 'target="_blank"', $pluginloader_tx['menu']['tab_help'], '');
    }

    $t .= PluginMenu('SHOW');
	}
    return $t;
}

/**
 * Function plugin_admin_common()
 *
 * Handles reading and writing of plugin files
 * (e.g. en.php, config.php, stylesheet.css)
 *
 * @param bool $action Possible values: 'empty' = Error: empty value detected
 * @param array $admin Array, that contains debug_backtrace()-data
 * @param bool $plugin The called varname
 * @param bool $hint Array with hints for the variables (will be shown as popup)
 *
 * @global string $sn CMSimple's script-name (relative adress including $_GET)
 * @global string $action Ordered action
 * @global string $admin Ordered admin-action
 * @global string $plugin Name of the plugin, that called this function
 * @global string $pth CMSimple's configured pathes in an array
 * @global string $tx CMSimple's text-array
 * @global string $pluginloader_tx Plugin-Loader's text-array
 * @global string $plugin_tx Plugin's text-array
 * @global string $plugin_cf Plugin's config-array
 *
 * @return string Returns the created form or the result of saving the data
 */
function plugin_admin_common($action, $admin, $plugin, $hint=ARRAY()) 
{
    global $sn, $action, $admin, $plugin, $pth, $tx, $pluginloader_tx, $plugin_tx, $plugin_cf, $adm;

    $data = '';
    $t = '';
    $error_msg = ($admin == '' OR is_writeable($pth['file'][$admin])) ? '' : '<div class="pluginerror">' . "\n" . '<b>' . $tx['error']['notwritable'] . ':</b>' . "\n" . '</div>' . "\n" . '<ul>' . "\n" . '<li>' . $pth['file'][$admin] . '</li>' . "\n" . '</ul>' . "\n";

    
	if($adm)
	{
	
		if ($admin == 'plugin_config') 
		{
			$var_name = 'plugin_cf';
			$data = $plugin_cf[$plugin];
		}
		if ($admin == 'plugin_language') 
		{
			$var_name = 'plugin_tx';
			$data = $plugin_tx[$plugin];
		}
		if ($admin == 'plugin_stylesheet') 
		{
			$var_name = 'plugin_text';
		}
		if ($admin == 'plugin_main') 
		{
			$var_name = 'plugin_text';
		}
	
	}

    if ($adm && ($action == 'plugin_text' OR $action == 'plugin_edit')) 
	{
        if (isset($plugin_tx[$plugin])) 
		{
            $hint = array_merge($hint, $plugin_tx[$plugin]);
        }
        $form = ARRAY();
        $style = ARRAY();

        $style['form'] = 'class="plugineditform"';
        $style['divcaption'] = 'class="plugineditcaption"';
        $style['submit'] = 'class="submit"';

        $form['action'] = $sn . '?&amp;' . $plugin;
        $form['method'] = 'POST';
        $form['value_admin'] = $admin;
        $form['value_submit'] = ucfirst($tx['action']['save']);
        $form['caption'] = ucfirst(str_replace("_", " ", $plugin));
        $form['errormsg'] = $error_msg;

        if ($action == 'plugin_text') 
		{
            $file_data = PluginReadFile($pth['file'][$admin]);
            $data = $file_data['content'];
            $form['type'] = 'TEXT';
            $form['value_action'] = 'plugin_textsave';
            $form['textarea_name'] = 'plugin_text';
            $style['div'] = 'class="plugintext"';
            $style['textarea'] = 'class="plugintextarea"';
        }
        if ($action == 'plugin_edit') 
		{
            $form['type'] = 'CONFIG';
            $form['value_action'] = 'plugin_save';
            $style['div'] = 'class="pluginedit"';
            $style['table'] = 'class="pluginedittable"';
            $style['tdcaption'] = 'class="plugincfcap"';
            $style['tdhint'] = 'class="plugincfhint"';
            $style['tdconfig'] = 'class="plugincf"';
            $style['input'] = 'class="plugininput"';
            $style['inputmax'] = 'class="plugininputmax"';
        }
        $t .= PluginSaveForm($form, $style, $data, $hint);
    }

    if ($adm && ($action == 'plugin_save' OR $action == 'plugin_textsave')) 
	{
        if ($action == 'plugin_save') 
		{
            $config_data = ARRAY();
            foreach ($data as $key => $value) 
			{
                global $pluginloader_cfg;
                $config_data[$key] = $_POST[$pluginloader_cfg['form_namespace'] . $key];
            }
            $save_data = PluginPrepareConfigData($var_name, $config_data, $plugin);
        }
        if ($action == 'plugin_textsave') 
		{
            $text_data = $_POST[$var_name];
            $save_data = PluginPrepareTextData($text_data);
        }
        $is_saved = PluginWriteFile($pth['file'][$admin], $save_data);
        $t .= tag('br') . '<b>' . $is_saved['msg'] . '</b>' . tag('br');
    }
    return $t;
}

/**
 * Function PluginDebugger()
 *
 * @param bool $error Possible values: 'empty' = Error: empty value detected
 * @param array $caller Array, that contains debug_backtrace()-data
 * @param bool $varname The called varname
 * @param bool $value The value of the var
 *
 * @global array $pluginloader_tx Plugin-loader's text-array
 *
 * @return string Returns result of debugging as text
 */
function PluginDebugger($error=FALSE, $caller=FALSE, $varname=FALSE, $value=FALSE) 
{
    global $pluginloader_tx;
    $debug = '';
    $debug .= $pluginloader_tx['error']['plugin_error'] . '';
    switch ($error) 
	{
        case 'empty': $debug .= 'empty/no data (' . $varname . ')';
            break;
        default: $debug .= 'undefined error (' . $varname . ')';
    }
    $debug .= tag('br') . 'in' . tag('br');
    foreach ($caller AS $call) 
	{
        $debug .= 'File: ' . $call['file'] . ' - ' . $call['function'] . ' - Line: ' . $call['line'] . tag('br');
    }
    return $debug;
}

/**
 * Function preCallPlugins() => Pre-Call of Plugins.
 *
 * All Plugins which are called through a function-call
 * can use this. At the moment it is'nt possible to do
 * this with class-based plugins. They need to be called
 * through standard-CMSimple-Scripting.
 *
 * Call a plugin: place this in your code (example):
 * {{{PLUGIN:pluginfunction('parameters');}}}
 *
 * Call a built-in function (at the moment only one for
 * demonstration):
 * {{{HOME}}} or: {{{HOME:name_of_Link}}}
 * This creates a link to the first page of your CMSimple-
 * Installation.
 * 
 * @param pageIndex - added for search
 * @global bool $edit TRUE if edit-mode is active
 * @global array $c Array containing all contents of all CMSimple-pages
 * @global integer $s Pagenumber of active page
 * @global array $u Array containing URLs to all CMSimple-pages
 * 
 * @author mvwd
 * @since V.2.1.02
 */
function preCallPlugins($pageIndex = -1) 
{
    global $edit, $c, $s, $u;

    if (!$edit) 
	{
        if ((int) $pageIndex > - 1 && (int) $pageIndex < count($u)) 
		{
            $as = $pageIndex;
        } 
		else 
		{
            $as = $s < 0 ? 0 : $s;
        }
		$c[$as] = evaluate_plugincall($c[$as]);
    }
}

?>