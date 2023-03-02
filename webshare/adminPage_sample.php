<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | Admin</title>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<style>
		body {
			color: hsl(0, 0%, 100%);
			background-color: hsl(0, 0%, 0%);
			font-family: Verdana, sans-serif;
			padding: 0.5rem;
			margin: 0;
			color-scheme: dark;
		}

		a {
			color: inherit;
			background-color: inherit;
			text-decoration: none;
		}

		a:hover {
			text-decoration: underline;
		}

		form {
			margin: 1rem 0;
		}

		form label {
			display: inline-block;
			max-width: 8rem;
			width: 100%;
		}

		form input {
			border: none;
			border-bottom: 1px solid hsl(0deg, 0%, 100%);
			padding: 1px 2px;
			background: none;
			color: inherit;
			outline: none;
			font-family: inherit;
			font-size: inherit;
			max-width: 25rem;
			width: 100%;
			margin-bottom: 0.5rem;
		}

		form input[type="file"]::file-selector-button {
			display: none;
		}

		form input[type="submit"] {
			margin-top: 0.5rem;
			border: none;
			background-color: hsl(0, 0%, 20%);
			max-width: 33.25rem;
			width: 100%;
		}

		form input[type="datetime-local"]::-webkit-calendar-picker-indicator {
			color: hsl(0deg, 0%, 100%);
		}

		form input[type="submit"]:hover {
			background-color: hsl(0, 0%, 27%);
			cursor: pointer;
		}

		.copy-icon {
			box-sizing: border-box;
			position: relative;
			display: inline-block;
			width: 16px;
			height: 16px;
			background-color: inherit;
		}

		.copy-icon::before {

			content: "";
			display: block;
			box-sizing: border-box;
			border: 2px solid;
			position: absolute;
			bottom: 0px;
			right: 0px;
			width: 12px;
			height: 16px;
		}

		.copy-icon::after {
			content: "";
			display: block;
			box-sizing: border-box;
			border: 2px solid;
			position: absolute;
			bottom: -4px;
			right: 4px;
			width: 12px;
			height: 16px;
			background-color: inherit;
		}

		.shareList {
			width: 100%;
			overflow-y: hidden;
			overflow-x: auto;
		}

		.shareList table {
			border-collapse: collapse;
			table-layout: fixed;
			width: 100%;
			white-space: nowrap;
		}

		.shareList th {
			background-color: hsl(0, 0%, 20%);
		}

		.shareList th,
		.shareList td {
			border: 2px solid hsl(0, 0%, 20%);
			padding: 0.1rem 0.5rem;
			overflow: hidden;
		}
	</style>
</head>

<body>
	<h1>Webshare Admin</h1>
	Add a new webshare. Either a file share (then upload a file) or a link (then enter a link).
	<form method="post" enctype="multipart/form-data">
		<label>URI: </label><input type="text" name="uri" required><br>
		<label>File: </label><input type="file" name="file"><br>
		<label>Link: </label><input type="url" name="link"><br>
		<label>Expire Date: </label><input type="datetime-local" name="expireDate" min="<?php echo date('Y-m-d\TH:i'); ?>"><br>
		<label>Password: </label><input type="text" name="password"><br>
		<input type="submit" value="Add share" name="submit"><br>
	</form>
	<?php print($message) ?>
	<div class="shareList">
		<table>
			<th style="width: 50px;">URI</th>
			<th style="width: 300px;">File</th>
			<th style="width: 300px;">Link</th>
			<th style="width: 90px;">Password</th>
			<th style="width: 180px;">Expire Date</th>
			<th style="width: 180px;">Create Date</th>
			<?php print($shareList) ?>
		</table>
	</div>
</body>

</html>