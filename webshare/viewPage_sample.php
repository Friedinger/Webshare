<!DOCTYPE html>
<html lang="en">

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

		iframe {
			order: 2;
			flex-grow: 1;
			width: 100%;
			height: 100%;
			border: none;
		}
	</style>
	<script>
		function copyLink() {
			navigator.clipboard.writeText("<?php print("https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/" . $share["uri"]) ?>");
			document.getElementById("copyLink").innerHTML = "Link copied";
		}

		function resetCopyLink() {
			setTimeout(function() {
				document.getElementById("copyLink").innerHTML = "Copy Link";
			}, 1000)
		}
	</script>
</head>

<body>
	<header>
		<div class="fileName"><?php print($share["fileName"]) ?></div>
		<a href="<?php print($share["uri"]) ?>?action=download">
			<div class="download-icon"></div>
			Download
		</a>
		<a href="javascript:void(0);" onclick="copyLink();" onmouseout="resetCopyLink();">
			<div class="copy-icon"></div>
			<span id="copyLink">Copy Link</span>
		</a>
	</header>
	<iframe src="<?php print($iframeSrc) ?>?action=show" title="<?php print($iframeTitle) ?>"></iframe>
</body>

</html>