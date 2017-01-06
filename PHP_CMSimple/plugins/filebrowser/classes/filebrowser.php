<?php

if (preg_match('#/filebrowser/classes/filebrowser.php#i',$_SERVER['SCRIPT_NAME'])) 
{
    die('no direct access');
}

/* utf-8 marker: äöü */

class CMSimpleFileBrowser {

    var $linkPrefix = '';
    var $browseBase = '';
    var $baseDirectory;
    var $currentDirectory;
    var $linkType;
    var $folders = array();
    var $files = array();
    var $baseDirectories = array();
    var $allowedExtensions = array();
    var $maxFilesizes = array();
    var $view;
    var $message = '';
    var $browserPath = '';
    

    function __construct() {
        global $pth, $plugin_cf, $cf, $userfiles_path, $userfiles_path_images, $userfiles_path_downloads, $userfiles_path_media;

        $image_extensions = array();
        $temp = explode(',', $cf['filebrowser']['extensions_images']);

        foreach ($temp as $ext) {
            $extension = trim($ext, ' ./');
            if ((bool) $extension) {
                $image_extensions[] = strtolower($extension);
            }
        }

        $download_extensions = array();
        $temp = explode(',', $cf['filebrowser']['extensions_downloads']);

        foreach ($temp as $ext) {
            $extension = trim($ext, ' ./');
            if ((bool) $extension) {
                $download_extensions[] = strtolower($extension);
            }
        }


        $userfiles_extensions = array();
        $temp = explode(',', $cf['filebrowser']['extensions_userfiles']);

        foreach ($temp as $ext) {
            $extension = trim($ext, ' ./');
            if ((bool) $extension) {
                $userfiles_extensions[] = strtolower($extension);
            }
        }


        $media_extensions = array();
        $temp = explode(',', $cf['filebrowser']['extensions_media']);

        foreach ($temp as $ext) {
            $extension = trim($ext, ' ./');
            if ((bool) $extension) {
                $media_extensions[] = strtolower($extension);
            }
        }


        $this->browserPath = $pth['folder']['plugins'] . basename(dirname(dirname(__FILE__))) . '/';


        $this->view = new CMSimpleFileBrowserView();


		$this->baseDirectories['images'] = $userfiles_path_images;
		$this->baseDirectories['downloads'] = $userfiles_path_downloads;;
		$this->baseDirectories['userfiles'] = $userfiles_path;
		$this->baseDirectories['media'] = $userfiles_path_media;



        $this->allowedExtensions['images'] = $userfiles_extensions;
        $this->allowedExtensions['downloads'] = $userfiles_extensions;
        $this->allowedExtensions['userfiles'] = $userfiles_extensions;
        $this->allowedExtensions['media'] = $userfiles_extensions;
    }

    function fileIsLinked($file) {
        global $h, $c, $u;
        $i = 0;
        $usages = array();


        foreach ($c as $page) {

            if (preg_match('#<.*(src|href|download)=["|\'].*' . $file . '["|\'].*>#i', $page) > 0) {
                $usages[] = '<a href="?' . $u[$i] . '">' . $h[$i] . '</a>';
            }
            $i++;
        }
        $usages = array_unique($usages);
        if (count($usages) > 0) {
            return $usages;
        }
        return false;
    }

