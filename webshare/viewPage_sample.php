<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | <?php print($shareFileName) ?></title>
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
			height: 100%;
			padding: 0.5rem;
			margin: 0;
			color-scheme: dark;
			box-sizing: border-box;
		}

		header {
			order: 1;
			background-color: hsl(0, 0%, 20%);
			margin-bottom: 0.5rem;
		}

		header .fileName {
			display: inline-block;
			padding: 0.5rem;
		}

		header a {
			float: right;
			color: inherit;
			background-color: inherit;
			text-decoration: none;
			padding: 0.5rem;
		}

		header a:hover {
			background-color: hsl(0, 0%, 27%);
		}

		header .copy-icon {
			box-sizing: border-box;
			position: relative;
			display: inline-block;
			width: 16px;
			height: 16px;
			background-color: inherit;
		}

		header .copy-icon::before {

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

		header .copy-icon::after {
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

		header .download-icon {
			box-sizing: border-box;
			position: relative;
			display: inline-block;
			width: 16px;
			height: 6px;
			border: 2px solid;
			border-top: 0;
			bottom: -2px;
		}

		header .download-icon::after {
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

		header .download-icon::before {
			content: "";
			box-sizing: inherit;
			position: absolute;
			width: 2px;
			height: 10px;
			background: currentColor;
			left: 5px;
			bottom: 5px;
		}

		main {
			order: 2;
			flex-grow: 1;
			max-height: calc(100% - 2.75rem);

		}

		main * {
			width: 100%;
			height: 100%;
			object-fit: contain;
			border: none;
		}

		@media only screen and (max-width: 600px) {

			header a,
			header .fileName {
				display: inline;
				float: left;
				;
			}
		}
	</style>
	<script>
		function copyLink() {
			navigator.clipboard.writeText("<?php print("https://" . $shareLink) ?>");
			document.getElementById("copyLink").innerHTML = "Link copied";
			setTimeout(function() {
				document.getElementById("copyLink").innerHTML = "Copy Link";
			}, 1000)
		}
	</script>
</head>

<body>
	<header>
		<div class="fileName"><?php print($shareFileName) ?></div>
		<a href="?action=download">
			<div class="download-icon"></div>
			Download
		</a>
		<a href="javascript:void(0);" onclick="copyLink();">
			<div class="copy-icon"></div>
			<span id="copyLink">Copy Link</span>
		</a>
	</header>
	<main><?php print($sharePreview) ?></main>
</body>

</html>