<?php
/*
* CloudLevels, an easy way to share user created level files for video games.
* Copyright (C) 2016 Alexander Aquino
*
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the Free
* Software Foundation, either version 3 of the License, or (at your option)
* any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
* FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
* more details.
*
* You should have received a copy of the GNU General Public License along with
* this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//CloudLevels Download File

//Header + Vars:
$page_title='Template';
include 'header.php';

//Get file + record in DB
if(!empty($_GET["id"])){
	$result=NULL;
	try{
		$stmt = $db->prepare("
			SELECT *
			FROM cl_file
			WHERE id = ?");
		$stmt->execute(array($_GET["id"]));
		$result = $stmt->fetchAll();
		
		$stmt = $db->prepare("
			UPDATE cl_file
			SET downloads = downloads+1
			WHERE id = ?");
		$stmt->execute(array($_GET["id"]));
	}
	catch(PDOException $ex){
		errorbox('Something happened.');
	}
	header("Location:/data/" . $_GET["id"] . " " . rawurlencode($result[0]['name']) . ".zip");
}

//Footer
include 'footer.php';
?>
