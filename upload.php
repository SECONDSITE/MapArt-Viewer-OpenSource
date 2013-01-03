<html>
<head>

<title>Upload your Images</title>

<style type="text/css">
body
{
margin:0px;
padding-left:16px; padding-right:12px;
font-family:'Lucida Grande',Geneva,Arial,Verdana,sans-serif;
}

div
{
border-style:solid;
border-color:#888;
border-width:1px;
padding:25px;
}

i
{
color:red;
display:block;
margin-top:25px;
text-align:center;
}

h1
{
height:43px;
margin-top:12px;
text-align:center;
margin-bottom:12px;
background-color:#eee;
border-style:solid;
border-color:#888;
border-width:1px;
}

p
{
margin-top:0px;
text-indent:25px;
margin-bottom:0px;
text-align:justify;
}

a
{
color:#A52A2A;
font-weight:bold;
text-decoration:none;
}

form {margin:0px;}
input[type="file"]
{display:block;}
</style>

<script type="text/javascript">
var count = 0;

//The users can designate up to ten
//different images with each session
//by adding another browsing form.

function addUploadbit()
{
	//Limit 10 images.
	if(++count < 10)
	{
		//Create a new browsing form and append
		//  it to the end of all the other browsing forms.
		var insertArea = document.getElementById("inserting");
		var newBit = document.createElement("input");
		newBit.setAttribute("name","IMG" + count);
		newBit.setAttribute("type","file");
		insertArea.appendChild(newBit);
	}
	//Limit 10 images.
	if(count == 9) {document.getElementById("warnings").innerHTML = "10 images max";}
}
</script>
</head>
<body>
<?php

