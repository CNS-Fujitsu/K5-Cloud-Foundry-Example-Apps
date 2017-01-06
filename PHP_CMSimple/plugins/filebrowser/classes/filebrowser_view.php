<?php

if (preg_match('#/filebrowser/classes/filebrowser_view.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* utf-8 marker: äöü */

class CMSimpleFileBrowserView 
{
    var $partials = array();
    var $browserPath = '';
    var $basePath;
    var $baseDirectory;
    var $baseLink;
    var $currentDirectory;
    var $linkParams;
    var $linkPrefix;
    var $folders;
    var $subfolders;
    var $files;
    var $message = '';
    var $lang = array();

    function __construct() 
	{
        global $sl, $pth, $plugin_tx, $tx;
        $lang = array();
        $langFile = $pth['folder']['cmsimple'] . 'languages/';

        $langFile .= file_exists($langFile . $sl . '.php') ? $sl . '.php' : 'en.php';
        include_once $langFile;
        $this->lang = $tx['filebrowser'];
    }

    function folderList($folders) 
	{
        global $tx, $plugin_tx, $adm, $subsite_folder;
        //     $title = $this->baseLink === 'images' ? 'Bilder' : 'Downloads';
        
		if($adm == '')
		{
			$title = ucfirst($tx['title'][$this->baseLink]) ? $tx['title'][$this->baseLink] : ucfirst($tx['title']['userfiles'] . ' ' . $this->translate('folder')); // für Editorbrowser
		}
		else
		{
			$title = ucfirst($tx['title'][$this->baseLink]) ? $tx['title']['userfiles'] : ucfirst($tx['title']['userfiles'] . ' ' . $this->translate('folder')); // für CMS Browser
		}
		
		
        $html = '
<ul>
<li class="openFolder">
<a href="?' . $this->linkParams . '">' . $title . ' ' . $tx['filebrowser']['folder'] . '</a>
<ul>';
		foreach ($folders as $folder => $data) 
		{
			if ($data['level'] == 2) 
			{
				$html .= $data['linkList'];
			}
		}
        $html .='
	</ul>
</li>
</ul>';
        return $html;
    }

    function folderLink($folder, $folders) 
	{
		global $subsite_folder;
		
		$link = $_SESSION['fb_sn'];
		if(!defined('CMSIMPLE_VERSION'))
		{
			$link = str_replace($_SESSION['subsite_folder'],'',$_SESSION['fb_sn']);
			$link.='plugins/filebrowser/editorbrowser.php';
		}
		$class = 'folder';
		if (substr($this->currentDirectory, 0, strlen($folder)) == $folder) 
		{
			$class = 'openFolder';
		}
		$temp = explode('/', $folder);
        $html = "\n" . '
<li class="' . $class . '">
<a href="' . $link . '?' . $this->linkParams . '&subdir=' . $folder . '/">' . end($temp) . '</a>';
        if (count($folders[$folder]['children']) > 0) 
		{
            if (substr($this->currentDirectory, 0, strlen($folder)) !== $folder) 
			{
                $class = 'unseen';
            }

            $html .= '
<ul class="' . $class . '">';
            foreach ($folders[$folder]['children'] as $child) 
			{
                $html .= $this->folderLink($child, $folders);
            }
            $html .= '
</ul>';
        }
        $html .= '
</li>';
        return $html;
    }

    function subfolderList($folders) 
	{

		$html = '';
		if (is_array($folders) && count($folders) > 0) 
		{
			$html = '<ul>';
			foreach ($folders as $folder) 
			{
			$name = str_replace($this->currentDirectory, '', $folder);
			$html .= '
<li class="folder">
<form style="display: inline;" method="POST" action="" onsubmit="return confirmFolderDelete(\'' . $this->translate('confirm_delete', $this->basePath . $folder) . '\');">
<input type="image" src="' . $this->browserPath . 'icons/delete.gif" alt="delete" title="delete folder" style="float: left; margin-right: 8px;" />
<input type="hidden" name="deleteFolder" />
<input type="hidden" name="folder" value="' . $folder . '" />
</form>
<a href="?' . $this->linkParams . '&subdir=' . $folder . '/">' . $name . '</a></li>';
            }
            $html .= '</ul>
';
        }
        return $html;
    }

    function fileList($files) 
	{
		global $cf, $images;
		
		if(@$_SESSION['fb_view'] == 'list')
		{
			$html = '
<ul class="fb_files_list">';
		}
		else
		{
			$html = '
<ul>';
		}
		
        $i = 0;

        foreach ($files as $file) 
		{
			if(@$_SESSION['fb_view'] == 'miniatur' || !isset($_SESSION['fb_view'])) // thumbs view
			{
				$html.= '
<li style="width: ' . ($cf['filebrowser']['maxheight_of_thumbs']+$cf['filebrowser']['width_px_plus']) . 'px; height: ' . ($cf['filebrowser']['maxheight_of_thumbs']+66) . 'px; padding: 8px 0 12px 8px; margin: 6px 3px 9px 3px;">
<form style="display: inline;" method="POST" action="" onsubmit="return confirmFileDelete(\'' . $this->translate('confirm_delete', $this->currentDirectory . $file) . '\');">
<input type="image" src="' . $this->browserPath . 'icons/delete.gif" alt="delete" title="delete file" style="float: left; margin-right: 8px;" />
<input type="hidden" name="deleteFile" />
<input type="hidden" name="file" value="' . $file . '" />
</form>
<form method="POST" style="display:none;" action="" id="rename_' . $i . '">
<input type="text" size="25" name="renameFile" value="' . $file . '" onmouseout="hideRenameForm(\'' . $i . '\');"/>
<input type="hidden" name="oldName" value="' . $file . '" />
</form>
<a style="position:relative" class="xhfbfile" href="javascript:void(0)" id="file_' . $i . '" ondblclick="showRenameForm(\'' . $i . '\', \'' . $this->translate('prompt_rename', $file) . '\');" title="' . $file . '">
<div style="clear: both; width: 240px; float: left; padding-top: 6px;">' . substr($file,0,14);
				
				if(strlen($file) > 14 )
				{
					$html.= '...';
				}
				
				$html.= '</div>';

				if (is_array(@getimagesize($this->basePath . $this->currentDirectory . $file))) 
				{
					$image = getimagesize($this->basePath . $this->currentDirectory . $file);
					$width = $image[0];
					$height = $image[1];
					if ($width > 100) 
					{
						$ratio = $width / $height;
						$width = 100;
						$height = $width / $ratio;
					}
				}
				
				$fbFileTypeArray = explode('.',$file);
				$fbFileType = array_pop($fbFileTypeArray);
				
				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '<span class="filebrowser_image"><img src="' . $this->basePath . $this->currentDirectory . $file . '" style="float: left; max-width: 92%; max-height: ' . $cf['filebrowser']['maxheight_of_thumbs'] . 'px; padding: 0; margin: 0;" alt="' . $file . '" /></span>';
				}
				else
				{
					$html.= '<div class="fb_dummy" style="line-height: 2.4em;">' . $fbFileType . '</div>';
				}

				$html .= '
</a>
<p style="clear: both; padding: 6px 0 0 0; margin: 0; font-size: 12px;">
' . round(filesize($this->basePath . $this->currentDirectory . $file) / 1024, 0) . '&nbsp;kb';

				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '&nbsp;/&nbsp;' . @$image[0] . '&nbsp;x&nbsp;' . @$image[1];
				}

				$html .= '</p>
</li>
';
				
			}
			
			if(@$_SESSION['fb_view'] == 'list') // list view
			{
				$html .= '
<li class="fb_file">
<form style="display: inline;" method="POST" action="" onsubmit="return confirmFileDelete(\'' . $this->translate('confirm_delete', $this->currentDirectory . $file) . '\');">
<input type="image" src="' . $this->browserPath . 'icons/delete.gif" alt="delete" title="delete file" style="float: left; margin-right: 8px;" />
<input type="hidden" name="deleteFile" />
<input type="hidden" name="file" value="' . $file . '" />
</form>
<form method="POST" style="display:none;" action="" id="rename_' . $i . '">
<input type="text" size="25" name="renameFile" value="' . $file . '" onmouseout="hideRenameForm(\'' . $i . '\');"/>
<input type="hidden" name="oldName" value="' . $file . '" />
</form>
<a style="position:relative" class="xhfbfile" href="javascript:void(0)" id="file_' . $i . '" ondblclick="showRenameForm(\'' . $i . '\', \'' . $this->translate('prompt_rename', $file) . '\');" title="' . $file . '">' . substr($file,0,18);
				
				if(strlen($file) > 18 )
				{
					$html.= '...';
				}

				if (is_array(@getimagesize($this->basePath . $this->currentDirectory . $file))) 
				{
					$image = getimagesize($this->basePath . $this->currentDirectory . $file);
					$width = $image[0];
					$height = $image[1];
					if ($width > 100) 
					{
						$ratio = $width / $height;
						$width = 100;
						$height = $width / $ratio;
					}
				}
				
				$fbFileTypeArray = explode('.',$file);
				$fbFileType = array_pop($fbFileTypeArray);
				
				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '<span class="filebrowser_image"><img src="' . $this->basePath . $this->currentDirectory . $file . '" style="max-height: ' . $cf['filebrowser']['maxheight_of_thumbs'] . 'px; padding: 0; margin: 0;" alt="' . $file . '"  title="' . $file . '" /></span>';
				}
				
				$html .= '
</a>
<span class="fb_filedata"> - <span style="font-family: tahoma, verdana, arial, sans-serif; font-weight: 700; padding: 0;">' . $fbFileType . '</span> - ' . round(filesize($this->basePath . $this->currentDirectory . $file) / 1024, 0) . '&nbsp;kb';

				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '&nbsp;/&nbsp;' . @$image[0] . '&nbsp;x&nbsp;' . @$image[1];
				}
				$html .= '</span>
</li>
';
			}
			$i++;
		}
		$html .= '<br style="clear: both;"></ul>
