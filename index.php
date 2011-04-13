<?php

$fileStream = fopen('HNI_0001.MPO','rb');

$numImgs = 0;
while(!feof($fileStream)) { 

	$data = fread($fileStream, 1);

	if($data == base64_decode('/w==')) {
		$data = fread($fileStream, 1);
		if($data == base64_decode('2A==')) {			
			if($level == 0) { 
				$imgStart[$numImgs] = ftell($fileStream) - 2; 
			} 
			$level++; 
		} 

		if($data == base64_decode('2Q==')) {
			$level--;
			if($level == 0) { 
				$imgEnd[$numImgs] = ftell($fileStream) - 2; 
				$numImgs++; 
			} 
		} 
	}	
}

$imgs = array();
for( $i = 0; $i < $numImgs; $i++ ) {
	fseek( $fileStream, $imgStart[$i] );
	$img = imagecreatefromstring( fread( $fileStream, $imgEnd[$i] - $imgStart[$i] ) );
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
header('Content-Type: image/png');
imagejpeg($fullImg);
