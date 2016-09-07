<?php ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>MSN ILM - Windows Live Messenger</title>
<style type="text/css">
body { padding: 0; margin: 0; background-color: #C0C0FF; }
.zero { clear: both; }
#main { width: 500px; padding: 20px; margin: 25px auto; background-color: #E0E0FF; font: 12px verdana, helvetica, sans-serif; }
#main h1 { padding: 5px 10px; margin: 0 10px; font-weight: bold; font-size: 16px; text-align: center; background-color: #A0A0FF; }
#main h2 { padding: 5px 10px; margin: 0 10px; font-weight: bold; font-size: 14px; text-align: center; background-color: #C0FFC0; }
#main input.input { border: 0; }
#main .test { padding: 10px; margin: 10px; background-color: #B0B0FF; text-align: center; }
#main .label { display: block; float: left; width: 200px; height: 15px; padding: 3px 5px; margin-bottom: 5px; font-weight: bold; text-align: left; background-color: #C0C0FF; }
#main .value { display: block; float: right; width: 230px; height: 15px; padding: 3px 5px; margin-bottom: 5px; font-weight: normal; text-align: left; background-color: #C0C0FF; overflow: hidden; }
#main .bigvalue { padding: 3px 5px; font: normal 12px monospace, helvetica, sans-serif; text-align: left; background-color: #C0C0FF; }
</style>
</head>
<body>
<div id="main">
	<h1>FILE UPLOAD TEST</h1>
	<form action="" method="post" enctype="multipart/form-data">
	<div class="test">
		<input class="input" name="test_file" type="file" class="field" size="40"/> <input type="submit" value="OK"/>
	</div>
	</form>
	<?php if (is_uploaded_file($_FILES["test_file"]["tmp_name"]) && ($fh = fopen($_FILES["test_file"]["tmp_name"], "r"))): ?>
	<h2>UPLOAD SUCCESSFULL : FILE DATA</h2>
	<div class="test">
		<span class="label">Original Name :</span><span class="value"><?php print $_FILES["test_file"]["name"] ?></span>
		<span class="label">Temp Name :</span><span class="value"><?php print $_FILES["test_file"]["tmp_name"] ?></span>
		<span class="label">Size :</span><span class="value"><?php print $_FILES["test_file"]["size"] ?> bytes</span>
		<span class="label">MIME Type:</span><span class="value"><?php print $_FILES["test_file"]["type"] ?></span>
		<span class="label">First 1000 bytes :</span>
		<div class="zero"></div>
		<div class="bigvalue"><?php print to_entities(fread($fh, 1000)) ?></div>
	</div>
	<?php fclose($fh); endif ?>
</div>
</body>
</html>