<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | Delete</title>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<link rel="stylesheet" href="/style.css">
</head>

<body>
	<h1>Webshare Delete</h1>
	<share-form>
		<p>Do you really want to delete the share <i><share-uri /></i> ?</p>
		<form method="post">
			<label>Share uri: </label><input type="text" name="uri"><br>
			<input type="submit" name="submit" value="Delete permanently"><br>
		</form>
	</share-form>
	<share-status />
	<br>
	<p><a href="<share-installPath /><share-adminLink />">Back to Admin Page</a></p>
</body>

</html>