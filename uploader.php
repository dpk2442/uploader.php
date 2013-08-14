<?php

/**************
 ** SETTINGS **
 **************/

// The directory to list for possible paths. 
define(DIR, "..");

// Disallowed mime types
$blacklistedMimes = array(
	"text/php"
);

// Disallowed extensions
$blacklistedExts  = array(
	"php"
);


/***********************
 ** EDIT WITH CAUTION **
 ***********************/

$errors = array();
$successes = array();

if (isset($_POST["submit"])) {
	$file = $_FILES["file"];
	$path = isset($_POST["path"]) ? $_POST["path"] : ".";
	for ($i = 0; $i < sizeof($file["name"]); $i++) {
		$ext = end(explode(".", $file["name"][$i]));
		if (in_array($file["type"][$i], $blacklistedMimes) || in_array($ext, $blacklistedExts)) {
			$errors[$file["name"][$i]] = "Invalid file type.";
			continue;
		}
		if (move_uploaded_file($file["tmp_name"][$i], $path . DIRECTORY_SEPARATOR . $file["name"][$i])) {
			$successes[] = $file["name"][$i];
		} else {
			$errors[$file["name"][$i]] = "Unable to upload file.";
		}
	}
}

function dirToArray($dir, $starting_dir = false) {
	$contents = array();
	if (!$starting_dir) {
		if (preg_match("%" . DIRECTORY_SEPARATOR . "$%", $dir)) $starting_dir = $dir;
		else $starting_dir = $dir . DIRECTORY_SEPARATOR;
	}
	foreach (scandir($dir) as $node) {
		$path = $dir . DIRECTORY_SEPARATOR . $node;
		if ($node == '.' || $node == ".." || realpath($path) == dirname(__FILE__))  continue;
		if (is_dir($path)) {
			$disp_path = substr($path, strlen($starting_dir));
			$contents[] = array($path, $disp_path);
			$subs = dirToArray($path, $starting_dir);
			$contents = array_merge($contents, $subs);
		}
	}
	return $contents;
}
$dirs = dirToArray(DIR);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Uploader</title>
</head>
<body>

	<h1>Uploader</h1>
	<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" enctype="multipart/form-data">
		<?php if ($dirs): ?>
		<p>
			<label for="path">Upload to:</label><br>
			<select id="path" name="path">
				<?php foreach ($dirs as $dir): ?>
				<option value="<?php echo $dir[0]; ?>"><?php echo $dir[1]; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<? endif; ?>
		<?php if ($errors): ?>
		<p style="color: red">
			<?php foreach ($errors as $file => $error): ?>
				Could not upload file "<?php echo $file; ?>": <?php echo $error; ?><br>
			<?php endforeach; ?>
		</p>
		<?php endif; ?>
		<?php if ($successes): ?>
		<p style="color: green">
			<?php foreach ($successes as $file): ?>
				File "<?php echo $file; ?>" uploaded successfully.<br>
			<?php endforeach; ?>
		</p>
		<?php endif; ?>
		<p id="files"><input type="file" name="file[]" /></p>
		<p><a href="#" id="addfile">Add File</a></p>
		<p><input type="submit" name="submit" value="Upload" /></p>
	</form>

	<script>
		var files = document.getElementById("files")
		function clicked(e) {
			e.preventDefault()
			newInput = document.createElement("input")
			newInput.type = "file"
			newInput.name = "file[]"
			br = document.createElement("br")
			files.appendChild(br)
			files.appendChild(newInput)
		}
		var addfile = document.getElementById("addfile")
		if (addfile.addEventListener) {
			addfile.addEventListener("click", clicked)
		} else if (addfile.attachEvent) {
			addfile.attachEvent("onclick", clicked)
		}
	</script>

</body>
</html>