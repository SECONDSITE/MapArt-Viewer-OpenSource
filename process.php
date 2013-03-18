<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//The "@" supresses error message, but if you have aggressive error reporting, you'll
// get an error here if "infile.txt" doesn't exist.
@unlink("infile.txt");

/*$fileLink = fopen("infile.txt", "w");
fwrite($fileLink, "GET: \n");
foreach($_GET as $key=>$value)
  fwrite($fileLink, "$key => $value \n");

fwrite($fileLink, "POST: \n");
foreach($_POST as $key=>$value)
  fwrite($fileLink, "$key => $value \n");
fclose($fileLink);*/


$i=0;
$pixels = Array();
$lines = Array();
$maxLat[$i] = Array();
$maxLon[$i] = Array();
$fullFileName[$i] = Array();
$firstFileName[$i] = Array();
//Make sure the output file is created...
$fileLink = fopen("infile.txt", "w");
fwrite($fileLink, "");
fclose($fileLink);

//Iterate through input images.
while(isset($_GET["img$i"]))
{
	$fileLink = fopen("infile.txt", "a");
	$fullFileName[$i] = "uploading/".$_GET["img$i"];
	$dot = strrpos($fullFileName[$i], ".");
	$firstFileName[$i] = substr($fullFileName[$i], 0, $dot);

	fwrite($fileLink, "<h2>Image ".($i+1)."</h2>");
	fwrite($fileLink, "<b><u>Step 1/2: Getting Info</u></b>\n");

	$return = "";
	//Dump everything from "gdalinfo" into a new array.
	$output = array();
	$errno = exec("gdalinfo " . $fullFileName[$i], $output, $return);

	if($errno != 0)
	{
		echo "Execution failed with error $errno";
		exit();
	}
	
	foreach($output as $key=>$value)
	{
		if(preg_match("/^Lower Right \(\s*(\d+\.\d+),\s*(\d+\.\d+)\)$/", $value, $matches)==1)
		{
		   $pixels[$i] = floatval($matches[1]);
		   $lines[$i] = floatval($matches[2]);
		   $lines_adjusted = $lines[$i];
		   //$lines_adjusted /= 1.20048772;
		}
	}
	
	//When we tile an image, it's placed in a bounding box whose sides are
	//  the power of 2 >= larger side of the image.
	//SO, an image 240x230 would be in a 256x256 bounding box.
	//... an image 256x230 would be in a 256x256 bounding box.
	//... an image 256x257 would be in a 512x512 bounding box.
	//Now, let's find the size of our bounding box, and the ratio of the 
	//  bounding box size to the larger side of the image (boundingBoxRatio)
	$largerSize = max($pixels[$i], $lines_adjusted);
	$boundingSize = 1;
	while($largerSize > $boundingSize)
	{
		$boundingSize *= 2;
	}
	$boundingBoxRatio = $largerSize / $boundingSize;
				
	if($pixels[$i]>$lines_adjusted)
	{
		//Geographic:
		//$maxLat[$i] = 34 + $lines_adjusted/$pixels[$i];
		//$maxLon[$i] = -85.0;
		//Raster:
		$maxLat[$i] = 4096 * $boundingBoxRatio * $lines_adjusted/$pixels[$i];
		$maxLon[$i] = 4096 * $boundingBoxRatio;
	}
	
	else
	{
		//Geographic:
		//$maxLat[$i] = 35.0;
		//$maxLon[$i] = -86.0 + $pixels[$i]/$lines_adjusted;
		//Raster:
		$maxLat[$i] = 4096 * $boundingBoxRatio;
		$maxLon[$i] = 4096 * $boundingBoxRatio * $pixels[$i]/$lines_adjusted;
	}

	/*
	GEOGRAPHIC GDAL EXAMPLE:
	With pixels = 19193, lines = 15475:
	     max = 19193
		 ratio = min/max = 15475/19193 = 0.8062835408742771
		 
	gdal_translate -of GTiff -a_srs EPSG:32616 -gcp 0 15475 -86 34 0 -gcp 0 0 -86 34.80628354 0 -gcp 19193 0 -85 34.80628354 0 -gcp 19193 15475 -85 34 0 test.tif test-geo.tif
	gdalwarp -of GTiff -t_srs EPSG:32616 -ts 19193 15475 test-geo.tif test-geo-warped.tif
	gdalbuildvrt test-geo-warped-vrt.vrt test-geo-warped.tif
	time gdal2tiles.py -p 'geodetic' -k -s EPSG:32616 -z 3-5 -v test-geo-warped-vrt.vrt
	
	GENERALIZED:
	"gdal_translate -of GTiff -a_srs EPSG:32616 -gcp 0 $lines -86 34 0 -gcp 0 0 -86 $maxLat 0 -gcp $pixels 0 $maxLon $maxLat 0 -gcp $pixels $lines $maxLon 34 0 $fullFileName ".$firstFileName."-geo.tif";
	"gdalwarp -of GTiff -t_srs EPSG:32616 -ts $pixels $lines ".$firstFileName."-geo.tif ".$firstFileName."-geo-warped.tif";
	"gdalbuildvrt ".$firstFileName."-geo-warped-vrt.vrt ".$firstFileName."-geo-warped.tif";
	"time gdal2tiles.py -p 'geodetic' -k -s EPSG:32616 -z 3-5 ".$firstFileName."-geo-warped-vrt.vrt";
	 --> this has been checked, good to go.
	
	...BUT we're using raster. It's much simpler and more accurate for documents.
	*/
	//$exec4 = "gdal2tiles.py -p 'raster' -k -s EPSG:4326 -z 0-5 ".$fullFileName[$i]." >> infile.txt";		
	$exec4 = "gdal2tiles.py -p 'raster' -k -z 0-5 ".$fullFileName[$i]." >> infile.txt";		

	$fileLink = fopen("infile.txt", "a");
	fwrite($fileLink, "<b><u>Step 2/2: Tiling</u></b>\n");
	fclose($fileLink);
	$errNo = exec($exec4, $output2, $return2);

	$i++;
}
$i--;

$fileLink = fopen("infile.txt", "a");
fwrite($fileLink, "<b><u><font color=\"red\">Finished.</font> Complete the form above to continue.</u></b>\n");
fclose($fileLink);  

echo "|maxLon=".$maxLon[$i]."|maxLat=".$maxLat[$i]."|"; //this is regexp'd in initiate.php
exit();
?>
