<?php

/**
 * Placeholder Class
 * Contains all functionality to render, cache and get placeholder images.
 * 
 * @author Hans Westman <hanswestman@gmail.com>
 */
class Placeholder {

	var $directories = array();
	var $settings = array(
		'width' => 400,
		'height' => 300,
		'fg' => array('r'=>144, 'g'=>144, 'b'=>144),
		'bg' => array('r'=>192, 'g'=>192, 'b'=>208),
		'type' => 'png',
		'fontsize' => 10,
		'font' => 'arial',
		'text' => '',
	);

	/**
	 * Setting up the variables used in this application.
	 */
	public function __construct(){

		$this->directories['cache'] = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
		$this->directories['fonts'] = __DIR__ . DIRECTORY_SEPARATOR . 'fonts';
		$this->directories['templates'] = __DIR__ . DIRECTORY_SEPARATOR . 'templates';

		$this->extendSettings($_GET);

	}

	/**
	 * Extends the default settings with the new
	 * @param array $new Associative array of new values, as key => value
	 */
	function extendSettings($new){
		if(!empty($new['size']) && preg_match('/^\d+x\d+$/', $new['size'])){
			list($this->settings['width'], $this->settings['height']) = preg_split('/x/', $new['size']);
		}

		$this->settings['text'] = empty($new['text']) ? $this->settings['width'] . 'x' . $this->settings['height'] : $new['text'];

		if(!empty($new['fg']) && preg_match('/^([0-9abcdef]{3}){1,2}$/i', $new['fg'])){
			$color = self::hex2rgb($new['fg']);
			if($color !== false){
				$this->settings['fg'] = array(
					'r' => $color[0],
					'g' => $color[1],
					'b' => $color[2]
				);
			}
		}

		if(!empty($new['bg']) && preg_match('/^([0-9abcdef]{3}){1,2}$/i', $new['bg'])){
			$color = self::hex2rgb($new['bg']);
			if($color !== false){
				$this->settings['bg'] = array(
					'r' => $color[0],
					'g' => $color[1],
					'b' => $color[2]
				);
			}
		}

		if(!empty($new['type'])){
			$this->settings['type'] = strtolower($new['type']);
		}
		
		if(!empty($new['font'])){
			$this->settings['font'] = strtolower($new['font']);
		}

	}

	/**
	 * Helper function to generate the file path for caching.
	 * @return string File system path to file
	 */
	function getFilePath(){
		return $this->directories['cache'] . DIRECTORY_SEPARATOR . substr(($this->settings['width'] . 'x' . $this->settings['height'] . 'fg' . implode('', $this->settings['fg']) . 'bg' .implode('', $this->settings['bg']) . 'f' . $this->settings['font'] . 't' . preg_replace('/[^\w\-]/', '', $this->settings['text'])), 0, 96) . '.' . $this->settings['type'];
	}

	/**
	 * Find all available fonts from the fonts directory in this application.
	 * @return array of font arrays
	 */
	function getAvailableFonts(){

		$fonts = array();
		if($handle = opendir($this->directories['fonts'])){
			while(false !== ($file = readdir($handle))){
				$exceptions = array('.', '..');
				$fileNameParts = explode('.', $file);
				$fileEnding = end($fileNameParts);
				if(!in_array($file, $exceptions) && $fileEnding == 'ttf'){
					$name = preg_replace('/\.ttf$/', '', $file);
					$fonts[strtolower($name)] = array(
						'title' => ucfirst($name),
						'name' => $name,
						'path' => $this->directories['fonts'] . DIRECTORY_SEPARATOR . $file,
					);
				}
			}
		}

		return $fonts;

	}

	/**
	 * Renders a manual if you don't supply any GET arguments with settings.
	 */
	function renderManual(){
		
		extract(array(
			'fonts' => array_keys($this->getAvailableFonts()),
		));
		include_once($this->directories['templates'] . DIRECTORY_SEPARATOR . 'manual.php');

	}

	/**
	 * Sets cache and content type headers and returns the image data
	 */
	function renderImage(){
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s',time()) . ' GMT');
		header('Expires: ' . gmdate('D, d M Y H:i:s',time()+60*60*24*30) . ' GMT');

		if(file_exists($this->getFilePath())){
			switch($this->settings['type']){
				case 'gif':
					header('Content-type: image/gif');
					break;
				case 'jpeg':
					header('Content-type: image/jpeg');
					break;
				default: //PNG
					header('Content-type: image/png');
					break;
			}

			echo(file_get_contents($this->getFilePath()));
			die();
		}
	}

	/**
	 * Renders the image and returns the image data to the user.
	 */
	function generateAndRenderImage(){

		$fonts = $this->getAvailableFonts();
		$font = empty($fonts[$this->settings['font']]) ? $fonts['arial'] : $fonts[$this->settings['font']];

		$image = imagecreatetruecolor($this->settings['width'], $this->settings['height']);
		$bg = imagecolorallocate($image, $this->settings['bg']['r'], $this->settings['bg']['g'], $this->settings['bg']['b']);
		$fg = imagecolorallocate($image, $this->settings['fg']['r'], $this->settings['fg']['g'], $this->settings['fg']['b']);

		imagefilledrectangle($image, 0, 0, $this->settings['width'], $this->settings['height'], $bg);

		$textBoundingBox = imagettfbbox($this->settings['fontsize'], 0, $font['path'], $this->settings['text']);
		$textWidth = $textBoundingBox[2] - $textBoundingBox[0];
		$textHeight = $textBoundingBox[1] - $textBoundingBox[7];


		$foundMaxFontSize = false;
		while(!$foundMaxFontSize){
			$textBoundingBox = imagettfbbox($this->settings['fontsize'], 0, $font['path'], $this->settings['text']);
			if((($textBoundingBox[2] - $textBoundingBox[0]) < $this->settings['width']*0.5) && (($textBoundingBox[1] - $textBoundingBox[7]) < $this->settings['height']*0.4)){
				$this->settings['fontsize']++;
			}
			else{
				$foundMaxFontSize = true;
				$textBoundingBox = imagettfbbox($this->settings['fontsize'], 0, $font['path'], $this->settings['text']);
				$textWidth = $textBoundingBox[2] - $textBoundingBox[0];
				$textHeight = $textBoundingBox[1] - $textBoundingBox[7];
			}
		}

		imagettftext($image, $this->settings['fontsize'], 0, ($this->settings['width']*0.5 - $textWidth*0.5), ($this->settings['height']*0.5 + $textHeight*0.5) , $fg, $font['path'], $this->settings['text']);

		switch($this->settings['type']){
			case 'gif':
				imagegif($image, $this->getFilePath());
				break;
			case 'jpeg':
				imagejpeg($image, $this->getFilePath());
				break;
			default: //PNG
				imagepng($image, $this->getFilePath());
				break;
		}
		imagedestroy($image);
		$this->renderImage();
	}

	/**
	 * Helper function to convert hex into a list of RGB values.
	 * @param string $color Color in hex format
	 * @return mixed Returns array of RGB values if successful, otherwise returns false
	 */
	public function hex2rgb($color){
		if ($color[0] == '#'){
			$color = substr($color, 1);
		}
		if (strlen($color) == 6){
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		}
		elseif (strlen($color) == 3){
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}
		else{
			return false;
		}
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	}
}

?>