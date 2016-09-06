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

//CloudLevels Upload File Page

//Header + Vars:
$page_title='Upload';
include 'header.php';

//Members only!
if($user_type==-1||$user_type==1){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

//When there is input data
if(!empty($_POST["title"])){
	
	//Upload file
	
	//Check for errors
	if($_FILES['file']['error']!=UPLOAD_ERR_OK||$_FILES['screenshot']['error']!=UPLOAD_ERR_OK){
		errorbox('Upload failed. Please try again later.');
		include 'footer.php';
		exit(0);
	}
	
	//Verify file sizes
	if(max($_FILES['file']['size'], $_FILES['screenshot']['size'])>$file_size_limit){
		errorbox('You have exceeded the maximum file size of ' . $file_size_limit . '.');
		include 'footer.php';
		exit(0);
	}
	
	//File must be a ZIP
	if(strtolower(substr($_FILES['file']['name'], -4))!='.zip'){
		errorbox('You must upload a file of type ZIP.');
		include 'footer.php';
		exit(0);
	}
	
	//Image must be a PNG
	if(strtolower(substr($_FILES['screenshot']['name'], -4))!='.png'){
		errorbox('You must upload a screenshot of type PNG.');
		include 'footer.php';
		exit(0);
	}
	
	$last_id=0;
	try{
		
		//Create database entries
		
		//Begin
		$db->beginTransaction();
		
		//File table entry
		date_default_timezone_set('America/New_York');
		$stmt = $db->prepare("
			INSERT INTO cl_file(name, author, date, ip, description)
			VALUES(?,?,?,?,?)");
		$stmt->execute(array(htmlspecialchars($_POST["title"]), $_SESSION['uid'], date("F j, Y"), $_SERVER['REMOTE_ADDR'], htmlspecialchars($_POST["description"])));
		
		//Get file id
		$last_id = $db->lastInsertId();
		
		//Tag table entry
		if(!empty($_POST["tags"])){
			foreach($_POST["tags"] as $tag){
				$stmt = $db->prepare("
					INSERT INTO cl_tag(file, tag)
					VALUES(?,?)");
				$stmt->execute(array($last_id, htmlspecialchars($tag)));
			}
		}
		
		//Increment upload count
		$stmt = $db->prepare("
			UPDATE cl_user
			SET uploads = uploads+1
			WHERE id = ?");
		$stmt->execute(array($_SESSION['uid']));
		
		//End
		$db->commit();
		
	}
	//Handle errors
	catch(PDOException $ex){
		
		$db->rollBack();
		errorbox('Upload failed. Please try again later.');
		include 'footer.php';
		exit(0);
	}
	
	//Actually upload the files
	move_uploaded_file($_FILES["file"]["tmp_name"], "data/" . $last_id . ".zip");
	move_uploaded_file($_FILES["screenshot"]["tmp_name"], "data/" . $last_id . ".png");
	
	//Success!
	successbox('File uploaded. Please wait.');
	
	//Refresh
	header("Refresh:2;url=file.php?id=" . $last_id);
	
}
else{
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Upload File</span>
				<form action="upload.php" method="post" enctype="multipart/form-data" class="col s12 m10 l8 offset-m1 offset-l2">
					<div class="file-field input-field">
						<div class="btn <?php echo $theme ?>">
							<span>File (<?php echo ($file_size_limit/1000000); ?>MB MAX)</span>
							<input type="file" name="file" accept="application/x-zip-compressed" required>
						</div>
						<div class="file-path-wrapper">
							<input class="file-path validate" type="text">
						</div>
					</div>
					<div class="file-field input-field">
						<div class="btn <?php echo $theme ?>">
							<span>Screenshot</span>
							<input type="file" name="screenshot" accept="image/png" required>
						</div>
						<div class="file-path-wrapper">
							<input class="file-path validate" type="text">
						</div>
					</div>
					<div class="input-field col s12">
						<i class="fa fa-commenting-o prefix" aria-hidden="true"></i>
						<input id="title" name="title" type="text" class="validate" required>
						<label for="title">Title</label>
					</div>
					<div class="input-field col s12">
						<i class="fa fa-comment prefix" aria-hidden="true"></i>
						<textarea id="description" name="description" class="materialize-textarea" required></textarea>
						<label for="description">Description</label>
					</div>
					<i class="fa fa-cloud small col s1" aria-hidden="true"></i> 
					<div class="input-field col s11">
						<select id="tags" name="tags[]" multiple>
							<option value="" disabled selected>Select Tags</option>
							<?php 
								$get_tags=explode("\n", $tags);
								foreach($get_tags as $tag)
									echo '<option value="' . trim($tag) . '">' . trim($tag) . '</option>';
							?>

						</select>
						<label for="tags">Tags</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Upload</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
}
//Footer
include 'footer.php';
?>
