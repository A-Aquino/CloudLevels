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

//CloudLevels Installer

//When there is input data
if(!empty($_POST["username"])){
	
	//Check if confirm password matches password field
	if($_POST["password"]!=$_POST["password_confirm"]){
		echo '<div class="card hoverable red"><div class="card-content white-text"><p>ERROR: Your entered passwords do not match.</p></div></div>';
		exit(0);
	}

	//Create configuration file:

	//Open file
	$configfile = fopen("configuration.php", "w") or die('<div class="card hoverable red"><div class="card-content white-text"><p>ERROR: File <strong>configuration.php</strong> could not be written to server.</p></div></div>');
	fwrite($configfile, "<?php\n");

	//Write database values
	fwrite($configfile, "\$db_type='" . addslashes($_POST["db_type"]) . "';\n");
	fwrite($configfile, "\$db_hostname='" . addslashes($_POST["db_hostname"]) . "';\n");
	fwrite($configfile, "\$db_username='" . addslashes($_POST["db_username"]) . "';\n");
	fwrite($configfile, "\$db_password='" . addslashes($_POST["db_password"]) . "';\n");
	fwrite($configfile, "\$db_database='" . addslashes($_POST["db_database"]) . "';\n");
	
	//Write default configuration stuff
	fwrite($configfile, "\$site_name='Site Name';\n");
	fwrite($configfile, "\$site_desc='Set the description here!';\n");
	fwrite($configfile, "\$game_url='#';\n");
	fwrite($configfile, "\$file_size_limit='1000000';\n");
	fwrite($configfile, "\$tags='Each\nTag\nGoes\nOn\nA\nNew\nLine';\n");
	fwrite($configfile, "\$theme='light-blue';\n");
	fwrite($configfile, "\$reg_question='What is 1 plus 8 minus the number of clouds in your head?';\n");
	fwrite($configfile, "\$reg_answer='9';\n");
	
	//Close file
	fwrite($configfile, "?>\n");
	fclose($configfile);

	//Create database:
	
	//Configuration variables
	include 'configuration.php';

	//Create tables
	try {
		
		//Connect to database
		$db = new PDO($db_type . ':host=' . $db_hostname . ';dbname=' . $db_database . ';charset=utf8', $db_username, $db_password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		
		//Begin
		$db->beginTransaction();
		
		//Users table
		$db->exec('CREATE TABLE cl_user(
		id INTEGER AUTO_INCREMENT,
		usergroup TINYINT DEFAULT 0,
		username TINYTEXT,
		password TINYTEXT,
		date TINYTEXT,
		ip TINYTEXT,
		uploads INTEGER DEFAULT 0,
		PRIMARY KEY (id)
		)');
		//usergroup: 0=Normal, 1=Banned, 2=Admin
		
		//Files table
		$db->exec('CREATE TABLE cl_file(
		id INTEGER AUTO_INCREMENT,
		name TINYTEXT,
		author INTEGER,
		date TINYTEXT,
		ip TINYTEXT,
		downloads INTEGER DEFAULT 0,
		likes INTEGER DEFAULT 0,
		featured TINYINT DEFAULT 0,
		description TEXT,
		PRIMARY KEY (id),
		FOREIGN KEY (author) REFERENCES cl_user (id)
		)');
		
		//Comments table
		$db->exec('CREATE TABLE cl_comment(
		id INTEGER AUTO_INCREMENT,
		author INTEGER,
		file INTEGER,
		date TINYTEXT,
		ip TINYTEXT,
		comment TEXT,
		PRIMARY KEY (id),
		FOREIGN KEY (author) REFERENCES cl_user (id)
		)');
		
		//Likes table
		$db->exec('CREATE TABLE cl_like(
		author INTEGER,
		file INTEGER,
		PRIMARY KEY (author, file),
		FOREIGN KEY (author) REFERENCES cl_user (id),
		FOREIGN KEY (file) REFERENCES cl_file (id)
		)');
		
		//Tags table
		$db->exec('CREATE TABLE cl_tag(
		file INTEGER,
		tag VARCHAR (255),
		PRIMARY KEY (file, tag),
		FOREIGN KEY (file) REFERENCES cl_file (id)
		)');
		
		//Create first admin user
		date_default_timezone_set('America/New_York');
		$stmt = $db->prepare("
			INSERT INTO cl_user(usergroup, username, password, date, ip)
			VALUES(2,?,?,?,?)");
		$stmt->execute(array($_POST["username"], crypt($_POST["password"]), date("F j, Y"), $_SERVER['REMOTE_ADDR']));
		
		//End
		$db->commit();
		
	}
	
	//Handle errors
	catch(PDOException $ex){
		
		//$db->rollBack();
		echo '<div class="card hoverable red"><div class="card-content white-text"><p>ERROR: ' . $ex->getMessage() . '</p></div></div>';
		exit(0);
		
	}

}

//Get available database types
$supported_db_types = PDO::getAvailableDrivers();

//HTML Template:
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
		<title>CloudLevels Installer</title>
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	</head>
	<body>
		<div class="container">
			<?php
			if(empty($supported_db_types)){
			?>
			<div class="card hoverable red">
				<div class="card-content white-text">
					<p>ERROR: No supported database types detected.</p>
				</div>
			</div>
			<?php
			}
			else if(!empty($_POST["username"])){
			?>
			<div class="card hoverable green">
				<div class="card-content white-text">
					<p>SUCCESS: Installation complete! Please delete <strong>install.php</strong> from your server.</p>
				</div>
			</div>
			<?php
			}
			?>
			<div class="row card hoverable">
				<span class="col s12 card-title light-blue white-text center" style="font-size: 200%;">Install</span>
				<form action="install.php" method="post" class="col s6 offset-s3">
					<div class="input-field col s12">
						<i class="material-icons prefix">account_circle</i>
						<input id="username" name="username" type="text" class="validate" required>
						<label for="username">User Name</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="password" name="password" type="password" class="validate" required>
						<label for="password">Password</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="password-confirm" name="password_confirm" type="password" class="validate" required>
						<label for="password-confirm">Confirm Password</label>
					</div>
					<i class="material-icons small col s1">settings_input_component</i> 
					<div class="input-field col s11">
						<select id="db_type" name="db_type">
							<?php 
								foreach($supported_db_types as $db_driver)
									echo '<option value="' . $db_driver . '">' . $db_driver . '</option>';
							?>

						</select>
						<label for="db_type">Database Type</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">settings_input_component</i>
						<input id="db-hostname" name="db_hostname" type="text" class="validate" required>
						<label for="db-hostname">Database Hostname</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">settings_input_component</i>
						<input id="db-username" name="db_username" type="text" class="validate" required>
						<label for="db-username">Database Username</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">settings_input_component</i>
						<input id="db-password" name="db_password" type="password" class="validate" required>
						<label for="db-password">Database Password</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">settings_input_component</i>
						<input id="db-database" name="db_database" type="text" class="validate" required>
						<label for="db-database">Database Name</label>
					</div>
					<button class="btn waves-effect waves-light light-blue col s12" type="submit">Install</button>
				</form><div class="row"></div>
			</div>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
		<script>$(document).ready(function() {$('select').material_select();$("form").submit(function(){$("button").attr("disabled", true);return true;});});</script>
	</body>
</html>
