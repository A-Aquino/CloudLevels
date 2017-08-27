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

//CloudLevels Login Page

//Header + Vars:
$page_title='Login';
include 'header.php';

//Guests only!
if($user_type!=-1){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

//When there is input data
if(!empty($_POST["username"])){
	
	//Check if password is correct, index if correct, error otherwise
	try{
		$stmt = $db->prepare("
			SELECT id, username, password
			FROM cl_user
			WHERE username = ?");
		$stmt->execute(array($_POST["username"]));
		$result = $stmt->fetchAll();
		$passhash=$result[0]['password'];
		
		//Compare password hash
		if(crypt($_POST["password"], $passhash)==$passhash){
			successbox('Logging in. Please wait.');
			
			//Session set
			$_SESSION['uid']=$result[0]['id'];
			
			//Refresh
			header("Refresh:2;url=index.php");
			
		}
		else{
			errorbox('Invalid login information.');
		}
		
	}
	
	//Handle errors
	catch(PDOException $ex){
		errorbox('Login failed. Please try again later.');
	}
	
}
else{
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Log In</span>
				<form action="login.php" method="post" class="col s12 m10 l8 offset-m1 offset-l2">
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
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Log In</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
}
//Footer
include 'footer.php';
?>