//If there's no POST data, we're hitting this page for the first time. Echo the upload form.
if(count($_POST) == 0)
{
	echo "
		<h1>..Upload up to 10 Images with Identical Extents..</h1>
		
		<div style='width:325px; float:left;'>
			<form action='upload.php' method='post' enctype='multipart/form-data'>
				<span id='inserting'><input type='file' name='IMG0' id='firstfile'/></span>
				<input type='button' value='Add another Image' onclick='addUploadbit()'/>
				<input type='submit' name='submit' value='Submit'/>
			</form><span style='color:red;' id='warnings'></span>
		</div>";
	
	//Also echo out some temporary placeholder text, as well as a
	//javascript warning if you're using anything but firefox.
	echo "<div style='position:absolute; margin-left:425px; margin-right:12px;'>";
			
	$max_upload = (int)(ini_get('upload_max_filesize'));
		
	echo "The largest file you can upload at this time is: $max_upload MB. <br>";
	echo "(You can change this in your PHP/server settings)<br>";
	echo "Current supported file formats: PNG, JPG, TIF, BMP";

	/* The following is a list of theoretically supported file formats, derived from http://www.gdal.org/formats_list.html
	These can all be georeferenced with GDAL utilities, so theoretically we can pass them along. The only thing stopping
	us is checking for all of these file formats on upload.
	Most common unsupported file format: GIF (can't be georeferenced)

	 Arc/Info ASCII Grid (AAIGrid) (2GB limit)
	 ACE2 (ACE2) (no size limit)
	 ADRG/ARC Digitilized Raster Graphics (.gen/.thf) (ADRG) (no size limit)
	 Arc/Info Binary Grid (.adf) (AIG) (no size limit)
	 Magellan BLX Topo (.blx, .xlb) (BLX) (no size limit)
	 Microsoft Windows Device Independent Bitmap (.bmp) (BMP) (4GiB limit)
	 BSB Nautical Chart Format (.kap) (BSB) (no size limit)
	 VTP Binary Terrain Format (.bt) (BT) (no size limit)
	 Convair PolGASP data (CPG) (no size limit)
	 USGS LULC Composite Theme Grid (CTG) (no size limit)
	 Spot DIMAP (metadata.dim) (DIMAP) (no size limit)
	 ELAS DIPEx (DIPEx) (no size limit)
	 First Generation USGS DOQ (.doq) (DOQ1) (no size limit)
	 New Labelled USGS DOQ (.doq) (DOQ2) (no size limit)
	 Military Elevation Data (.dt0, .dt1, .dt2) (DTED) (no size limit)
	 Arc/Info Export E00 GRID (E00GRID) (no size limit)
	 ECRG Table Of Contents (TOC.xml) (ECRGTOC) (no size limit)
	 ESRI .hdr Labelled (EHdr) (No limits)
	 Erdas Imagine Raw (EIR) (no size limit)
	 NASA ELAS (ELAS) (no size limit)
	 ENVI .hdr Labelled Raster (ENVI) (No limits)
	 ERMapper (.ers) (ERS)
	 EOSAT FAST Format (FAST) (no size limit)
	 WMO GRIB1/GRIB2 (.grb) (GRIB) (2GB limit)
	 GRASS ASCII Grid (GRASSASCIIGrid) (no size limit)
	 TIFF / BigTIFF / GeoTIFF (.tif) (GTiff) (4GiB for classical TIFF / No limits for BigTIFF)
	 NOAA .gtx vertical datum shift (GTX)
	 GXF - Grid eXchange File (GXF) (4GiB limit)
	 HF2/HFZ heightfield raster (HF2) (no size limit)
	 Erdas Imagine (.img) (HFA) (No limits)
	 Image Display and Analysis (WinDisp) (IDA) (2GB limit)
	 ILWIS Raster Map (.mpr,.mpl) (ILWIS) (no size limit)
	 Intergraph Raster (INGR) (2GiB limit)
	 USGS Astrogeology ISIS cube (Version 2) (ISIS2) (no size limit)
	 USGS Astrogeology ISIS cube (Version 3) (ISIS3) (no size limit)
	 Japanese DEM (.mem) (JDEM) (no size limit)
	 JPEG JFIF (.jpg) (JPEG) (4GiB) (max dimentions 65500x65500)
	 KMLSUPEROVERLAY (KMLSUPEROVERLAY)
	 NOAA Polar Orbiter Level 1b Data Set (AVHRR) (L1B) (no size limit)
	 Erdas 7.x .LAN and .GIS (LAN) (2GB)
	 FARSITE v.4 LCP Format (LCP) (No limits)
	 Daylon Leveller Heightfield (Leveller) (2GB)
	 NADCON .los/.las Datum Grid Shift (LOSLAS)
	 In Memory Raster (MEM)
	 Vexcel MFF (MFF) (No limits)
	 Vexcel MFF2 (MFF2 (HKV) (No limits)
	 EUMETSAT Archive native (.nat) (MSGN (
	 NLAPS Data Format (NDF) (No limits)
	 NOAA NGS Geoid Height Grids (NGSGEOID)
	 NITF (.ntf, .nsf, .gn?, .hr?, .ja?, .jg?, .jn?, .lf?, .on?, .tl?, .tp?, etc.) (NITF) (10GB)
	 NTv2 Datum Grid Shift (NTv2)
	 Northwood/VerticalMapper Classified Grid Format .grc/.tab (NWT_GRC) (no size limit)
	 Northwood/VerticalMapper Numeric Grid Format .grd/.tab (NWT_GRD) (no size limit)
	 PCI Geomatics Database File (PCIDSK) (No limits)
	 PCRaster (PCRaster)
	 NASA Planetary Data System (PDS) (no size limit)
	 Portable Network Graphics (.png) (PNG)
	 Swedish Grid RIK (.rik) (RIK) (4GB)
	 Raster Matrix Format (*.rsw, .mtw) (RMF) (4GB)
	 Raster Product Format/RPF (CADRG, CIB) (RPFTOC) (no size limit)
	 RadarSat2 XML (product.xml) (RS2 (4GB)
	 Idrisi Raster (RST) (No limits)
	 SAGA GIS Binary format (SAGA) (no size limit)
	 SAR CEOS (SAR_CEOS) (no size limit)
	 USGS SDTS DEM (*CATD.DDF) (SDTS) (no size limit)
	 SGI Image Format (SGI) (no size limit)
	 Snow Data Assimilation System (SNODAS) (no size limit)
	 Standard Raster Product (ASRP/USRP) (SRP) (2GB)
	 SRTM HGT Format (SRTMHGT) (no size limit)
	 Terragen Heightfield (.ter (TERRAGEN) (no size limit)
	 USGS ASCII DEM / CDED (.dem) (USGSDEM) (no size limit)
	 GDAL Virtual (.vrt) (VRT) (no size limit)
	 ASCII Gridded XYZ (XYZ) (no size limit)
	 ZMap Plus Grid (ZMap)
*/

			echo "</p>
			<i id='caution'></i><script>if(!(/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent))) {document.getElementById('caution').innerHTML = 'MORE TESTING NEEDED: but we suggest using <a href=http://www.mozilla.org>Firefox</a>.';}</script>
				
				<i id='caution'></i><script>if((/Chrome[\/\s](\d+\.\d+)/.test(navigator.userAgent))) {document.getElementById('caution').innerHTML = 'This application currently does not work with Chrome. We suggest <a href=http://www.mozilla.org>Firefox</a>.';}</script>
	";

	//GET parameters with no POST parameters means an error has occurred.
	if(count($_GET) <> 0)
	{
		switch($_GET['e'])
		{
		case 801: echo "<i>Unsupported filetype (please be sure to check that your files are either JPEG, PNG, GIF or BMP.)</i>"; break;
		//This case should no longer be trigerred... repeated filenames are handled on upload.
		case 802: echo "<i>SECONDHAND FILENAMES (try to rename the conflicting files so we can avoid overriding any other files.)</i>"; break;
		case 803: echo "<i>INVALID DIMENSIONS (all images must be cropped to the same dimensions)</i>"; break;
		case 804: echo "<i>EXCESSIVE FILESIZE (try uploading smaller files)</i>"; break;
		case 805: echo "<i>DESIGNATED ERROR (unknown error. Try broadening permissions on all of this program's directories.)</i>"; break;
		}
	}
	
	echo "</div>";
}
else
{
	//Initiate some variables.
	$num = -1; 
	$dimension = 0;
	$push = "initiate.php?";
	
	//Loop through the attached images...
	while(true)
	{

		$imgnum = "IMG" . (++$num);

		if(!array_key_exists($imgnum,$_FILES)) {break;}

		//Ensure that the filetype matches one
		//of the supported filetypes for GDALing.
		if($_FILES[$imgnum]["type"] <> "image/png"
		&& $_FILES[$imgnum]["type"] <> "image/bmp"
		&& $_FILES[$imgnum]["type"] <> "image/jpeg"
		&& $_FILES[$imgnum]["type"] <> "image/pjpeg"
		&& $_FILES[$imgnum]["type"] <> "image/tiff"
		&& $_FILES[$imgnum]["type"] <> "image/x-tiff"
		)
		{header("location: upload.php?e=801"); return;}
		
		/*If this is a duplicate filename, make a new filename.
		while(file_exists("uploading/" . $_FILES[$imgnum]["name"]))
		{
			
		}*/
		//if($_FILES[$imgnum]["size"] > 25000000) {header("location: upload.php?e=804"); return;}
		if($_FILES[$imgnum]["error"] > 0) {header("location: upload.php?e=805"); return;}
	}
	
	//If the images have all been cleared without throwing any
	//errors, individually iterate through them all to carefully
	//upload them up to the servers.
	for($iee = 0; $iee < $num; $iee++)
	{
		$imgnum = "IMG" . $iee;
		//First, replace spaces with underscores:
		$newFileName = str_replace("\\\ ", "_", $_FILES[$imgnum]["name"]);
		$newFileName = str_replace("\ ", "_", $newFileName);
		$newFileName = str_replace(" ", "_", $newFileName);
		//Separate filename from extension:
		$ext = substr($newFileName, strrpos($newFileName, "."));
		$file = substr($newFileName, 0, strrpos($newFileName, "."));
		//If there's already a file with this name, append a random 
		while(file_exists("uploading/" . $newFileName))
		{
		    $ext = substr($newFileName, strrpos($newFileName, "."));
		    $file = substr($newFileName, 0, strrpos($newFileName, "."));			
		    $newFileName = $file.rand(0,9).$ext;
		}
		//Move the uploaded file:
		$result = move_uploaded_file($_FILES[$imgnum]["tmp_name"],"uploading/" . $newFileName);
		//If that failed:
		if(!$result)
		{
			echo "Upload failed.";
			exit();
		}
		$size = getimagesize("uploading/" . $_FILES[$imgnum]["name"]); 
		$size = $size[0] / $size[1];
		$push .= "img" . $iee . "=$newFileName&";
	}
	//We want to tack on input GET variables, so make them into a string:
	$getString = "";
	foreach($_GET as $key=>$value)
	{
		$getString.="$key=$value&";
	}
	$push .= $getString;
	//Cut off the tailing "&"
	$push = substr($push, 0, $push.length-1);

	//We then pass a listing of all the newly uploaded files to "initiate.php" (and hence "process.php")
	header("Location: $push");
}

?>
</body>
</html>
