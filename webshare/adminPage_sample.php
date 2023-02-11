<!DOCTYPE html>
<html>

<head>
	<title>Webshare | Admin</title>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<style>
		body {
			color: hsl(0, 0%, 100%);
			background-color: hsl(0, 0%, 0%);
			font-family: Verdana, sans-serif;
			display: flex;
			flex-direction: column;
			min-height: calc(100% - 1rem);
			padding: 0.5rem;
			margin: 0;
			color-scheme: dark;
		}

		form {
			margin-top: 1rem;
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
			;
		}

		form input[type="submit"]:hover {
			background-color: hsl(0, 0%, 27%);
			cursor: pointer;
		}
	</style>
</head>

<body>
	<h1>Webshare Admin</h1>
	Add a new webshare. Either a file share (then upload a file) or a link (then enter a link).
	<form method="post" enctype="multipart/form-data">
		<label>URI: </label><input type="text" name="uri" required><br>
		<label>File: </label><input type="file" name="file"><br>
		<label>Link: </label><input type="text" name="link"><br>
		<label>Expire Date: </label><input type="datetime-local" name="expireDate" min="<?php echo date('Y-m-d\TH:i'); ?>"><br>
		<input type="submit" value="Add share" name="submit"><br>
	</form>
</body>

</html>