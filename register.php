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

//CloudLevels Registration Page

//Header + Vars:
$page_title='Register';
include 'header.php';

//Guests only!
if($user_type!=-1){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

//When there is input data
if(!empty($_POST["username"])){
	
	//Verify registration question
	if($_POST["reg_question"]!=$reg_answer){
		
		errorbox($reg_question . ' Hit the back button and try again.');
		
	}
	
	//Check password confirmation
	else if($_POST["password"]!=$_POST["password_confirm"]){
		
		errorbox('Your passwords do not match. Hit the back button and try again.');
		
	}
	
	else{
		
		//Create account
		try{
			
			//Check if username exists
			$stmt = $db->prepare("
				SELECT *
				FROM cl_user
				WHERE username = ?");
			$stmt->execute(array($_POST["username"]));
			
			//If user exists
			if ($stmt->rowCount()>0){
				
				errorbox('User already exists.');
				
			}
			else{
				
				//Check if IP address exists
				$stmt = $db->prepare("
					SELECT *
					FROM cl_user
					WHERE ip = ?");
				$stmt->execute(array($_SERVER['REMOTE_ADDR']));
				
				//If IP address exists
				if ($stmt->rowCount()>0){
					
					errorbox('You are only allowed to have one account.');
					
				}
				else{
					
					date_default_timezone_set('America/New_York');
					$stmt = $db->prepare("
						INSERT INTO cl_user(username, password, date, ip)
						VALUES(?,?,?,?)");
					$stmt->execute(array(htmlspecialchars($_POST["username"]), crypt($_POST["password"]), date("F j, Y"), $_SERVER['REMOTE_ADDR']));
					successbox('Your account has been created. Please log in.');
					
				}
				
			}
			
		}
		//Handle errors
		catch(PDOException $ex){
			
			$db->rollBack();
			errorbox('Account could not be created. Hit the back button and try again.');
			
		}
		
	}
}
else{
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Register</span>
				<form action="register.php" method="post" class="col s12 m10 l8 offset-m1 offset-l2">
					<div class="input-field col s12">
						<i class="fa fa-user prefix" aria-hidden="true"></i>
						<input id="username" name="username" type="text" class="validate" required>
						<label for="username">User Name</label>
					</div>
					<div class="input-field col s12">
						<i class="fa fa-key prefix" aria-hidden="true"></i>
						<input id="password" name="password" type="password" class="validate" required>
						<label for="password">Password</label>
					</div>
					<div class="input-field col s12">
						<i class="fa fa-key prefix" aria-hidden="true"></i>
						<input id="password-confirm" name="password_confirm" type="password" class="validate" required>
						<label for="password-confirm">Confirm Password</label>
					</div>
					<div class="input-field col s12">
						<i class="fa fa-lock prefix" aria-hidden="true"></i>
						<input id="reg-question" name="reg_question" type="text" class="validate" required>
						<label for="reg-question"><?php echo $reg_question ?></label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Register</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
}
//Footer
include 'footer.php';
?>