<div style="clear: both; padding: 36px;">&nbsp;</div>';
        return $html;
    }

    function fileListForEditor($files) 
	{
		global $cf;
		
		if(@$_SESSION['fb_view'] == 'list')
		{
			$html = '
<ul class="fb_files_list">';
		}
		else
		{
			$html = '
<ul>';
		}
		
        $dir = $this->basePath . $this->currentDirectory;
        $is_image = (int) (strpos($this->linkParams, 'type=images') === 0);
        foreach ($files as $file) 
		{
            if // thumbs view
			(
				(@$_SESSION['fb_view'] == 'miniatur' || !isset($_SESSION['fb_view']))
			)
			{
				$html .= '
			<li style="';
				$html .= 'width: ' . ($cf['filebrowser']['maxheight_of_thumbs']+$cf['filebrowser']['width_px_plus']) . 'px; height: ' . ($cf['filebrowser']['maxheight_of_thumbs']+60) . 'px;  padding: 8px 0 12px 8px; margin: 6px 3px 9px 3px;">';
			
				$prefix = $this->linkPrefix;
			
				if ($prefix != '?&amp;download=') 
				{
					$prefix .= $this->currentDirectory;
				}
			
				$html .= '<a href="#" class="xhfbfile" onclick="window.setLink(\'' . $prefix . $file . '\',' . $is_image . '); return false;" title="' . $file . '">' . substr($file,0,14);
				
				if(strlen($file) > 14 )
				{
					$html.= '...';
				}
				
				if (is_array(@getimagesize($this->basePath . $this->currentDirectory . $file))) 
				{
					$image = getimagesize($this->basePath . $this->currentDirectory . $file);
					$width = $image[0];
					$height = $image[1];
					if ($width > 100) 
					{
						$ratio = $width / $height;
						$width = 100;
						$height = $width / $ratio;
					}
				}
				
				$fbFileTypeArray = explode('.',$file);
				$fbFileType = array_pop($fbFileTypeArray);
				
				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '<span class="filebrowser_image"><img src="' . $this->basePath . $this->currentDirectory . $file . '" style="float: left; max-width: 92%; max-height: ' . $cf['filebrowser']['maxheight_of_thumbs'] . 'px; padding: 0; margin: 0;" alt="' . $file . '" title="' . $file . '" /></span>';
				}
				else
				{
					$html.= '<div class="fb_dummy" style="line-height: 2.4em;">' . $fbFileType . '</div>';
				}

				$html .= '
</a>
<p style="clear: both; padding: 6px 0 0 0; margin: 0; font-size: 12px;">
' . round(filesize($this->basePath . $this->currentDirectory . $file) / 1024, 0) . '&nbsp;kb';

				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '&nbsp;/&nbsp;' . @$image[0] . '&nbsp;x&nbsp;' . @$image[1];
				}

				$html .= '</p>';
				
				$html .= '</li>';
			}
			else // list view
			{
				$html .= '
				<li class="fb_file" style="';
				$html .= 'width: 90%; background: transparent; border: 0;">';
          
				$prefix = $this->linkPrefix;

				if ($prefix != '?&amp;download=') 
				{
					$prefix .= $this->currentDirectory;
				}

				$html .= '<a href="#" class="xhfbfile" onclick="window.setLink(\'' . $prefix . $file . '\',' . $is_image . '); return false;" title="' . $file . '">' . substr($file,0,18);
				
				if(strlen($file) > 18 )
				{
					$html.= '...';
				}

			
				if ((strpos($this->linkParams, 'type=images') !== FALSE && getimagesize($dir . $file)) || preg_match('/.jpg|.jpeg|.png|.gif/i',$file)) 
				{
					$image = getimagesize($dir . $file);
					$width = $image[0];
					$height = $image[1];
					if ($width > 150) 
					{
						$ratio = $width / $height;
						$width = 150;
						$height = $width / $ratio;
					}
				}
				
				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '<span style="position: relative; z-index: 4; width: 100%; text-align: center;">
<img src="' . $this->basePath . $this->currentDirectory . $file . '" style="max-height: ' . $cf['filebrowser']['maxheight_of_thumbs'] . 'px;" alt="' . $file . '" title="' . $file . '" /></span>';
				}
				
				$fbFileTypeArray = explode('.',$file);
				$fbFileType = array_pop($fbFileTypeArray);
				
				$html .= '
