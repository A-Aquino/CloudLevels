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

//CloudLevels View File Page

//Header + Vars:
include 'header.php';

//ID Check
if($file_get==NULL){
	errorbox('Invalid file.');
	include 'footer.php';
	exit(0);
}

//When there is comment data
if($user_type!=-1&&!empty($_POST["comment"])){
	
	try{
		date_default_timezone_set('America/New_York');
		$stmt = $db->prepare("
			INSERT INTO cl_comment(author, file, date, ip, comment)
			VALUES(?,?,?,?,?)");
		$stmt->execute(array($_SESSION['uid'], $file_get['id'], date("F j, Y"), $_SERVER['REMOTE_ADDR'], htmlspecialchars($_POST["comment"])));
	}
	
	//Handle errors
	catch(PDOException $ex){
		errorbox('Something happened.');
		include 'footer.php';
		exit(0);
	}
	
	successbox('Your comment has been posted. Please wait.');
	header("Location:file.php?id=" . $file_get['id']);
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
		errorbox('Something happened.');
		include 'footer.php';
		exit(0);
	}
	successbox('Comment deleted. Please wait.');
	header("Location:file.php?id=" . $file_get['id']);
	include 'footer.php';
	exit(0);
}

//Check if user likes this file
$file_liked=FALSE;
if($user_type!=-1){
	try{
		$stmt = $db->prepare("
				SELECT *
				FROM cl_like
				WHERE author = ? AND file = ?");
		$stmt->execute(array($_SESSION['uid'], $file_get['id']));
		$result = $stmt->fetchAll();
		if(!empty($result)) $file_liked=TRUE;
	}
	//Handle errors
	catch(PDOException $ex){
		errorbox('Something happened.');
		include 'footer.php';
		exit(0);
	}
}

//If something is being done to the file
if(!empty($_GET["action"])){
	try{
	
		//Like
		if($user_type!=-1&&$_GET["action"]=='like'){
			$db->beginTransaction();
			if($file_liked){
				$stmt = $db->prepare("
					DELETE FROM cl_like
					WHERE author = ? AND file = ?");
				$stmt->execute(array($_SESSION['uid'], $file_get['id']));
				$stmt = $db->prepare("
					UPDATE cl_file
					SET likes = likes-1
					WHERE id = ?");
				$stmt->execute(array($file_get['id']));
				successbox('You no longer like this file. Please wait.');
			}
			else{
				$stmt = $db->prepare("
					INSERT INTO cl_like(author, file)
					VALUES(?,?)");
				$stmt->execute(array($_SESSION['uid'], $file_get['id']));
				$stmt = $db->prepare("
					UPDATE cl_file
					SET likes = likes+1
					WHERE id = ?");
				$stmt->execute(array($file_get['id']));
				successbox('You liked this file. Please wait.');
			}
			$db->commit();
			header("Location:file.php?id=" . $file_get['id']);
			include 'footer.php';
			exit(0);
		}
		
		//Admin stuff
		else if($user_type==2){
			
			//Delete
			if($_GET["action"]=='delete'){
				
				$stmt = $db->prepare("
					DELETE FROM cl_tag
					WHERE file = ?");
				$stmt->execute(array($file_get['id']));
				$stmt = $db->prepare("
					DELETE FROM cl_like
					WHERE file = ?");
				$stmt->execute(array($file_get['id']));
				$stmt = $db->prepare("
					DELETE FROM cl_comment
					WHERE file = ?");
				$stmt->execute(array($file_get['id']));
				$stmt = $db->prepare("
					DELETE FROM cl_file
					WHERE id = ?");
				$stmt->execute(array($file_get['id']));
				
				unlink('/data/' . $file_get['id'] . ' ' . $file_get['name'] . '.zip');
				unlink('/data/' . $file_get['id'] . '.png');
				successbox('File deleted.');
				include 'footer.php';
				exit(0);
			}
			
			//Feature
			else if($_GET["action"]=='feature'){
				if($file_get['featured']){
					$stmt = $db->prepare("
						UPDATE cl_file
						SET featured = 0
						WHERE id = ?");
					$stmt->execute(array($file_get['id']));
					successbox('File is no longer featured. Please wait.');
				}
				else{
					$stmt = $db->prepare("
						UPDATE cl_file
						SET featured = 1
						WHERE id = ?");
					$stmt->execute(array($file_get['id']));
					successbox('File featured. Please wait.');
				}
				header("Location:file.php?id=" . $file_get['id']);
				include 'footer.php';
				exit(0);
			}
			
			else
				errorbox('Nothing happened.');
		}
		else
			errorbox('Nothing happened.');
		
	}
	catch(PDOException $ex){
		errorbox('Something happened.');
		include 'footer.php';
		exit(0);
	}
}
	
$result=null;
$comments=null;
$num_rows=0;
$file_author='';
try{
	
	//Get author
	$stmt = $db->prepare("
			SELECT username
			FROM cl_user
			WHERE id = ?");
	$stmt->execute(array($file_get["author"]));
	$result = $stmt->fetchAll();
	$file_author=$result[0]['username'];
	
	//Get tags
	$stmt = $db->prepare("
			SELECT tag
			FROM cl_tag
			WHERE file = ?");
	$stmt->execute(array($file_get["id"]));
	$result = $stmt->fetchAll();
	
	//Get comments
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_comment JOIN cl_user ON cl_comment.author=cl_user.id
		WHERE file = ?
		ORDER BY cl_comment.id DESC
		" . page_sql_calc(10));
	$stmt->execute(array($file_get["id"]));
	$comments = $stmt->fetchAll();
	$num_rows = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
	
}

//Handle errors
catch(PDOException $ex){
	errorbox('Something happened.');
	echo $ex->getMessage();
	include 'footer.php';
	exit(0);
}
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;"><?php if($file_get['featured']==1){ echo '<i class="material-icons">grade</i> '; } echo $file_get['name']; ?></span>
				<div class="row"></div>
				<div class="row">
					<div class="col s5 offset-s1">
						<img class="responsive-img" src="/data/<?php echo $file_get['id'] ?>.png">
<?php if($user_type==2){ ?>
						<br><a href="file.php?id=<?php echo $file_get['id'] ?>&action=feature" class="btn waves-effect waves-light <?php if($file_get['featured']){ echo 'red">Un-Feature'; } else { echo 'green">Feature'; } ?></a>
						<a href="file.php?id=<?php echo $file_get['id'] ?>&action=delete" class="btn waves-effect waves-light red">Delete</a>
<?php } ?>
					</div>
					<div class="col s5">
						<p>By <a href="browse.php?author=<?php echo $file_author ?>"><?php echo $file_author ?></a><?php if($user_type==2){ echo ' <span class="green-text">[' . $file_get['ip'] . ']</span>'; } ?></p>
						<p><?php echo $file_get['date'] ?></p>
						<p><i class="tiny material-icons">system_update_alt</i> <?php echo $file_get['downloads'] ?> <i class="tiny material-icons">thumb_up</i> <?php echo $file_get['likes'] ?></p>
						<p><?php echo $file_get['description'] ?></p>
						<p><?php 
foreach($result as $tag){
	echo '<a href="browse.php?tags[]=' . $tag['tag'] . '" class="chip">' . $tag['tag'] . '</a>';
}
						?></p>
						<a href="download.php?id=<?php echo $file_get['id'] ?>" class="btn waves-effect waves-light <?php echo $theme ?>">Download</a>
<?php if($user_type==0||$user_type==2){ ?>
						<a href="file.php?id=<?php echo $file_get['id'] ?>&action=like" class="btn waves-effect waves-light <?php if($file_liked){ echo 'red'; } else { echo $theme; } ?>"><i class="tiny material-icons"><?php if($file_liked) echo 'thumb_down'; else echo 'thumb_up'; ?></i></a>
<?php } ?>
					</div>
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
		if($user_type==2) $append=' <span class="green-text">[' . $comment[4] . ']</span> <a href="file.php?id=' . $file_get['id'] . '&deletecomment=' . $comment[0] . '" class="red-text">[Delete]</a>';
		echo "
				<div class=\"row\">
					<div class=\"card hoverable col s2 offset-s1 center\">
						<div class=\"card-content\">
							<p><i class=\"large material-icons\">account_circle</i> 
							<p><a href=\"browse.php?author=" . $comment['username'] . "\">" . $comment['username'] . "</a></p>
						</div>
					</div>
					<div class=\"card hoverable col s7 offset-s1\">
						<div class=\"card-content\">
							<p>" . $comment['comment'] . "</p>
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
				<form action="file.php?id=<?php echo $file_get['id'] ?>" method="post" class="col s6 offset-s3">
					<div class="input-field col s12">
						<i class="material-icons prefix">comment</i>
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
