<?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Placeholder.class.php');
	$placeholder = new Placeholder();

	if(empty($_GET)){
		$placeholder->renderManual();
	}
	else {
		if(file_exists($placeholder->getFilePath())){
			$placeholder->renderImage();
		}
		else {
			$placeholder->generateAndRenderImage();
		}
	}
?>