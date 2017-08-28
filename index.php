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

//CloudLevels Front Page

//Header + Vars:
$page_title='';
include 'header.php';

//When there is comment data
if($user_type!=-1&&!empty($_POST["comment"])){
	
	try{
		date_default_timezone_set('America/New_York');
		$stmt = $db->prepare("
			INSERT INTO cl_comment(author, file, date, ip, comment)
			VALUES(?,?,?,?,?)");
		$stmt->execute(array($_SESSION['uid'], 0, date("F j, Y"), $_SERVER['REMOTE_ADDR'], nl2br(htmlspecialchars($_POST["comment"]))));
	}
	
	//Handle errors
	catch(PDOException $ex){
		errorbox('Failed to post comment.');
		include 'footer.php';
		exit(0);
	}
	
	successbox('Your comment has been posted. Please wait.');
	header("Location:index.php");
	include 'footer.php';
	exit(0);
	
}

//Delete comments
if($user_type==2&&!empty($_GET["deletecomment"])){
	try{
			$stmt = $db->prepare("
				DELETE FROM cl_comment
				WHERE id = ?");
			$stmt->execute(array($_GET["deletecomment"]));
	}
	//Handle errors
	catch(PDOException $ex){
		errorbox('Failed to delete comment.');
		include 'footer.php';
		exit(0);
	}
	successbox('Comment deleted. Please wait.');
	header("Location:index.php");
	include 'footer.php';
	exit(0);
}

$result1=null;
$result2=null;
$result3=null;
$comments=null;
$num_rows=0;
try{
	
	//Featured
	$stmt = $db->prepare("
		SELECT *
		FROM cl_file JOIN cl_user ON cl_file.author=cl_user.id
		WHERE featured=1
		ORDER BY cl_file.id DESC
		LIMIT 4");
	$stmt->execute();
	
	$result1 = $stmt->fetchAll();
	
	//Popular
	$stmt = $db->prepare("
		SELECT *
		FROM cl_file JOIN cl_user ON cl_file.author=cl_user.id
		ORDER BY likes DESC
		LIMIT 4");
	$stmt->execute();
	
	$result2 = $stmt->fetchAll();
	
	//Recent
	$stmt = $db->prepare("
		SELECT *
		FROM cl_file JOIN cl_user ON cl_file.author=cl_user.id
		ORDER BY cl_file.id DESC
		LIMIT 4");
	$stmt->execute();
	
	$result3 = $stmt->fetchAll();
	
	//Comments
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_comment JOIN cl_user ON cl_comment.author=cl_user.id
		WHERE file = 0
		ORDER BY cl_comment.id DESC
		" . page_sql_calc(10));
	$stmt->execute();
	$comments = $stmt->fetchAll();
	$num_rows = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
	
}

//Handle errors
catch(PDOException $ex){
	errorbox('Failed to load file information.');
}
?>
		
		<div class="section no-pad-bot" id="index-banner">
			<div class="container">
				<br><br>
				<h1 class="header center <?php echo $theme ?>-text"><?php echo $site_name ?></h1>
				<div class="row center">
					<h5 class="header col s12 light"><?php echo $site_desc ?></h5>
				</div>
				<div class="row center">
					<a href="<?php echo $game_url ?>" id="download-button" class="btn-large waves-effect waves-light <?php echo $theme ?>">Download Game</a>
				</div>
				<br><br>
			</div>
		</div>
		<div class="container">
			<div class="card hoverable row">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Featured</span>
				<div class="card-content"><br><br>
<?php
if(!empty($result1)){
	foreach($result1 as $file){
		filebox($file);
	}
}
?>
				</div>
				
				<div class="col s12 card-action">
					<a class="<?php echo $theme ?>-text right" href="browse.php?featured=on">See more...</a>
				</div>
				
			</div>
		</div>
		<div class="container">
			<div class="card hoverable row">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Popular</span>
				<div class="card-content"><br><br>
<?php
if(!empty($result2)){
	foreach($result2 as $file){
		filebox($file);
	}
}
?>
				</div>
				
				<div class="col s12 card-action">
					<a class="<?php echo $theme ?>-text right" href="browse.php?sort=popular">See more...</a>
				</div>
				
			</div>
		</div>
		<div class="container">
			<div class="card hoverable row">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Recent</span>
				<div class="card-content"><br><br>
<?php
if(!empty($result3)){
	foreach($result3 as $file){
		filebox($file);
	}
}
?>
				</div>
				<div class="col s12 card-action">
					<a class="<?php echo $theme ?>-text right" href="browse.php?sort=recent">See more...</a>
				</div>
			</div>
		</div>
<?php if(!empty($comments)){ ?>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Comments</span>
				<div class="row"></div>
<?php
	//Comments
	foreach($comments as $comment){
		$append='';
		if($user_type==2) $append=' <span class="green-text">[' . $comment[4] . ']</span> <a href="index.php?deletecomment=' . $comment[0] . '" class="red-text">[Delete]</a>';
		echo "
				<div class=\"row\">
					<div class=\"card hoverable col s3 m2 offset-s1 offset-m1 center\">
						<div class=\"card-content\">
							<p><i class=\"medium fa fa-user\" aria-hidden=\"true\"></i> 
							<p>" . memberlink($comment['username'], $comment['usergroup']) . "</p>
						</div>
					</div>
					<div class=\"card hoverable col s6 m7 offset-s1 offset-m1\">
						<div class=\"card-content\">
							<p style=\"word-break: break-all;\">" . $comment['comment'] . "</p>
							<br>
							<p>" . $comment[3] . $append . "</p>
						</div>
					</div>
				</div>
";
	}
//Pages
pagination($num_rows, 10, $theme);
?>
			</div>
		</div>
		
<?php }
if($user_type==0||$user_type==2){ ?>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">New Comment</span>
				<form action="index.php" method="post" class="col s12 m10 offset-m1 l8 offset-l2">
					<div class="input-field col s12">
						<i class="fa fa-comment prefix" aria-hidden="true"></i>
						<textarea id="comment" name="comment" class="materialize-textarea" required></textarea>
						<label for="comment">Comment</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Post</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
}
//Footer
include 'footer.php';
?>
