<html>
<head>

<title>Processing your Images</title>

<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);

//Alright, I want to delete "infile.txt" and then make a new one.
@unlink("infile.txt");
$fileLink = fopen("infile.txt", "w");
fwrite($fileLink, "...");
fclose($fileLink);

?>


<style type="text/css">
body
{
margin:0px;
padding:12px;
//cursor:wait;
padding-left:16px;
font-family:'Lucida Grande',Geneva,Arial,Verdana,sans-serif;
}

h1
{
height:43px;
text-align:center;
margin-bottom:12px;
background-color:#eee;
border-style:solid;
border-color:#888;
border-width:1px;
}

div
{
width:99%;
padding:4px;
color:#000000;
border-style:solid;
border-width:4.25px;
border-color:#000000;
background-color:#F0F0F0;
}

form
{
left:46%;
margin-top:13px;
margin-left:-110;
position:relative;
}
input {background-color:#eee;}
select {background-color:#eee;}
</style>

<script type="text/javascript">

var gdalExecIsDone = false;
var streamInfileIsDone = false;
var processReturn = "";

function initiateEverything()
{
	document.getElementById("submit0").disabled = true;
	executeGDAL();
	streamInfile();
}

function executeGDAL()
{
	//This segment of code will
	//be revised with some PHP so as
	//to iterate through for each image
	//that is designated in the $_GET[]
	//paramaters of the page to the PHP.
	var xmlhttp = new XMLHttpRequest();
	
	xmlhttp.onreadystatechange=function()
	{
		//As is standard procedure for all this AJAX, check
		//if the process initiated above has finished just yet.
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
		{
			gdalExecIsDone = true;
			//All of the below coding should eventually
			//be replaced with some programming that parses
			//the strings passed in that can better recognize
			//the actually progress of the process.
			
			console.log("Response: "+xmlhttp.responseText);	
			processReturn = xmlhttp.responseText;
		}
	}

	//We're passing on all of our GET variables, so make them into a string:
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	        vars[key] = value;
	});
	var getString = "";
	var index;
	for(index in vars) {
	  getString += index + "=" + vars[index] + "&";
	}
	getString = getString.substr(0, getString.length-1);
	
	
	//Open and send the request
	xmlhttp.open("GET","process.php?"+getString,true);
	xmlhttp.send();
	console.log("Request sent");
	//It may be interesting to actually
	//initiate *MULTIPLE* requests through
	//AJAX, one for each of the images, as
	//the process itself is stupidly multi-
	//threadable, just as long as they don't
	//share any filenames. Google into it!
}

function streamInfile()
{
	//This script is initiated once
	//the page is entirely loaded, where
	//upon it then loops psuedo indefinitely
	//as it continually reads from the dumpfile.
	var xmlhttp;
	var progress;
//console.log("Stream...");
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function()
	{
		//As is standard procedure for all this AJAX, check
		//if the process initiated above has finished just yet.
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
		{
			console.log("Proper response in streamInFile");
			document.getElementById("loadingtext").innerHTML = xmlhttp.responseText.replace(/\n/g, '<br />');
			
			if(gdalExecIsDone==false)
			{
				var patt=/^\d+\.?\d*$/;
				if(document.getElementById("width").value != "Width"
				&& document.getElementById("height").value != "Height"
				&& patt.test(document.getElementById("width").value)
				&& patt.test(document.getElementById("height").value))
				{
					document.getElementById("submit0").disabled = true;
					document.getElementById("submit0").value = "Processing...";
				}
				streamInfile();
				return;
			}
			else //GDAL exec is complete... but if our form hasn't been filled out, keep calling this function to check.
			//Once the form is filled out, we can enable the "submit" button.
			{
				var patt=/^\d+\.?\d*$/;
				if(document.getElementById("width").value != "Width"
				&& document.getElementById("height").value != "Height"
				&& patt.test(document.getElementById("width").value)
				&& patt.test(document.getElementById("height").value))
				{

					document.getElementById("submit0").disabled = false;
					document.getElementById("submit0").value = "Continue";
				}
				else
				{
					streamInfile();
					return;
				}
			}
			//If the progress variable is ever larger than one hundred, then the page has
			//to redirect over to our openlayers page. It may be best to also wait for
			//the user to specify the names and units of the images before entirely
			//redirecting the browser, but I'll leave that up to you. -- McPherson
		}
	}
	//Alright, this is goofy since we're reading from a text file, but we need a unique request to prevent caching,
	// so we tack the current time onto the request.
	var t = new Date().getTime();
	xmlhttp.open("GET","infile.txt?t="+t,true);
	//streamInfileIsDone = false;
	xmlhttp.send();
	console.log("Started streamInFile");
}


