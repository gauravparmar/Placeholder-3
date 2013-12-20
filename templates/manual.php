<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
			<title>Placeholder Manual</title>
		</head>
		<body>
			<h1>Placeholder - Manual</h1>
			<p>
				<strong>GET-parameters</strong><br />
				<dl>
					<dt>size</dt>
					<dd>Size of the image in WIDTHxHEIGHT, default is 400x300.</dd>
					<dt>bg</dt>
					<dd>Background color, should be in hex without the "#", either as three or six numbers.</dd>
					<dt>fg</dt>
					<dd>Text color, should be in hex without the "#", either as three or six numbers.</dd>
					<dt>text</dt>
					<dd>A string to display on the image, default is the size of the image.</dd>
					<dt>type</dt>
					<dd>File type of the image, you can choose from jpg, gif or png. Png is default.</dd>
					<dt>font</dt>
					<dd>Which font to use. You can choose between <?php echo(implode(', ', $fonts)); ?>.</dd>
				</dl>
			</p>
		</body>
	</html>