    function readDirectory() {
        $dir = $this->browseBase . $this->currentDirectory;
        $this->files = array();

        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {

                if (strpos($file, '.') === 0) {
                    continue;
                }
                if (is_dir($dir . $file)) {
                    $this->folders[] = $this->currentDirectory . $file;
                    continue;
                }

                if ($this->isAllowedFile($file)) {
                    $this->files[] = $file;
                }
            }
            closedir($handle);
            natcasesort($this->folders);
            natcasesort($this->files);
        }
    }

    function getFolders($directory) {


        $folders = array();



        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') === 0) {
                    continue;
                }
                if (is_dir($directory . $file)) {
                    $folders[] = str_replace($this->browseBase, '', $directory . $file);
                    foreach ($this->getFolders($directory . $file . '/') as $subfolder) {
                        $folders[] = $subfolder;
                    }
                }
            }
            closedir($handle);
            natcasesort($folders);
        }
        return $folders;
    }

    function isAllowedFile($file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($extension == $file) {
            return false;
        }
        if (!in_array($extension, $this->allowedExtensions[$this->linkType])
                && !in_array('*', $this->allowedExtensions[$this->linkType])) {
            return false;
        }
        return true;
    }

		function foldersArray($all = true) {
        $folders = array();

        $temp = $this->getFolders($this->browseBase . $this->baseDirectory);
        $baseDepth = count(explode('/', $this->baseDirectory)) - 2;
        foreach ($temp as $i => $folder) {
            $ar = explode('/', $folder);
            $level = count($ar);
            $parent = '';
            for ($i = 0; $i < $level - 1; $i++) {
                $parent .= '/' . $ar[$i];
            }
            $parent = substr($parent, 1);
            $folders[$folder]['level'] = count($ar) - $baseDepth;
            $folders[$folder]['parent'] = $parent;
            $folders[$folder]['children'] = array();
            $linkList = '';
        }
        foreach ($folders as $folder => $data) {
            $folders[$folder]['children'] = $this->gatherChildren($folder, $folders);
        }

        $this->view->currentDirectory = $this->currentDirectory;
        foreach ($folders as $folder => $data) {
            $folders[$folder]['linkList'] = $this->view->folderLink($folder, $folders);
        }
        return $folders;
    }

    function gatherChildren($parent, $folders) {
        $children = array();
        foreach ($folders as $key => $folder) {
            if ($folder['parent'] == $parent) {
                $children[] = $key;
            }
        }
        return $children;
    }

    function deleteFile($file) {

        $file = $this->browseBase . $this->currentDirectory . basename($file);

        if (is_array($this->fileIsLinked($file))) 
		{
			$this->view->message .= '<div class="cmsimplecore_warning"><p>';
            $this->view->error('error_not_deleted', $file);
			$this->view->message .= '</p><p>';
            $this->view->error('error_file_is_used', $file);
			$this->view->message .= '</p>';

            foreach ($this->fileIsLinked($file) as $page) 
			{
                $this->view->message .= '<ul style="font-weight: 300;"><li>' . $page . '</li>';
            }
            $this->view->message .= '</ul></div>';
            return;
        }


        if (@unlink($file)) {
            $this->view->success('success_deleted', $file);
        } else {
            $this->view->error('error_not_deleted', $file);
        }
    }

	function uploadFile() 
	{
		$file = $_FILES['fbupload'];
		$dir = explode('/',$this->currentDirectory);

		if (isset($this->maxFilesizes[$dir[0]]))
		{
			if ($file['size'] > $this->maxFilesizes[$dir[0]]) {
				$this->view->message.= '<p class="cmsimplecore_warning" style="text-align: center;">';
				$this->view->error('error_not_uploaded', $file['name']);
				$this->view->error('error_file_too_big', array(number_format($file['size']/1000, 2),  number_format($this->maxFilesizes[$dir[0]]/1000, 2)));
				$this->view->message.= '</p>';
				return;
			}
		}

		if ($file['error'] != 0) 
		{
			$this->view->error('error_not_uploaded', $file['name']);
			return;
		}
		$filename = $this->browseBase . $this->currentDirectory . basename($file['name']);
		if (file_exists($filename)) 
		{
			$this->view->message.= '<p class="cmsimplecore_warning" style="text-align: center;">';
			$this->view->error('error_not_uploaded', $file['name']);
			$this->view->error('error_file_already_exists', $filename);
			$this->view->message.= '</p>';
			return;
        }
        if ($this->isAllowedFile($file['name']) == false) 
		{
			$this->view->message.= '<p class="cmsimplecore_warning" style="text-align: center;">';
			$this->view->error('error_not_uploaded', $file['name']);
			$this->view->error('error_no_proper_extension', pathinfo($file['name'], PATHINFO_EXTENSION));
			$this->view->message.= '</p>';
			return;
        }
		if (move_uploaded_file($_FILES['fbupload']['tmp_name'], $filename)) 
		{
			chmod($filename, 0666);
			$this->view->success('success_uploaded', $file['name']);
			return;
		}
		$this->view->error('error_not_uploaded', $file['name']);
	}

	function createFolder() 
	{
		$folder = basename($_POST['createFolder']);
		$folder = str_replace(array(':', '*', '?', '"', '<', '>', '|', ' '), '', $folder);
		$folder = $this->browseBase . $this->currentDirectory . $folder;
		if (is_dir($folder)) 
		{
			$this->view->error('error_folder_already_exists', basename($folder));
			return;
		}
		if (!mkdir($folder)) 
		{
			$this->view->error('error_unknown');
		}
		$this->view->success('success_folder_created', basename($folder));
		chmod($folder, 0777);
		return;
	}

    function deleteFolder() {
        $folder = $this->browseBase . $this->currentDirectory . basename($_POST['folder']);
        if (!rmdir($folder)) {
            $this->view->error('error_not_deleted', basename($folder));
            return;
        }
        $this->view->success('success_deleted', basename($folder));
        return;
    }

    function renameFile() {

        $newName = str_replace(array('..', '<', '>', ':', '?', ' '), '', basename($_POST['renameFile']));
        $oldName = $_POST['oldName'];
        if ($oldName == $newName) {
            return;
        }
        if (pathinfo($newName, PATHINFO_EXTENSION) !== pathinfo($oldName, PATHINFO_EXTENSION)) {
            $this->view->message = 'You can not change the file extension!';
            return;
        }
        if (file_exists($this->browseBase . $this->currentDirectory . '/' . $newName)) {
            $this->view->error('error_file_already_exists', $newName);
            return;
        }

        if (is_array($this->fileIsLinked($oldName))) {
            $this->view->error('error_cant_rename', $oldName);
            $this->view->error('error_file_is_used', $oldName);

            foreach ($this->fileIsLinked($oldName) as $page) {
                $this->view->message .= '<li>' . $page . '</li>';
            }
            $this->view->message .= '</ul>';
            return;
        }
        if (rename($this->browseBase . $this->currentDirectory . '/' . $oldName, $this->browseBase . $this->currentDirectory . '/' . $newName)) {
            $this->view->message = 'Renamed ' . $oldName . ' to ' . $newName . '!';
            return;
        }
        $this->view->message = 'Something went wrong (CMSimpleFileBrowser::renameFile())';
        return;
    }

    function render($template) {

        $template = str_replace(array('.', '/', '\\', '<', ' '), '', $template);


        if (!file_exists($this->browserPath . 'tpl/' . $template . '.html')) {
            return "<p>CMSimpleFileBrowser::render() - Template not found: {$this->browserPath}tpl/$template.html'</p>";
        }
        $this->view->baseDirectory = $this->baseDirectory;
        //  $this->view->basePath = '';
        $this->view->baseLink = $this->linkType;
        $this->view->folders = $this->foldersArray();
        $this->view->subfolders = $this->folders;
        $this->view->files = $this->files;

        return $this->view->loadTemplate($this->browserPath . 'tpl/' . $template . '.html');
    }

    function setLinkParams($paramsString) {
        $this->view->linkParams = $paramsString;
    }

    function setLinkPrefix($prefix) {
        $this->view->linkPrefix = $prefix;
    }

    function setBrowseBase($path) {

        $this->browseBase = $path;
        $this->view->basePath = $path;
    }

    function setBrowserPath($path) {
        $this->view->browserPath = $path;
    }
    
    function setMaxFileSize($folder = '', $bytes) {
        if (key_exists($folder, $this->baseDirectories)){
            $this->maxFilesizes[$folder] = (int) $bytes;
        }
    }
}

?>
