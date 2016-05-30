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

//CloudLevels Browse Page

//Header + Vars:
$page_title='Browse';
include 'header.php';

$result=null;
$num_rows=0;
try{
	
	//Get requested files
	$where='WHERE ';
	$args=array();
	
	//Title
	if(!empty($_GET["title"])){
		$where.=' AND name LIKE ?';
		array_push($args, "%" . $_GET["title"] . "%");
	}
	
	//Author
	if(!empty($_GET["author"])){
		$where.=' AND username = ?';
		array_push($args, $_GET["author"]);
	}
	
	//Featured
	if(!empty($_GET["featured"]))
		$where.=' AND featured=1';
	
	//Liked
	if(!empty($_GET["liked"])){
		$where.=' AND cl_file.id IN (SELECT file FROM cl_like WHERE author = ? ) ';
		array_push($args, $_SESSION['uid']);
	}
	
	//Tags
	if(!empty($_GET["tags"])){
		foreach($_GET["tags"] as $tag){
			$where.=' AND cl_file.id IN (SELECT file FROM cl_tag WHERE tag = ? ) ';
			array_push($args, $tag);
		}
	}
	
	//Order by
	$order='';
	if(empty($_GET["sort"]))
		$order='cl_file.id';
	else if($_GET["sort"]=='popular')
		$order='likes';
	else if($_GET["sort"]=='downloaded')
		$order='downloads';
	else
		$order='cl_file.id';
	
	//No query case
	if($where=='WHERE '){
		$where='';
	}
	
	//Remove first AND from where string
	$where=preg_replace('/AND/', '', $where, 1);
	
	$stmt = $db->prepare("
		SELECT SQL_CALC_FOUND_ROWS *
		FROM cl_file JOIN cl_user ON cl_file.author=cl_user.id
		" . $where . "
		ORDER BY " . $order .  " DESC
		" . page_sql_calc(16));
	$stmt->execute($args);
	
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
				<form action="browse.php" method="get">
					<div class="input-field col s6">
						<i class="fa fa-commenting-o prefix" aria-hidden="true"></i>
						<input id="title" name="title" type="text" value="<?php if(!empty($_GET["title"])){echo $_GET["title"];} ?>" class="validate">
						<label for="title">Title</label>
					</div>
					<div class="input-field col s6">
						<select id="tags" name="tags[]" multiple>
							<option value="" disabled selected>Select Tags</option>
							<?php 
								$get_tags=explode("\n", $tags);
								foreach($get_tags as $tag){
									if(!empty($_GET["tags"])&&in_array(trim($tag), $_GET["tags"]))
										echo '<option value="' . trim($tag) . '" selected>' . trim($tag) . '</option>';
									else
										echo '<option value="' . trim($tag) . '">' . trim($tag) . '</option>';
								}
							?>

						</select>
						<label for="tags">Tags</label>
					</div>
					<div class="input-field col s6">
						<i class="fa fa-user prefix" aria-hidden="true"></i>
						<input id="author" name="author" type="text" value="<?php if(!empty($_GET["author"])){echo $_GET["author"];} ?>" class="validate">
						<label for="author">Author</label>
					</div>
					<div class="input-field col s6">
						<select id="sort" name="sort" required>
							<option value="recent"<?php if(!empty($_GET["sort"])&&$_GET["sort"]=='recent') echo ' selected'; ?>>Most Recent</option>
							<option value="popular"<?php if(!empty($_GET["sort"])&&$_GET["sort"]=='popular') echo ' selected'; ?>>Most Popular</option>
							<option value="downloaded"<?php if(!empty($_GET["sort"])&&$_GET["sort"]=='downloaded') echo ' selected'; ?>>Most Downloaded</option>
						</select>
						<label for="sort">Sort</label>
					</div>
					<div class="switch col s2">
						<label>
						All
						<input type="checkbox" name="featured"<?php if(!empty($_GET["featured"])) echo ' checked'; ?>>
						<span class="lever"></span>
						Featured
						</label>
					</div>
<?php if($user_type==0||$user_type==2){ ?>
					<div class="switch col s2">
						<label>
						All
						<input type="checkbox" name="liked"<?php if(!empty($_GET["liked"])) echo ' checked'; ?>>
						<span class="lever"></span>
						Liked
						</label>
					</div>
<?php } ?>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s4<?php if($user_type==-1){ echo ' offset-s2'; } ?>" type="submit">Filter</button>
				</form><div class="row"></div>
			</div>
		</div>
		
		<div class="container">
			<div class="card hoverable row">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Browse</span>
				<div class="card-content"><br><br>
<?php
if(!empty($result)){
	foreach($result as $file){
		filebox($file);
	}
}
?>
				</div>
<?php
//Pages
pagination($num_rows, 16, $theme);
?>
			</div>
		</div>
		
<?php
//Footer
include 'footer.php';
?>
