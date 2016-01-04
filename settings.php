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

//CloudLevels User Settings

//Header + Vars:
$page_title='Settings';
include 'header.php';

//Members only!
if($user_type==-1||$user_type==1){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

//When there is input data
if(!empty($_POST["password_old"])){
	
	try{
		
		//Check password
		if($_POST["password_confirm"]!=$_POST["password_new"]){
			errorbox('New password and confirm password mismatch.');
		}
		
		//Correct password
		else{
		
			//SQL Stuff
			$stmt = $db->prepare("
				SELECT username, password
				FROM cl_user
				WHERE username = ?");
			$stmt->execute(array($user_name));
			$result = $stmt->fetchAll();
			$passhash=$result[0]['password'];
			
			//Compare password hash
			if(crypt($_POST["password_old"], $passhash)==$passhash){
				
				//SQL Stuff
				$stmt = $db->prepare("
					UPDATE cl_user
					SET password = ?
					WHERE username = ?");
				$stmt->execute(array(crypt($_POST["password_new"]), $user_name));
				
				successbox('Your password has been changed.');
			}
			else{
				errorbox('Wrong password');
			}
		}
	}
	
	//Handle errors
	catch(PDOException $ex){
		errorbox('Something happened.');
	}
	
}
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Change Password</span>
				<form action="settings.php" method="post" class="col s6 offset-s3">
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="password-old" name="password_old" type="password" class="validate" required>
						<label for="password-old">Old Password</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="password-new" name="password_new" type="password" class="validate" required>
						<label for="password-new">New Password</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="password-confirm" name="password_confirm" type="password" class="validate" required>
						<label for="password-confirm">Confirm New Password</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Change Password</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
//Footer
include 'footer.php';
?>
