<?php
session_start();
if($_GET['section'] == "io")
{
	if ($_GET['action'] == "createDirectory") {
					$odir = $_POST['path'];
				    $dir = "../files/".$_SESSION['username']."/$odir";
					mkdir($dir);
	}
		if ($_GET['action'] == "removeFile") {
					$odir = $_POST['path'];
				    $dir = "../files/".$_SESSION['username']."/$odir";
					unlink($dir);
	}
		if ($_GET['action'] == "removeDir") {
					$odir = $_POST['path'];
				    $dir = "../files/".$_SESSION['username']."/$odir";
					rmdir($dir);
	}
		if ($_GET['action'] == "getFolder") {
					$odir = $_POST['path'];
				    $dir = opendir("../files/".$_SESSION['username']."/$odir");
					if(!$dir){
								die();
					} else {
						$output = "<" . "?xml version='1.0' encoding='utf-8' ?" . ">" . "\r\n" . "<getFolderResponse path=\"" . $_REQUEST['path'] . "\">";
						while(($file = readdir($dir)) !== false){
							if($file == '..' || $file == '.'){
								continue;
							} else {
								$t = strtolower($file);
								if(is_dir("../files/".$_SESSION['username']."/$odir" . $file)){
									$type = 'folder';
								} else {
									$type = 'file';
								}
								$output .=  "\r\n" . '<file type="' . $type . '">' . $file . '</file>';
							}
						}
						$output .=  "\r\n" . '</getFolderResponse>';
						header('Content-type: text/xml');
						echo $output;
					}
	}
		if ($_GET['action'] == "getFile") {
					$odir = $_POST['path'];
				    	$dir = "../files/".$_SESSION['username']."/$odir";
					$file = file_get_contents($dir);
					$output = "<" . "?xml version='1.0' encoding='utf-8' ?" .">\r\n" . "<getFileResponse path=\"" . $_REQUEST['path'] . "\">";
					$output .=  "\r\n" . '<file>' . $file . '</file>';
					$output .= '</getFileResponse>';
					header('Content-type: text/xml');
					echo $output;
	}
		if ($_GET['action'] == "writeFile") {
					$content = $_POST['content'];
					$odir = $_POST['path'];
				    	$dir = "../files/".$_SESSION['username']."/$odir";
					$file = file_put_contents($dir, $content);
	}
}
?>