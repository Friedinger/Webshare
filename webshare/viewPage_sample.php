<!DOCTYPE html>
<html>

<head>
	<title>Webshare | <?php print($share["fileName"]) ?></title>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<style>
		html {
			height: 100%;
		}

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

		header {
			order: 1;
			background-color: hsl(0, 0%, 20%);
			/* padding: inherit; */
			margin-bottom: 0.5rem;
		}

		header .fileName {
			display: inline-block;
			padding: 0.5rem;
		}

		header .download {
			float: right;
			color: inherit;
			text-decoration: none;
			padding: 0.5rem;
		}

		header a:hover {
			background-color: hsl(0, 0%, 27%);
		}

		header .download .download-icon {
			box-sizing: border-box;
			position: relative;
			display: inline-block;
			width: 16px;
			height: 6px;
			border: 2px solid;
			border-top: 0;
			bottom: -2px;
		}

		header .download .download-icon::after {
			content: "";
			box-sizing: inherit;
			position: absolute;
			width: 8px;
			height: 8px;
			border-left: 2px solid;
			border-bottom: 2px solid;
			transform: rotate(-45deg);
			left: 2px;
			bottom: 4px;
		}

		header .download .download-icon::before {
			content: "";
			box-sizing: inherit;
			position: absolute;
			width: 2px;
			height: 10px;
			background: currentColor;
			left: 5px;
			bottom: 5px;
		}

		iframe {
			order: 2;
			flex-grow: 1;
			width: 100%;
			height: 100%;
			border: none;
		}
	</style>
</head>

<body>
	<header>
		<div class="fileName"><?php print($share["fileName"]) ?></div>
		<a class="download" href="<?php print($share["uri"]) ?>?action=download">
			<div class="download-icon"></div>
			Download
		</a>
	</header>
	<iframe src="<?php print($share["uri"]) ?>?action=show"></iframe>
</body>

</html>