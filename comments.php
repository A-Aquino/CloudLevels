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

//CloudLevels View All Comments

//Header + Vars:
$page_title='Comments';
include 'header.php';

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
	header("Location:comments.php");
	include 'footer.php';
	exit(0);
}

$comments=null;
$num_rows=0;
try{
	
	//Get requested comments
	$where='';
	$args=array();
	
	//Author
	if(!empty($_GET["author"])){
		$where='WHERE cl_user.username = ?';
		array_push($args, $_GET["author"]);
	}
	
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_comment JOIN cl_user ON cl_comment.author=cl_user.id
		" . $where . "
		ORDER BY cl_comment.id DESC
		" . page_sql_calc(10));
	$stmt->execute($args);
	
	$comments = $stmt->fetchAll();
	
	$num_rows = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
	
}

//Handle errors
catch(PDOException $ex){
	errorbox('Failed to load comments.');
}
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Filters</span>
				<form action="comments.php" method="get">
					<div class="input-field col s12">
						<i class="fa fa-user prefix" aria-hidden="true"></i>
						<input id="author" name="author" type="text" value="<?php if(!empty($_GET["author"])){echo $_GET["author"];} ?>" class="validate">
						<label for="author">Author</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s10 l8 offset-s1 offset-l2" type="submit">Filter</button>
				</form><div class="row"></div>
			</div>
		</div>
		
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Comments</span>
				<div class="row"></div>
<?php
	//Comments
	foreach($comments as $comment){
		$append='';
		if($user_type==2) $append=' <span class="green-text">[' . $comment[4] . ']</span> <a href="comments.php?deletecomment=' . $comment[0] . '" class="red-text">[Delete]</a>';
		$append2=' <a href="index.php">[Link]</a>';
		if($comment['file']>0) $append2=' <a href="file.php?id=' . $comment['file'] . '">[Link]</a>';
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
							<p>" . $comment[3] . $append . $append2 . "</p>
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
		
<?php
//Footer
include 'footer.php';
?>
