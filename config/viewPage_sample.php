<!DOCTYPE html>
<html lang="en">

<head>
	<title>Webshare | <share-value /></title>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<link rel="stylesheet" href="/style.css">
	<script>
		function copyLink() {
			navigator.clipboard.writeText(window.location.href);
			document.getElementById("copyLink").innerHTML = "Link copied";
			setTimeout(function() {
				document.getElementById("copyLink").innerHTML = "Copy Link";
			}, 1000)
		}
	</script>
</head>

<body class="view">
	<header>
		<div class="fileName">
			<share-value />
		</div>
		<a href="?action=download">
			<div class="download-icon"></div>
			Download
		</a>
		<a href="javascript:void(0);" onclick="copyLink();">
			<div class="copy-icon"></div>
			<span id="copyLink">Copy Link</span>
		</a>
	</header>
	<main>
		<share-preview />
	</main>
</body>

</html>