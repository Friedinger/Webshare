<!DOCTYPE html>
<html lang="en">

<head>
	<title>Password | <?php print($uri) ?></title>
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

		form input[type="submit"] {
			margin-top: 0.5rem;
			border: none;
			background-color: hsl(0, 0%, 20%);
			max-width: 33.25rem;
			width: 100%;
		}

		form input[type="submit"]:hover {
			background-color: hsl(0, 0%, 27%);
			cursor: pointer;
		}
	</style>
</head>

<body>
	<h2>The share <i><?php print($uri) ?></i> is password protected</h2>
	<form method="post">
		<label>Password: </label><input type="password" name="password"><br>
		<input type="submit" value="Submit password" name="submit"><br>
	</form>
	<?php if ($status == "incorrect") { ?>
		<p>The entered password is incorrect, please try again.</p>
	<?php } else { ?>
		<p>Please enter the password to access the share.</p>
	<?php } ?>
</body>

</html>