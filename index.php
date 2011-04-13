<?php

/** JPEG Multi-Picture Format to Stereoscopic JPEG **
*
* @link http://donatstudios.com/
* @license http://opensource.org/licenses/mit-license.php
* @author Jesse G. Donat
*
**/

$handle = fopen('HNI_0001.MPO','rb');

define('MPO_JPG_MKR', base64_decode('/w=='));
define('MPO_JPG_SOI', base64_decode('2A=='));
define('MPO_JPG_EOI', base64_decode('2Q=='));

$imgind = 0;
while(!feof($handle)) { 

	$data = fread($handle, 1);

	if($data == MPO_JPG_MKR) {
		$data = fread($handle, 1);
		if($data == MPO_JPG_SOI) {			
			if($depth++ == 0) { 
				$imgStart[$imgind] = ftell($handle) - 2; 
			}
		} 

		if($data == MPO_JPG_EOI) {
			if(--$depth == 0) { 
				$imgEnd[$imgind++] = ftell($handle);
			} 
		} 
	}	
}

$imgs = array();
for( $i = 0; $i < $imgind; $i++ ) {
	fseek( $handle, $imgStart[$i] );
	$img = imagecreatefromstring( fread( $handle, $imgEnd[$i] - $imgStart[$i] ) );
	$imgs[$i] = array( 'img' => $img, 'x' => imagesx($img), 'y' => imagesy($img) );
	$fullX += $imgs[$i]['x'];
	$fullY  = max( $fullHeight, $imgs[$i]['y'] );
}

$fullImg = imagecreatetruecolor($fullX, $fullY);

$offset_x = 0;
foreach( $imgs as $img ) {
	imagecopy($fullImg, $img['img'], $offset_x, 0, 0, 0, $img['x'], $img['y']);
	$offset_x += $img['x'];
}

header('Content-Type: image/jpeg');
imagejpeg($fullImg);