</a>
<span class="fb_filedata"> - <span style="font-family: tahoma, verdana, arial, sans-serif; font-weight: 700; padding: 0;">' . $fbFileType . '</span> - ' . round(filesize($this->basePath . $this->currentDirectory . $file) / 1024, 0) . '&nbsp;kb';

				if(preg_match('/.jpg|.jpeg|.png|.gif/i',$file))
				{
					$html .= '&nbsp;/&nbsp;' . @$image[0] . '&nbsp;x&nbsp;' . @$image[1];
				}
				$html .= '</span>
</li>
';
			} // END else
        } // END foreach
        $html .= '</ul>';
        return $html;
    }

    function loadTemplate($template) 
	{
        if (file_exists($template)) 
		{
            ob_start();
            global $tx;
            include $template;
        }
        $html = ob_get_clean();
        $this->partials['folders'] = $this->folderList($this->folders);
        $this->partials['subfolders'] = $this->subFolderList($this->subfolders);
        if (basename($template) == 'cmsbrowser.html') 
		{
            $this->partials['files'] = $this->fileList($this->files);
        }
        if (basename($template) == 'editorbrowser.html') 
		{
            $this->partials['files'] = $this->fileListForEditor($this->files);
        }
        $this->partials['message'] = $this->message;
        foreach ($this->partials as $placeholder => $value) 
		{
            $html = str_replace('%' . strtoupper($placeholder) . '%', $value, $html);
        }
        $this->message = '';
        return $html;
    }

    function error($message ='', $args = null) 
	{
        global $tx;
        $this->message .= $this->translate($message, $args);
    }

    function success($message, $args = null) 
	{
        global $tx;
        $this->message .= '<p style="width: auto;">' . $this->translate($message, $args) . '</p>';
    }

    function message($message) 
	{
        $this->message .= '<p style="width: auto;">' . $message . '</p>';
    }

    function translate($string = '', $args = null) 
	{
        if (strlen($string) === 0) 
		{
            return '';
        }
        $html = '';
        if (!isset($this->lang[$string])) 
		{
            $html = '{' . $string . '}';
        } 
		else 
		{
            $html = $this->lang[$string];
        }
//
        if (is_array($args)) 
		{

            array_unshift($args, $html);


            return call_user_func_array('sprintf', $args);
        }
        if (is_string($args)) 
		{
            $html = sprintf($html, $args);
            return $html;
        }
        return $html;
    }
}

?>