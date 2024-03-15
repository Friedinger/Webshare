<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | Admin</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="/style.css">
</head>

<body>
	<h1>Webshare Admin</h1>
	Add a new webshare. Either a file share (then upload a file) or a link (then enter a link).
	<form method="post" enctype="multipart/form-data">
		<label>URI: </label><input type="text" name="uri" pattern="[a-z0-9_-]+"><br>
		<label>File: </label><input type="file" name="file"><br>
		<label>Link: </label><input type="text" name="link"><br>
		<label>Expire Date: </label><input type="datetime-local" name="expireDate" min="<?= date('Y-m-d\TH:i'); ?>"><br>
		<label>Password: </label><input type="text" name="password"><br>
		<input type="submit" value="Add share" name="submit"><br>
	</form>
	<p><share-status /></p>
	<div class="shareList">
		<table>
			<tr>
				<th><a href="?sort=uri">URI</a></th>
				<th><a href="?sort=type">Type</a></th>
				<th><a href="?sort=value">Value</a></th>
				<th><a href="?sort=password">Password</a></th>
				<th><a href="?sort=expireDate">Expire Date</a></th>
				<th><a href="?sort=createDate">Create Date</a></th>
				<th>Action</th>
			</tr>
			<share-list>
				<tr>
					<td><a href="<share-url />"><share-uri /></a></td>
					<td><share-type /></td>
					<td><share-value /></td>
					<td><share-password /></td>
					<td><share-expire /></td>
					<td><share-create /></td>
					<td><a href="<share-url />?action=delete">Delete</a></td>
				</tr>
			</share-list>
		</table>
	</div>
</body>

</html>