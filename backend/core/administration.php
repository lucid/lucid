<?php
/*
    Psych Desktop
    Copyright (C) 2006 Psychcf

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
	*/
	require("../lib/includes.php");
	import("models.user");
	if($_SESSION['userlevel'] == "admin")
	{
		if($_GET['section'] == "general")
		{
			if($_GET['action'] == "diskspace")
			{
				if(is_dir("/"))
				{
					$free = disk_free_space("/");
					$total = disk_total_space("/");
				}
				else
				{
					//windowze?
					$free = disk_free_space("C:");
					$total = disk_total_space("C:");
				}
				$p = new jsonOutput();
				$free = str_replace(",", ".", strval($free));
				$total = str_replace(",", ".", strval($total));
				$p->append("free", $free);
				$p->append("total", $total);
			}
		}
		if($_GET['section'] == "permissions") {
			if($_GET['action'] == "list") {
				import("models.permission");
				$list = $Permission->all();
				$outList = array();
				foreach($list as $perm) {
					array_push($outList, array(
						id => $perm->id,
						name => $perm->name,
						description => $perm->description,
						initial => ($perm->initial == 1)
					));
				}
				$out = new jsonOutput($outList);
			}
			if($_GET['action'] == "setDefault") {
				import("models.permission");
				$perm = $Permission->get($_POST['id']);
				$perm->initial = ($_POST['value'] == "true" ? TRUE : FALSE);
				$perm->save();
				$out = new intOutput("ok");
			}
		}
		if($_GET['section'] == "groups") {
			if($_GET['action'] == "list") {
				import("models.group");
				$list = $Group->all();
				$out = array();
				foreach($list as $group) {
					$out->append(array(
						id => $group->id,
						name => $group->name,
						description => $group->description,
						permissions => $group->permissions
					));
				}
				$output = new jsonOutput($out);
			}
			if($_GET['action'] == "new") {
				//TODO:
			}
			if($_GET['action'] == "set") {
				//TODO:
			}
			if($_GET['action'] == "delete") {
				//TODO:
			}
		}
		if($_GET['section'] == "users")
		{
			if($_GET['action'] == "delete") {
				$p = $User->get($_POST['id']);
				$cur = $User->get_current();
				if($p !== false && $p->id != $cur->id) {
					$p->delete();
				}
			}
			if($_GET['action'] == "list")
			{
				$p = $User->all();
				$out = new jsonOutput();
				$val = array();
				foreach($p as $d => $v)
				{
					$o = array();
					foreach(array(
						"id",
						"username",
						"name",
						"logged",
						"email",
						"level",
						"permissions",
						"groups",
						"lastauth"
					) as $key) {
						$o[$key] = $v->$key;
					}
					array_push($val, $o);
				}
				$out->set($val);
			}
			if($_GET['action'] == "online")
			{
				$online = 0;
				$total = 0;
				$p = $User->all();
				foreach($p as $u)
				{
					$total++;
					if($u->logged == 1) $online++;
				}
				$o = new jsonOutput;
				$o->append("online", $online);
				$o->append("total", $total);
			}
		}
	}
?>