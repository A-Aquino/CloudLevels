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

//CloudLevels Member Management

//Header + Vars:
$page_title='Manage Members';
include 'header.php';

//Admins only!
if($user_type!=2){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

$result=null;
$num_rows=0;
try{
	
	//Modify members if specified
	if(!empty($_GET["user"])){
		
		if($_GET["user"]==$user_name){
			errorbox('You can not demote yourself!');
		}
		
		else{
			
			//SQL Stuff
			$stmt = $db->prepare("
				UPDATE cl_user
				SET usergroup = ?
				WHERE username = ?");
			$stmt->execute(array($_GET["update"], $_GET["user"]));
			
			if($_GET["update"]==0) successbox($_GET["user"] . " is now a regular member.");
			else if($_GET["update"]==1) successbox($_GET["user"] . " has been banned. Good bye!");
			else if($_GET["update"]==2) successbox($_GET["user"] . " is now an administrator.");
			
		}
		
	}
	
	//Get Member data
	$append='id';
	$append2='';
	
	if(!empty($_GET["sort"])&&$_GET["sort"]=='uploaded'){
		$append='uploads';
	}
	if(!empty($_GET["username"])){
		$append2='WHERE username = ?';
	}
	
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_user
		" . $append2 . "
		ORDER BY " . $append .  " DESC
		" . page_sql_calc(25));
	if(!empty($_GET["username"])){
		$stmt->execute(array($_GET["username"]));
	}
	else{
		$stmt->execute();
	}
	$result = $stmt->fetchAll();
	
	$num_rows = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
	
}

//Handle errors
catch(PDOException $ex){
	errorbox('Something happened.');
}
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Filters</span>
				<form action="members.php" method="get">
					<div class="input-field col s6">
						<i class="fa fa-user prefix" aria-hidden="true"></i>
						<input id="username" name="username" type="text" value="<?php if(!empty($_GET["username"])){echo $_GET["username"];} ?>" class="validate">
						<label for="username">Username</label>
					</div>
					<div class="input-field col s6">
						<select id="sort" name="sort" required>
							<option value="recent">Most Recent</option>
							<option value="uploaded"<?php if(!empty($_GET["sort"])&&$_GET["sort"]=='uploaded') echo ' selected'; ?>>Most Uploads</option>
						</select>
						<label for="sort">Sort</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s10 l8 offset-s1 offset-l2" type="submit">Filter</button>
				</form><div class="row"></div>
			</div>
		</div>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Members</span>
				<div class="row">
					<p class="center col s12"><strong><span class="blue-text">Member</span> | <span class="green-text">Admin</span> | <span class="red-text">Banned</span></strong></p>
					<table class="col s10 offset-s1 centered striped">
						<thead>
							<tr>
								<th>Username</th>
								<th>Uploads</th>
								<th>Join Date</th>
								<th>IP Address</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody><?php
foreach($result as $user){
	$append=' class="blue-text"';
	$append2="<a href=\"members.php?update=1&user=" . $user['username'] . "\" class=\"red-text\">[Ban]</a> <a href=\"members.php?update=2&user=" . $user['username'] . "\" class=\"green-text\">[Promote]</a>";
	if($user['usergroup']==1){
		$append=' class="red-text"';
		$append2="<a href=\"members.php?update=0&user=" . $user['username'] . "\" class=\"blue-text\">[Unban]</a>";
	}
	else if($user['usergroup']==2){
		$append=' class="green-text"';
		$append2="<a href=\"members.php?update=0&user=" . $user['username'] . "\" class=\"blue-text\">[Demote]</a>";
	}
	echo "
							<tr>
								<td><strong><a href=\"browse.php?author=" . $user['username'] . "\"" . $append . ">" . $user['username'] . "</a></strong></td>
								<td>" . $user['uploads'] . "</td>
								<td>" . $user['date'] . "</td>
								<td>" . $user['ip'] . "</td>
								<td>" . $append2 . "</td>
							</tr>";
	
}
?>

						</tbody>
					</table>
				</div>
<?php
//Pages
pagination($num_rows, 25, $theme);
?>
			</div>
		</div>
		
<?php
//Footer
include 'footer.php';
?>
