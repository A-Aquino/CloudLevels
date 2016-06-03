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
$page_title='All Comments';
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
		errorbox('Something happened.');
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
	
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_comment JOIN cl_user ON cl_comment.author=cl_user.id
		ORDER BY cl_comment.id DESC
		" . page_sql_calc(10));
	$stmt->execute();
	$comments = $stmt->fetchAll();
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
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">All Comments</span>
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
					<div class=\"card hoverable col s2 offset-s1 center\">
						<div class=\"card-content\">
							<p><i class=\"large fa fa-user\" aria-hidden=\"true\"></i> 
							<p><a href=\"browse.php?author=" . $comment['username'] . "\">" . $comment['username'] . "</a></p>
						</div>
					</div>
					<div class=\"card hoverable col s7 offset-s1\">
						<div class=\"card-content\">
							<p>" . $comment['comment'] . "</p>
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
