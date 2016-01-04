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

//CloudLevels Admin Control Panel

//Header + Vars:
$page_title='Administrator Control Panel';
include 'header.php';

//Admins only!
if($user_type!=2){
	errorbox('You do not have permission to view this page.');
	include 'footer.php';
	exit(0);
}

//When there is input data
if(!empty($_POST["name"])){
	
	//Create configuration file:

	//Open file
	$configfile = fopen("configuration.php", "w") or die('<div class="card hoverable red"><div class="card-content white-text"><p>ERROR: File <strong>configuration.php</strong> could not be written to server.</p></div></div>');
	fwrite($configfile, "<?php\n");

	//Write database values
	fwrite($configfile, "\$db_type='" . $db_type . "';\n");
	fwrite($configfile, "\$db_hostname='" . $db_hostname . "';\n");
	fwrite($configfile, "\$db_username='" . $db_username . "';\n");
	fwrite($configfile, "\$db_password='" . $db_password . "';\n");
	fwrite($configfile, "\$db_database='" . $db_database . "';\n");
	
	//Write default configuration stuff
	fwrite($configfile, "\$site_name='" . addslashes($_POST["name"]) . "';\n");
	fwrite($configfile, "\$site_desc='" . addslashes($_POST["description"]) . "';\n");
	fwrite($configfile, "\$game_url='" . addslashes($_POST["download"]) . "';\n");
	fwrite($configfile, "\$file_size_limit='" . addslashes($_POST["file_size"]) . "';\n");
	fwrite($configfile, "\$tags='" . addslashes($_POST["tag_list"]) . "';\n");
	fwrite($configfile, "\$theme='" . addslashes($_POST["theme"]) . "';\n");
	fwrite($configfile, "\$reg_question='" . addslashes($_POST["reg_question"]) . "';\n");
	fwrite($configfile, "\$reg_answer='" . addslashes($_POST["reg_answer"]) . "';\n");
	
	//Close file
	fwrite($configfile, "?>\n");
	fclose($configfile);
	
	//Message
	successbox('Settings updated. Please wait.');
	
	//Refresh
	header("Refresh:2");
	
}
else{
?>
		
		<br>
		<div class="container">
			<div class="row card hoverable">
				<span class="col s12 card-title <?php echo $theme ?> white-text center" style="font-size: 200%;">Administrator Control Panel</span>
				<form action="admin.php" method="post" class="col s6 offset-s3">
					<div class="input-field col s12">
						<i class="material-icons prefix">web</i>
						<input id="name" name="name" type="text" value="<?php echo $site_name ?>" class="validate" required>
						<label for="name">Site Name</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">comment</i>
						<textarea id="description" name="description" class="materialize-textarea" required><?php echo $site_desc ?></textarea>
						<label for="description">Site Description</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">file_download</i>
						<input id="download" name="download" type="url" value="<?php echo $game_url ?>" class="validate" required>
						<label for="download">Game Download Link</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">folder</i>
						<input id="file-size" name="file_size" type="number" value="<?php echo $file_size_limit ?>" class="validate" required>
						<label for="file-size">Max File Size Limit (Bytes)</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">cloud</i>
						<textarea id="tag-list" name="tag_list" class="materialize-textarea" required><?php echo $tags ?></textarea>
						<label for="tag-list">Tags</label>
					</div>
					<i class="material-icons small col s1">color_lens</i> 
					<div class="input-field col s11">
						<select id="theme" name="theme">
							<option value="light-blue"<?php if($theme=='light-blue') echo ' selected'; ?>>Light Blue</option>
							<option value="cyan"<?php if($theme=='cyan') echo ' selected'; ?>>Cyan</option>
							<option value="teal"<?php if($theme=='teal') echo ' selected'; ?>>Teal</option>
							<option value="green"<?php if($theme=='green') echo ' selected'; ?>>Green</option>
							<option value="light-green"<?php if($theme=='light-green') echo ' selected'; ?>>Light Green</option>
							<option value="lime"<?php if($theme=='lime') echo ' selected'; ?>>Lime</option>
							<option value="amber"<?php if($theme=='amber') echo ' selected'; ?>>Amber</option>
							<option value="orange"<?php if($theme=='orange') echo ' selected'; ?>>Orange</option>
							<option value="deep-orange"<?php if($theme=='deep-orange') echo ' selected'; ?>>Deep Orange</option>
							<option value="brown"<?php if($theme=='brown') echo ' selected'; ?>>Brown</option>
							<option value="grey"<?php if($theme=='grey') echo ' selected'; ?>>Grey</option>
							<option value="blue-grey"<?php if($theme=='blue-grey') echo ' selected'; ?>>Blue Grey</option>
							<option value="blue"<?php if($theme=='blue') echo ' selected'; ?>>Blue</option>
							<option value="indigo"<?php if($theme=='indigo') echo ' selected'; ?>>Indigo</option>
							<option value="deep-purple"<?php if($theme=='deep-purple') echo ' selected'; ?>>Deep Purple</option>
							<option value="purple"<?php if($theme=='purple') echo ' selected'; ?>>Purple</option>
							<option value="pink"<?php if($theme=='pink') echo ' selected'; ?>>Pink</option>
							<option value="red"<?php if($theme=='red') echo ' selected'; ?>>Red</option>
						</select>
						<label for="theme">Theme</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">lock</i>
						<input id="reg-question" name="reg_question" type="text" value="<?php echo $reg_question ?>" class="validate" required>
						<label for="reg-question">Registration Question</label>
					</div>
					<div class="input-field col s12">
						<i class="material-icons prefix">vpn_key</i>
						<input id="reg-answer" name="reg_answer" type="text" value="<?php echo $reg_answer ?>" class="validate" required>
						<label for="reg-answer">Answer to Registration Question</label>
					</div>
					<button class="btn waves-effect waves-light <?php echo $theme ?> col s12" type="submit">Save</button>
				</form><div class="row"></div>
			</div>
		</div>
		
<?php
}
//Footer
include 'footer.php';
?>
