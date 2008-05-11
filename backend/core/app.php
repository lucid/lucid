<?php
/*
	Copyright (c) 2004-2008, The Dojo Foundation & Lucid Contributors
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above.
*/


	require("../lib/includes.php");
	import("models.app");
	import("models.user");
	
	function jsSearch($path, $strip = "") {
		$files = array();
		$dir = opendir($path);
		while(($file = readdir($dir)) !== false){
			if($file{0} == '.'){
				continue;
			}
			else {
				if(is_dir($path . "/" . $file)) {
					$search = jsSearch($path . "/" . $file);
				}
				else if(is_file($path . "/" . $file) && count(preg_match("/\.js$/", $file) > 0)){
					$files[] = str_replace($strip, $strip === "" ? "" : "/", $path . "/" . $file);
				}
			}
		}
		return $files;
	}
	
    if($_GET['section'] == "install")
	{
		$cur = $User->get_current();
		if($_GET['action'] == "package" && $cur->has_permission("api.ide"))
		{
			import("lib.package");
			$out = new textareaOutput();	
			if(isset($_FILES['uploadedfile']['name'])) {
				$target_path = '../../tmp/'.$_FILES['uploadedfile']['name'];
				if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)
				&& package::install($target_path)) {
					$out->append("status", "success");
				} else{
				   $out->append("error", "Problem accessing uploaded file");
				}
			} else { $out->append("error", "No File Uploaded"); }
		}
	}
    if($_GET['section'] == "fetch")
	{
		if($_GET['action'] == "full")
		{
			if(isset($_POST['filename'])) {
				$_POST['filename'] = str_replace("..", "", $_POST['filename']);
				$file = $GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$_POST['filename'];
				$content = file_get_contents($file);
				$out = new jsonOutput(array(
					"contents" => $content
				));
			}
			else {
				$_POST['sysname'] = str_replace("..", "", $_POST['sysname']);
				$files = jsSearch($GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$_POST['sysname'],
									$GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/");
				$files[] = "/".$_POST['sysname'].".js";
				$out = new jsonOutput($files);
			}
		}
		if($_GET['action'] == "list")
		{
			$p = $App->all();
			$out = new jsonOutput();
			$list = array();
			foreach($p as $d => $v)
			{
				$item = array();
				foreach(array("sysname", "name", "author", "email", "maturity", "category", "version", "icon", "filetypes") as $key) {
					$item[$key] = $v->$key;
				}
				array_push($list, $item);
			}
			$out->set($list);
		}
		if($_GET['action'] == "listAll")
		{
			$p = $App->all();
			$out = new jsonOutput();
			$list = array();
			foreach($p as $d => $v)
			{
				$item = array();
				foreach(array("sysname", "name", "author", "email", "maturity", "category", "version", "filetypes") as $key) {
					$item[$key] = $v->$key;
				}
				if(is_dir($GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$item['sysname']))
					$item["files"] = jsSearch($GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$v->sysname,
												$GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/");
				else
					$item['files'] = array();
				$item["files"][] = "/".$v->sysname.".js";
				array_push($list, $item);
			}
			$out->set($list);
		}
	}
	if($_GET['section'] == "write")
	{
		if($_GET['action'] == "save")
		{
			import("models.user");
			$user = $User->get_current();
			if(!$user->has_permission("api.ide")) internal_error("permission_denied");
			if(isset($_POST['sysname'])) {
				$_POST['sysname'] = str_replace("..", "", $_POST['sysname']);
				$p = $App->filter("sysname", $_POST['sysname']);
				if($p === false) { $app = new App(array(sysname => $_POST['sysname'])); }
				else { $app = $p[0]; }
				foreach(array('name', 'author', 'email', 'version', 'maturity', 'category') as $item) {
					if(isset($_POST[$item]))
						$app->$item = $_POST[$item];
				}
				$app->save();
			}
			if(isset($_POST['filename'])) {
				$_POST['filename'] = str_replace("..", "", $_POST['filename']);
				file_put_contents($GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$_POST['filename'], $_POST['content']);
			}
			$out = new jsonOutput(array(status => "ok"));
			if($app) $out->append("sysname", $app->sysname);
		}
		if($_GET['action'] == "remove") {
			function rmdir_recurse($file) {
			    if (is_dir($file) && !is_link($file)) {
			        foreach(glob($file.'/*') as $sf) {
			            if ( !rmdir_recurse($sf) ) {
			                return false;
			            }
			        }
			        return rmdir($file);
			    } else {
			        return unlink($file);
			    }
			}
			
			import("models.user");
			$user = $User->get_current();
			if(!$user->has_permission("api.ide")) internal_error("permission_denied");
			$app = $App->filter("sysname", $_POST['sysname']);
			$app = $app[0];
			$app->delete();
			$_POST['sysname'] = str_replace("..", "", $_POST['sysname']);
			foreach(array(
				$GLOBALS['path']."/../apps/".$_POST['sysname'],
				$GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$_POST['sysname'],
				$GLOBALS['path']."/../desktop/dojotoolkit/desktop/apps/".$_POST['sysname'].".js"
			) as $dir) {
				if(is_dir($dir)) rmdir_recurse($dir);
				else if(is_file($dir)) unlink($dir);
			}
			$out = new intOutput("ok");
		}
	}