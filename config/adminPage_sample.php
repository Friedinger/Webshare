<!DOCTYPE html>
<html>

<head>
	<title>Webshare</title>
	<link rel='shortcut icon' type='image/x-icon' href='favicon.css' />
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<link rel='stylesheet' type='text/css' href='style.css'>
</head>

<body>
	<h2>Webshare</h2>
	<form method="post" enctype="multipart/form-data">
		URI: <input type="text" name="uri" required><br>
		File: <input type="file" name="file"><br>
		Link: <input type="text" name="link"><br>
		Expire Date: <input type="datetime-local" name="expireDate" min="<?php echo date('Y-m-d\TH:i'); ?>"><br>
		<input type="submit" value="Submit" name="submit"><br>
	</form>
</body>

</html>