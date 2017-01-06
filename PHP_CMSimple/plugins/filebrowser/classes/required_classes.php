<?php
/* utf-8 marker: äöü */

if (!defined('CMSIMPLE_VERSION') || preg_match('#/filebrowser/classes/required_classes.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

global $pth;

require_once $pth['folder']['plugin'] . 'classes/filebrowser_view.php'; 
require_once $pth['folder']['plugin'] . 'classes/filebrowser.php'; 
?>