function formSubmit()
//Send an AJAX request to write records to database... then submit form.
{
	
	//We'll need some existing GET vars... pull them into "vars"
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	        vars[key] = value;
	});
	
		var xmlhttp;
		var progress;
		
		
				//Now build a string of parameters to pass with this request.
				var getString = "";
				var i=0;
				while(document.getElementById('layername'+i))
				{
					getString += "img"+i+"_name=" + encodeURIComponent(document.getElementById("layername"+i).value) + "&";

					getString += "img"+i+"_directory=" + encodeURIComponent(vars["img"+i]) + "&";
					i++;
				}

				//Alright, so I have no idea why this isn't working. Like, not a damn clue. "test" returns true, but "exec" returns null.
				//(keep in mind that running "test" and THEN "exec" may fuck things up-- running "test" would set lastIndex>0, then "exec" wouldn't work.
				//  but even without running test, exec still doesn't work. WHAT THE FUCK.)
				/*
				var regExpMaxLon = /|maxLon=-?\d+\.?\d*|/m;
				var regExpMaxLat = /|maxLat=-?\d+\.?\d*|/m;
				var regExpMaxLon = /maxLon/m;
				var regExpMaxLat = /maxLat/m;

				var maxLon = regExpMaxLon.exec(processReturn);
				maxLon = maxLon.substr(8); //8 is length of |maxLon=

				var maxLat = regExpMaxLat.exec(processReturn);
				maxLat = maxLat.substr(8); //8 is length of |maxLat=

				var regExpDirectory = /|directory=[^|]*|/;
				var directory = regExpDirectory.exec(processReturn);
				directory = directory.substr(11); //11 is the length of |directory=

				var regExpTracking = /|trackingXML=[^|]*|/;
				var tracking = regExpTracking.exec(processReturn);
				tracking = tracking.substr(13); //13 is the length of |trackingXML=
				*/

				//Alright, we'll do this with substr, but I don't like it one bit.
				processReturn = processReturn.substr(processReturn.indexOf('|maxLon=')+8);
				var maxLon = processReturn.substr(0, processReturn.indexOf('|'));
				processReturn = processReturn.substr(processReturn.indexOf('|maxLat=')+8);
				var maxLat = processReturn.substr(0, processReturn.indexOf('|'));
				getString += "img_extentNLat=" + maxLat + "&";
				getString += "img_extentSLat=0&"; 
				getString += "img_extentWLon=-0&"; 
				getString += "img_extentELon=" + maxLon + "&";
				getString += "img_height=" + encodeURIComponent(document.getElementById("height").value) + "&";
				getString += "img_width=" + encodeURIComponent(document.getElementById("width").value) + "&";
				getString += "img_units=" + encodeURIComponent(document.getElementById("units").value)+ "&";
		
		xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange=function()
		{
			//As is standard procedure for all this AJAX, check
			//if the process initiated above has finished just yet.
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
			{
				console.log("writemapdetails response: "+xmlhttp.responseText);
    				//document.getElementById("form0").action = xmlhttp.responseText;				
    				//document.getElementById("form0").submit();
				window.location = xmlhttp.responseText;
			}
		}

		
		xmlhttp.open("GET","writeMapDetails.php?"+getString,true);
		//streamInfileIsDone = false;
		xmlhttp.send();
}
</script>

</head>
<body onload="initiateEverything();">

<h1>Processing your Images</h1>

<center>Please enter the following information while you wait:</center>
<form id="form0" action="" method="GET">
	
<?php
$j=0;
while(isset($_GET["img$j"]))
{
   echo 'Layer Name: <input type="text" name="layername'.$j.'" id="layername'.$j.'" value="'.$_GET["img$j"].'" style="width:220px;"><br/>';
   $j++;
}
?>

<input type="text" name="width" id="width" value="Width" style="width:110px; margin-left:0px;">
<input type="text" name="height" id="height" value="Height" style="width:106px;"><br/>
<select name="units" id="units" style="width:220px;">
<option value="mm">Millimeters (mm)</option>
<option value="in">Inches (in)</option>
<option value="ft">Feet (ft)</option>
</select>
<input type="hidden" name="map_id" id="map_id" value="ERROR">

<?php
//Now, add all existing GET params to this form...
foreach($_GET as $key=>$value)
{
	echo "<input type=\"hidden\" name=\"$key\" id=\"$key\" value=\"$value\">";
}
?>
<br>
<input type="button" name="submit0" id="submit0" onclick="formSubmit()" value="Please fill in the above form"/>

<script type="text/javascript">

document.getElementById("submit").disabled = true;

</script>

</form>


<center><div id="loadingbit"/>
	<span id="loadingtext">
	..loading your image..
	</span>
</div></center>	



</body>
</html>
