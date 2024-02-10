<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | Delete</title>
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

		p {
			color: inherit;
			background-color: inherit;
		}

		a {
			color: inherit;
			background-color: inherit;
			text-decoration: none;
		}

		a:hover {
			text-decoration: underline;
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
	<h1>Webshare Delete</h1>
	<?php if (Webshare\Output::$status == "success") { ?>
		<p>Share successfully deleted.</p>
	<?php } elseif (Webshare\Output::$status == "error") { ?>
		<p>Share deleting failed. Please contact webmaster.</p>
	<?php } else { ?>
		<p>Do you really want to delete the share <i><?= Webshare\Output::$uri ?></i>?</p>
		<form method="post">
			<input type="hidden" name="deleteShare" value="<?= Webshare\Output::$uri ?>">
			<input type="submit" name="submit" value="Delete"><br>
		</form>
	<?php } ?>
	<p><?= Webshare\Output::link("admin", "Admin Page") ?></p>
</body>

</html>