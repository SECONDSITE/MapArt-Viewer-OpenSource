<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);

$i=0;
while(isset($_GET['img'.$i.'_name']))
{
	$img_name[$i] = $_GET['img'.$i.'_name'];
	$img_directory[$i] = $_GET['img'.$i.'_directory'];
	$img_directory[$i] = substr($img_directory[$i], 0, strrpos($img_directory[$i], "."));
	$i++;
}
$numLayers = $i;
$extentNLat = $_GET['img_extentNLat'];
$extentSLat = $_GET['img_extentSLat'];
$extentWLon = $_GET['img_extentWLon'];
$extentELon = $_GET['img_extentELon'];
$height = urldecode($_GET['img_height']);
$width = urldecode($_GET['img_width']);
$units = urldecode($_GET['img_units']);
$stamp = time();

$out = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\"
		  <head>
		    <title>MapArt Viewer OpenSource</title>
		    <meta http-equiv='imagetoolbar' content='no'/>
			<style type=\"text/css\">
				BODY, HTML { padding: 0px; margin: 0px;	}
				BODY { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; background: #FFF; padding: 15px; }
				H1 { font-family: Georgia, serif; font-size: 20px; font-weight: normal; }
				H2 { font-family: Georgia, serif; font-size: 16px; font-weight: normal;	margin: 0px 0px 10px 0px; }
				#myDiv { width: 150px; border: solid 1px #2AA7DE; background: #6CC8EF; text-align: center; padding: 4em .5em; margin: 1em; float: left;	}
				#myList { margin: 1em; float: left;	}
				#myList UL { padding: 0px; margin: 0em 1em;	}
				#myList LI { width: 100px; border: solid 1px #2AA7DE; background: #6CC8EF; padding: 5px 5px; margin: 2px 0px; list-style: none; }
				#options { clear: left;	}
				#options INPUT { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; width: 150px; }
				#map { height: 500px; border: 1px solid #888; width: 100% }
				#sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
			    #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; }
			    #sortable li span { position: absolute; margin-left: -1.3em; }
			</style>		
		
			<!-- Set some variables that we're going to use in OpenLayers' \"Controls/MousePosition.js\" -->
			<script type=\"text/javascript\">
				var OpenLayers_lowLat = $extentSLat;
				var OpenLayers_lowLon = $extentWLon;
				var OpenLayers_highLat = $extentNLat;
				var OpenLayers_highLon = $extentELon;
				var OpenLayers_width = $width;
				var OpenLayers_height = $height;
				var OpenLayers_units = \"$units\";
				var OpenLayers_measureRatio = 111.118974; //At 4096x4096, measurements are off by this constant ratio.
			</script>
			<!-- This is a custom, local version of OpenLayers. -->
		    <script src=\"OpenLayers-2.11/lib/OpenLayers.js\" type=\"text/javascript\"></script>
		<script src=\"jquery-1.4.2.min.js\" type=\"text/javascript\"></script>
		<script src=\"jquery.contextMenu.js\" type=\"text/javascript\"></script>
		<link href=\"jquery.contextMenu.css\" rel=\"stylesheet\" type=\"text/css\" />
            <link href=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css\" rel=\"stylesheet\" type=\"text/css\"/> 
            <script src=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js\"></script> 


<script type=\"text/javascript\">


var vectorLayer;
  //Our OpenLayers Map.
 var map;
  //VARIABLE, based on input.
 var numLayers = $numLayers;
  //These are the bounds of our image... we'll zoom to these bounds later. VARIABLE.
//WARP EDIT next two lines
 //var mapBounds = new OpenLayers.Bounds( -86.0, 34.0, -85.265625, 35.0);
var mapBounds = new OpenLayers.Bounds(0,0,4096,4096);

  //mapMinZoom of zero is too intense... 6 is good for the image we're using. 
  //  Toy with this using the commented-out code in overlay_getTileURL()
  //If we change the mapMinZoom to 6 (which we want to do), we can zoom out to 5 and beyond, and 
  //  the image will just disappear at those levels.
 var mapMinZoom = 1;
 var mapMaxZoom = 5;
 var dimensionout;
 var measureControls;
  //Our array of layers (tmslayers's)
 var overlays = new Array();
 //Our array of vector layers:
 var vectorLayers = new Array();
 OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
 OpenLayers.Util.onImageLoadErrorColor = \"transparent\";

 var id_map = new Array();
 //The renderer we use. Not sure what our alternatives are, but we're using OpenLayers.Layer.Vector.prototype.renderers.
 var renderer;

  //This function (at startup) initializes the map and all of its layers (Tomlinson)
 function init()
 {
	//The options that we'll use for the map initialization.
    var options =
    {
		  //No controls to begin with
	      controls: [],
		  //Projection is WGS84 (planar-- no distortion. Perfect for raster)
	      projection: new OpenLayers.Projection(\"EPSG:4326\"),
		  //This is based on \"mapMaxZoom\", or the max zoom level of the map. 
		  //  Toy with this using the commented-out code in overlay_getTileURL()
		//WARP EDIT (next 2 lines)
	      //maxResolution: 0.703125,
maxResolution:4096 / 256,
	
		 //This is based on \"mapMinZoom\", or the min zoom level of the map. 
		  //  Toy with this using the commented-out code in overlay_getTileURL()
		  //Alright, so setting this gives us a bunch of 404's for all of our images.
		  //minResolution: 0.1009598163571613,

		  //This is the extent when we're zoomed out all the way.
		  // Toy with this using the commented-out code in overlay_getTileURL()
		  // We want to use mapMinZoom=6, so un-commenting that code we got the following:
		  //   \"bounds: -97.116958618164 33.148681640625 -76.242935180664 36.400634765625\"
		  //Setting this also gives us a bunch of 404's.
	      //maxExtent: new OpenLayers.Bounds(-97.116958618164, 33.148681640625, -76.242935180664, 36.400634765625)
		  //Original extent: (whole wide world)
		//WARP EDIT (next two lines)
		  //maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90)
		maxExtent: new OpenLayers.Bounds(0,0,4096,4096),
//WARP EDIT (next line)
numZoomLevels:5
    };
    map = new OpenLayers.Map('map', options);
    map.zoomTo(1);
    OpenLayers.Util.onImageLoadError = function(){
     console.log(\"onImageLoadError\");
     this.src = \"none.png\"; };
    \n\n";

for($i=0; $i<count($img_name); $i++)
{
  if($i==0)
  {
    //The first layer we enter is layer zero, used as an invisible base layer.
    $out .= "overlays[0] = new OpenLayers.Layer.TMS( \"InvisibleBaseLayer\", \"\",
    {
	      serviceVersion: '.', layername: '".$img_directory[$i]."', alpha: true,
	      type: 'png', getURL: overlay_getTileURL, //don't mess with any of this
	      isBaseLayer: true //don't mess with any of this
	});
	map.addLayer(overlays[0]);
	if (OpenLayers.Util.alphaHack() == false) {overlays[0].setOpacity(0.0);}";
  }
  
  $out .= "	overlays[".($i+1)."] = new OpenLayers.Layer.TMS( \"".$img_name[$i]."\", \"\",
	{
		serviceVersion: '.', layername: '".$img_directory[$i]."', alpha: true,
		type: 'png', getURL: overlay_getTileURL,
		isBaseLayer: false
	});
	map.addLayer(overlays[".($i+1)."]);
	if (OpenLayers.Util.alphaHack() == false) {overlays[".($i+1)."].setOpacity(1.0);}\n\n";
}

$out .= "var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
	renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
	map.zoomToExtent( mapBounds );
	map.addControl(new OpenLayers.Control.PanZoomBar());
	map.addControl(new OpenLayers.Control.MousePosition());
	map.addControl(new OpenLayers.Control.MouseDefaults());
";
for($i=1; $i<=count($img_name); $i++)
{
	$out .= "overlays[$i].setVisibility(true);\n";
}
for($i=count($img_name); $i>0; $i--)
{
	$out .= "overlays[$i].setZIndex(".(count($img_name)-$i).");\n";
}

	$out .= "//Set the size of the map/footer.
	downsize();

    var sketchSymbolizers = {
        \"Point\": {
            pointRadius: 4,
            graphicName: \"square\",
            fillColor: \"white\",
            fillOpacity: 1,
            strokeWidth: 1,
            strokeOpacity: 1,
            strokeColor: \"#333333\"
        },
        \"Line\": {
            strokeWidth: 3,
            strokeOpacity: 1,
            strokeColor: \"#666666\",
            strokeDashstyle: \"dash\"
        },
        \"Polygon\": {
            strokeWidth: 2,
            strokeOpacity: 1,
            strokeColor: \"#666666\",
            fillColor: \"white\",
            fillOpacity: 0.3
        }
    };
    var style = new OpenLayers.Style();
    style.addRules([
                    new OpenLayers.Rule({symbolizer: sketchSymbolizers})
                    ]);
    var styleMap = new OpenLayers.StyleMap({\"default\": style});
    
    measureControls = {
        line: new OpenLayers.Control.Measure(
                                             OpenLayers.Handler.Path, {
                                             persist: true,
                                             immediate: true,
                                             geodesic: false,
                                             handlerOptions: {
                                             layerOptions: {styleMap: styleMap}
                                             }
                                             }
                                             ),
        measurePolygon: new OpenLayers.Control.Measure(
                                                OpenLayers.Handler.Polygon, {
                                                persist: false,
                                                immediate: true,
                                                geodesic: false,
                                                handlerOptions: {
                                                layerOptions: {styleMap: styleMap}
                                                }
                                                }
                                                )
    };
    
    var control;
    for(var key in measureControls) {
        control = measureControls[key];
        control.events.on({
                          \"measure\": handleMeasurements,
                          \"measurepartial\": handleMeasurements
                          });
        map.addControl(control);
    }
    document.getElementById('noneToggle').checked = true;
	dimensionout = document.getElementById('output'); ///Increased the scope of the variable generically labelled \"element,\" and renamed it to \"dimensionout\"
}


//The following functions are utilized in switching between the embedded and popup perspectives.
   function undockComponent()
   {
       //Define a new window with the subcode of the openlayers. (This will have to dynamically sized depending on layeramounts.)
       thisWindow = window.open(\"openlayers_".$stamp."_popout.html\",\"\",\"width=350,height=300,left=300px,top=300px,resizable=no,scrollbars=no\");

       //When the popup window has been initialized, hide the layercontrols as the bottom of the page and rename the docking button.
       document.getElementById('dockbutton').innerHTML = \"<button type='button' onclick='redockComponent()'><b>Redock</b></button>\";
       document.getElementById('resetbutton').style.display = \"none\"; document.getElementById('feet').style.display = \"none\";

       upsize(); //Stretch out the mapview of the image to fill in the space vacated by the now hidden layercontrols.
   }

   function redockComponent()
   {
   	thisWindow.close(); //Close out the window that was previously initiated.

   	//Unhide all the layercontrols back at the bottom of the page, and rerename the docking button back again.
       document.getElementById('dockbutton').innerHTML = \"<button type='button' onclick='undockComponent()'><b>Undock</b></button>\";
       document.getElementById('resetbutton').style.display = \"inline\"; document.getElementById('feet').style.display = \"block\";

       downsize(); //Shrink the mapview to the originally designated sizing.
   }

	 //Set the opacity sliders and numerical readouts to be 100% (we do this once, after we add those elements to the document)
   function resetEverything()
   {
   	//Access the opacity values from the openlayers to reset them
   	//back to fifty, and reset the corresponding slider values as well. \n";
for($i=0; $i<count($img_name); $i++)
{
	$out .= "document.getElementById(\"opacity".($i+1)."\").value = 100; changeSlider(\"#slider".($i+1)."\",100);\n";
}
$out .= "}

	 //These two methods are for communication between the (potential) two windows. (McPherson)
   function changeSlider(whichSlider,newValue) 
	 {
		$(document).ready(function() 
		{
			$(whichSlider).slider( \"option\", \"value\", newValue );
		});
	 }
   function readSlider(whichSlider)
	 {
		return $(whichSlider).slider( \"option\", \"value\" );
	 }
	
	
 	  //This function allows us to drag and drop the \"sortable\" elements in the document. This is some JQuery magic.
	  //  When a sortable item is dragged (\"highlighted\" and released) we iterate through the \"li\"`s and assign them
	  //  z indeces as we go (when we iterate through, we're going from the top of the document down, which logically
	  //  is what we want.) (Tomlinson)
     $(function() {
        $(\"#sortable\").sortable({
            placeholder: 'ui-state-highlight',
            stop: function(i) {
               placeholder: 'ui-state-highlight'
				var indexCount=0;
				//Iterate through all of the \"li\"`s in the document.
               $('li').each(function(index) {
				    //A layer will have a name like 'Layer \"myLayer\"'. Here, we do some text processing to get just the layername ('myLayer')
					var text2 = $(this).text().substring($(this).text().indexOf(\"Layer \\\"\")+7, $(this).text().length-$(this).text().indexOf(\"Layer \\\"\"));
					var text3 = text2.substring(0, text2.indexOf(\"\\\"\"));
					//If this is a layer (that is, the layername was found and is therefore a non-zero string)...
					if(map.getLayersByName(text3).length>0)
					{
						//Get the actual layer object (instead of the \"li\")
						var thisLayer = map.getLayersByName(text3)[0];
						//Set the zIndex, counting down. We're moving through the \"li\"`s from the top of the document, which
						// means that layers whose \"li\" are literally higher in the document have a higher z index. This is the
						// logic behind the sortable list.
						map.getLayersByName(text3)[0].setZIndex(overlays.length-indexCount);
						/*
						//There is an OpenLayers quirk (bug?) wherein we can't change the baselayer without some strange
						//  behaviors. So this part has been removed (this is the same reason we've included a non-visible
						//  base layer on map creation.)
						if(indexCount ==$('li').length-1)
						{
							console.log(\"IndexCount: \"+indexCount+\"-- setting \"+text3+\" as baselayer\");
							//Set baselayer here.
						}
						*/
					}
					else
					{
						//We've removed this, and now we're doing nothing. Remember, not all \"li\"`s are layers, so we can safely skip over some
						//  by doing nothing here.
						;
					}
					indexCount++;
               });
            }
        });
     });



	
	//This function handles the measurements (yep) from the OpenLayers.Control.Measurement...
    function handleMeasurements(event)
	{
		//(Tomlinson, mostly OpenLayers examples)
        var geometry = event.geometry;
        var units = event.units;
        var order = event.order;
        var measure = event.measure;
        var out = \"\";
    
			var height = parseInt($height);
			var width = parseInt($width);
			var units = \"$units\";
			
        	//Assuming our largest side is 90km...
          	var ratio = (OpenLayers_highLon - OpenLayers_lowLon) / OpenLayers_width;
			//The \"order\" variable from the event lets us know if this is in units or units squared (distance or area)
          	if(order==1)
          	{
            	//out += \"Measurement: \" + (measure/ratio).toFixed(3) + \" \" + units;
            	out += \"Measurement: \" + ((measure/OpenLayers_measureRatio)/ratio).toFixed(3) + \" \" + units;
          	}
          	else
          	{
            	//out += \"Measurement: \" + (measure/(ratio*ratio)).toFixed(3) + \" \" + units + \"<sup>2</\" + \"sup>\";
            	out += \"Measurement: \" + ((measure/(OpenLayers_measureRatio*OpenLayers_measureRatio))/(ratio*ratio)).toFixed(3) + \" \" + units + \"<sup>2</\" + \"sup>\";
          	} 

	    //Output this business to the screen.
        dimensionout.innerHTML = out;
    }
    
 	function toggleControl(element) {
		//Well, we've been having a strange error where the top layer disappears when you hit this function a couple of times.
		//So, ugly as it is, we're going to re-do all of the layer visibilities, etc here. (This worked-- problem \"solved\")
		//First, get the properties...
		var opacities = Array();
		var visibility = Array();
		var zIndeces = Array();
		for (var i=0; i<overlays.length; i++) {
		   opacities[i] = overlays[i].opacity;
		   visibility[i] = overlays[i].getVisibility();
		   zIndeces[i] = overlays[i].getZIndex();
		}

		//Now, the actual \"toggleControl\" code:
        for(key in measureControls) {
			//We get currentGeo from measure.js every time a point is added to a measurement.
			//This is a good place to reset it. (we don't want to delete it, just empty it out.)
			currentGeo = null;
			
            var control = measureControls[key];
            if(element.value == key && element.checked) {
                control.activate();
            } else {
                control.deactivate();
            }
        }

		//Now restore the properties of all those layers:
		for (var i=0; i<overlays.length; i++) {
		   overlays[i].setOpacity(opacities[i]);
		   overlays[i].setVisibility(visibility[i]);
		   overlays[i].setZIndex(zIndeces[i]);
		}
    }

        

	//This is the function that gets the tile URLs for our layers. This same function is used for all overlayss, because it's simple.
	function overlay_getTileURL(bounds)
	{
		bounds = this.adjustBounds(bounds);
		var res = this.map.getResolution();
		var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
       	var y = Math.round (this.maxExtent.top / (res * this.tileSize.h) - ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h)));
		if(y>0)
			y--;

		var z = this.map.getZoom();
		var path = this.serviceVersion + \"/\" + this.layername + \"/\" + z + \"/\" + x + \"/\" + y + \".\" + this.type;
		var url = this.url;

		/*var layername = this.layername;
		var type = this.type;
		var returnVar = \"\";
		$.ajax({
    		url:layername + \"/\" + z + \"/\" + x + \"/\" + y + \".\" + type,
    		type:'HEAD',
    		error: function()
    		{
        		//file not exists
        		console.log(\"File not found\");
        		returnVar = \"none.png\";
    		},
    		success: function()
    		{
        		//file exists
    		    console.log(\"File found!\");
    		    returnVar = layername + \"/\" + z + \"/\" + x + \"/\" + y + \".\" + type;
       		    console.log(\"ReturnVar:\" + returnVar);
    		}
		});
		console.log(\"Returnvar: \"+returnVar);*/
		
		//if (mapBounds.intersectsBounds( bounds ) && z >= mapMinZoom && z <= mapMaxZoom)
		//{
			return this.layername + \"/\" + z + \"/\" + x + \"/\" + y + \".\" + this.type;
		//}
		//else {return \"none.png\";}
	}

	//Get the height of the window... (McPherson)
	function getWindowHeight()
	{
		if (self.innerHeight) return self.innerHeight;
		if (document.documentElement && document.documentElement.clientHeight)
		{
			return document.documentElement.clientHeight;
		}
		if (document.body) return document.body.clientHeight;
		{
			return 0;
		}
	}

	//Get the height of the window... (McPherson)
	function getWindowWidth()
	{
		if (self.innerWidth) return self.innerWidth;
		if (document.documentElement && document.documentElement.clientWidth)
		{
			return document.documentElement.clientWidth;
		}
		if (document.body) return document.body.clientWidth;
		{
			return 0;
		}
	}
	
	//Both upsize() and downsize() are used to manage the available space of the page for the mapview.
	//Upsize is called when the controls are detached-- it makes the map larger and zeroes out the footer. (McPherson)
	function upsize()
	{
		var height  = getWindowHeight();
		console.log(\"GetHeight: \"+height)
		var map = document.getElementById(\"map\");  
		var header = document.getElementById(\"header\");  
		var subheader = document.getElementById(\"subheader\");  
		var footer = document.getElementById(\"footer\");
		map.style.height = (getWindowHeight()-105) + \"px\";
		map.style.width = (getWindowWidth()-20) + \"px\";
		header.style.width = (getWindowWidth()-20) + \"px\";
		subheader.style.width = (getWindowWidth()-20) + \"px\";
		footer.style.height = \"0px\";
		if (map.updateSize) {map.updateSize();};
	} 

    //Downsize is called when the controls are re-attached-- makes the map smaller and restores the footer. (McPherson)
	function downsize()
	{
		var height  = getWindowHeight();
		console.log(\"GetHeight: \"+height)
		var map = document.getElementById(\"map\");  
		var header = document.getElementById(\"header\");  
		var subheader = document.getElementById(\"subheader\");  
		var footer = document.getElementById(\"footer\");
		map.style.height = (getWindowHeight()-(80*numLayers)-105) + \"px\";
		map.style.width = (getWindowWidth()-20) + \"px\";
		header.style.width = (getWindowWidth()-20) + \"px\";
		subheader.style.width = (getWindowWidth()-20) + \"px\";
		footer.style.height = 80*numLayers+\"px\";
		if (map.updateSize) {map.updateSize();};
	} 

	//This function calls either downsize() and upsize(), depending on which one needs to be done right now. (McPherson)
	function resize()
	{
		var footer = document.getElementById(\"footer\");
		if(footer.style.height==\"0px\") {upsize();}
		else {downsize();}
	}
	
	//The following functions all take as an argument the index of the desired layer in the overlays array.
	
	//This function is called when a user clicks the \"update\" button. This means they've put in a numerical
	//  value for opacity and now we want to implement that change. (McPherson)
	function inputOpacity(tmsnum)
	{
//This is all ghetto'd up. If tmsnum > number of tmslayers, we use it on vectorLayers... there's a more elegant way
		var value = parseFloat(document.getElementById(\"opacity\" + tmsnum).value);
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			if(tmsnum < overlays.length)
				overlays[tmsnum].setOpacity(value/100);
			else
				vectorLayers[tmsnum - overlays.length].setOpacity(value/100);
		}
		updateAll(tmsnum, value);
	}
	
	//This function syncs the opacity slider bar and the opacity numerical output to a desired value,
	//  so they're always the same. (McPherson)
	function updateAll(tmsnum, value)
	{
		document.getElementById(\"opacity\" + tmsnum).value=value;
		$(\"#slider\" + tmsnum).slider(\"value\", value);
	}

	//Reset the map to the center of the image.
	function centerMap()
	{
		var centerLat = ($extentNLat + $extentSLat) / 2;
		var centerLon = ($extentWLon + $extentELon) / 2;		
		var center = new OpenLayers.LonLat(centerLon, centerLat);
		map.setCenter(center, map.zoom, true, false);
	}
	
	//Toggle the layer \"on\" and \"off\"... setting opacity to zero, or to whatever value is in the
	//  numerical output box. (McPherson)
	function switchLayer(tmsnum)
	{
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			var value = parseFloat(document.getElementById(\"opacity\" + tmsnum).value);
			if(tmsnum < overlays.length)
				overlays[tmsnum].setOpacity(value/100);
			else
				vectorLayers[tmsnum - overlays.length].setOpacity(value/100);
		}
		else
		{
			if(tmsnum < overlays.length)
				overlays[tmsnum].setOpacity(0);
			else
				vectorLayers[tmsnum - overlays.length].setOpacity(0);
		}
	}
	
	//When we move the slider, this function is called to change the opacity and sync the numerical output. (McPherson)
	function slideOpacity(event, ui, tmsnum)
	{
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			overlays[tmsnum].setOpacity(ui.value/100);
		}
		updateAll(tmsnum, ui.value);
	}
	
/*	Implemented a non-toggle version in checkForUpdates
	//This will toggle the \"there are updates\" div on and off
	function toggle() {
		var ele = document.getElementById(\"updateText\");
		//var text = document.getElementById(\"displayText\");
		if(ele.style.display == \"block\") {
	    		ele.style.display = \"none\";
			text.innerHTML = \"show\";
	  	}
		else {
			ele.style.display = \"block\";
			text.innerHTML = \"hide\";
		}
	}*/
</script>
</head>
    <body onload=\"init()\" onresize=\"resize()\">
        <center>
	        <div id=\"header\">
		        <h1>MapArt OpenSource Viewer</h1>
		    </div>
            <div id=\"subheader\">Based on OpenLayers.html from <a href=\"http://www.maptiler.org/\">MapTiler.</a> Images tiled with <a href=\"http://www.klokan.cz/projects/gdal2tiles/\">GDAL2Tiles</a>. MapTiler and GDAL2TILES Copyright &copy; 2008 <a href=\"http://www.klokan.cz/\">Klokan Petr Pridal</a>,  <a href=\"http://www.gdal.org/\">GDAL</a> &amp; <a href=\"http://www.osgeo.org/\">OSGeo</a> <a href=\"http://code.google.com/soc/\">GSoC</a>. OpenSource project started by &copy; 2012 <a href=\"secondsitellc.com\">MapArt, LLC</a>.
			<!--From Klokan: PLEASE, LET THIS NOTE ABOUT AUTHOR AND PROJECT SOMEWHERE ON YOUR WEBSITE, OR AT LEAST IN THE COMMENT IN HTML. THANK YOU!! :D-->
		    </div>
		</center>
		<!--This is the mapview.-->
		<center>
			<div id=\"map\">
			<!-- That's the map! This is where the magic happens. -->	
			</div>
		</center>

		<!--Below the mapview, we provide a readout of measurements, as well as a set of buttons that allows the users to undock or reset the layercontrols.-->
		<b id=\"output\" style=\"float:left; margin-left:14px; position:absolute;\">Measurement: ...</b>
		<center>
			<div id=\"dockbutton\" style=\"display:inline;\">
				<button type=\"button\" onclick=\"undockComponent()\">
					<b>Undock</b>
				</button>
			</div>
			<div id=\"resetbutton\" style=\"display:inline;\">
				<button type=\"button\" onclick=\"resetEverything()\">
					<b>Reset</b>
				</button>
			</div>
			
			
		</center>
		<div id=\"footer\">
			<div id=\"feet\">
			    <!--Much of the layercontrols are made up of sliders, and as such, we must initalize the style and scripts.-->
				<style type=\"text/css\">\n";
for($i=1; $i<=count($img_name); $i++)
  $out .= "#slider$i { margin: 10px; width: 90%;}\n";

$out .="		</style>
				
				<!-- Set the functions for the sliders. We use slideOpacity for both \"slide\" and \"stop\". -->
				<script type=\"text/javascript\">\n";
for($i=1; $i<=count($img_name); $i++)
   $out .= "$(document).ready(function() {\$(\"#slider$i\").slider({step:0.1, slide:function(event, ui){ slideOpacity(event, ui, $i); }, stop:function(event, ui){ slideOpacity(event, ui, $i);}, value:100});});\n";

$out .= "			</script>


				
				<!--The radiobuttons below allow the users to designate how they want to navigate or measure the mapview.-->
				<style>
					ul {list-style-type: none;}
				</style>

				<div id=\"options\">
					<ul id=\"controlToggle\">
						<li>
							<input type=\"radio\" name=\"type\" value=\"none\" id=\"noneToggle\" onclick=\"toggleControl(this);\" checked=\"checked\" />
							<label for=\"noneToggle\">Navigate (No Measurement)</label>
						</li>
						<li>
							<button type=\"button\" id=\"centerButton\" style=\"display:inline;\" onclick=\"centerMap()\">Center Map</button>                            
						</li>
						<li>
							<input type=\"radio\" name=\"type\" value=\"line\" id=\"lineToggle\" onclick=\"toggleControl(this);\" />
							<label for=\"lineToggle\">Measure Distance</label>
						</li>
						<li>
							<input type=\"radio\" name=\"type\" value=\"measurePolygon\" id=\"polygonToggle\" onclick=\"toggleControl(this);\" />
							<label for=\"polygonToggle\">Measure Area</label>
						</li>						
					</ul>
				</div>

				<!--Each of the listing elements below designate the layercontrols, with a label, checkbox, slider, textbox and button.-->
				<ul id=\"sortable\">\n";
for($i=1; $i<=count($img_name); $i++)
{
	$out .= "			<li id='listItem$i' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>
    					<input type=\"checkbox\" id=\"checkbox$i\" value=\"checkbox$i\" checked=\"checked\" onchange=\"switchLayer($i)\" />Layer \"".$img_name[$i-1]."\"
    					<table width=\"100%\">   
        					<tr width=\"100%\">
            					<td>Opacity:</td>
            					<td width=\"70%\"><div id=\"slider$i\" display=\"inline-block\"></div></td>
            					<td><input type=\"text\" id=\"opacity$i\" size=\"1\" value=\"100\"/>%&nbsp;&nbsp;&nbsp;<button id=\"changeOpacity$i\" onclick=\"inputOpacity($i)\">Update</button></td>
        					</tr>
    					</table>
					</li>\n";
}
$out .= "				</ul>
					<br>
				</div></div>
				<script>
					//Set all of the opacities to their initial values of 100%
					resetEverything()
				</script>
			</body>
		</html>";

$fh = fopen("openlayers_".$stamp.".html", "w");
fwrite($fh, $out);
fclose($fh);

//Now write the "popout" file, as well:
$out = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
		<html xmlns=\"http://www.w3.org/1999/xhtml\"
		  <head>
		    <title>Demo</title>
		    <meta http-equiv='imagetoolbar' content='no'/>
<!-- //MARKER4: Below: changed map height to 500px and added \"footer\" div; removed \"width:100%\" and \"height:100%\" and \"overflow:hidden\" from html/body divs -->
		    <style type=\"text/css\"> v\:* {behavior:url(#default#VML);}
		        html, body { padding: 0; font-family: 'Lucida Grande',Geneva,Arial,Verdana,sans-serif; }
		        body { margin: 10px; background: #fff; overflow-y:scroll; }
		        h1 { margin: 0; padding: 6px; border:0; font-size: 20pt; }
		        #header { height: 43px; padding: 0; background-color: #eee; border: 1px solid #888; }
		        #subheader { height: 12px; text-align: right; font-size: 10px; color: #555;}
      	    </style>
		    <script src=\"OpenLayers-2.11/lib/OpenLayers.js\" type=\"text/javascript\"></script>
		<script src=\"jquery-1.4.2.min.js\" type=\"text/javascript\"></script>
		<script src=\"jquery.contextMenu.js\" type=\"text/javascript\"></script>
		<link href=\"jquery.contextMenu.css\" rel=\"stylesheet\" type=\"text/css\" />
            <link href=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css\" rel=\"stylesheet\" type=\"text/css\"/> 
            <script src=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js\"></script> 

               
<style type=\"text/css\">
    #sortable
	{
        list-style-type: none;
        margin: 0;
        padding: 0;
        width: 100%;
    }
	
    #sortable li
	{
        margin: 0 3px 3px 3px;
        padding: 0.4em;
        padding-left: 1.5em;
    }
	
    #sortable li span
	{
        position: absolute;
        margin-left: -1.3em;
    }
</style>                    

<script type=\"text/javascript\">
  
     $(function() {
        $(\"#sortable\").sortable({
            placeholder: 'ui-state-highlight',
            stop: function(i) {
               placeholder: 'ui-state-highlight'
				var indexCount=0;
				//Iterate through all of the \"li\"`s in the document.
               $('li').each(function(index) {
				    //A layer will have a name like 'Layer \"myLayer\"'. Here, we do some text processing to get just the layername ('myLayer')
					var text2 = $(this).text().substring($(this).text().indexOf(\"Layer \\\"\")+7, $(this).text().length-$(this).text().indexOf(\"Layer \\\"\"));
					var text3 = text2.substring(0, text2.indexOf(\"\\\"\"));
console.log(\"text3: \"+text3);
					//If this is a layer (that is, the layername was found and is therefore a non-zero string)...
					if(window.opener.map.getLayersByName(text3).length>0)
					{
						//Get the actual layer object (instead of the \"li\")
						var thisLayer = window.opener.map.getLayersByName(text3)[0];
						//Set the zIndex, counting down. We're moving through the \"li\"`s from the top of the document, which
						// means that layers whose \"li\" are literally higher in the document have a higher z index. This is the
						// logic behind the sortable list.
						window.opener.map.getLayersByName(text3)[0].setZIndex(window.opener.overlays.length-indexCount);
						/*
						//There is an OpenLayers quirk (bug?) wherein we can't change the baselayer without some strange
						//  behaviors. So this part has been removed (this is the same reason we've included a non-visible
						//  base layer on map creation.)
						if(indexCount ==$('li').length-1)
						{
							console.log(\"IndexCount: \"+indexCount+\"-- setting \"+text3+\" as baselayer\");
							//Set baselayer here.
						}
						*/
					}
					else
					{
						//We've removed this, and now we're doing nothing. Remember, not all \"li\"`s are layers, so we can safely skip over some
						//  by doing nothing here.
						;
					}
					indexCount++;
               });
            }
        });
     });
</script>

<script type=\"text/javascript\">
	function setOpacity(r)
	{
		overlays.setOpacity(r);
		overlays.setVisibility(true);
	}

	function slideOpacity(event, ui, tmsnum)
	{
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			window.opener.overlays[tmsnum].setOpacity(ui.value/100);
		}
		updateAll(tmsnum, ui.value);
	}
	
	function inputOpacity(tmsnum)
	{
		var value = parseFloat(document.getElementById(\"opacity\" + tmsnum).value);
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			window.opener.overlays[tmsnum].setOpacity(value/100);
		}
		updateAll(tmsnum, value);
	}
	
	function updateAll(tmsnum, value)
	{
		document.getElementById(\"opacity\" + tmsnum).value=value;
		window.opener.document.getElementById(\"opacity\" + tmsnum).value=value;
		window.opener.changeSlider(\"#slider\" + tmsnum,value);
		$(\"#slider\" + tmsnum).slider(\"value\", value);
	}
	
	function centerMap()
	{
		var centerLat = ($extentNLat + $extentSLat) / 2;
		var centerLon = ($extentWLon + $extentELon) / 2;		
		var center = new OpenLayers.LonLat(centerLon, centerLat);
		window.opener.map.setCenter(center, window.opener.map.zoom, true, false);
	}

	function switchLayer(tmsnum)
	{
		if(document.getElementById(\"checkbox\" + tmsnum).checked==true)
		{
			var value = parseFloat(document.getElementById(\"opacity\" + tmsnum).value);
			window.opener.overlays[tmsnum].setOpacity(value/100);
			window.opener.document.getElementById(\"checkbox\" + tmsnum).checked = true;
		}
		else
		{
			window.opener.overlays[tmsnum].setOpacity(0);
			window.opener.document.getElementById(\"checkbox\" + tmsnum).checked = false;
		}
	}
	
	function init()
	{\n";
for($i=1; $i<=count($img_name); $i++)
	$out .= "if(window.opener.document.getElementById(\"checkbox$i\").checked == false) {document.getElementById(\"checkbox$i\").checked = false;}\n";
for($i=1; $i<=count($img_name); $i++)
	$out .= "document.getElementById(\"opacity$i\").value = window.opener.document.getElementById(\"opacity$i\").value;\n";

	$out .= "var lastItem = \"\";
		var thisItem = \"\";
		var indexCount = 0;
		$('li', window.opener.document).each(function(index)
		{
			if(indexCount > 0)
			{
				lastItem = thisItem;
			}
			thisItem = $(this).attr(\"id\");
			if(indexCount > 0)
			{
				$('#'+thisItem).insertAfter($('#'+lastItem));
			}
			indexCount++;
		});
		window.opener.document.dimensionout = document.getElementById('output');
	}
</script>
</head>
<body onload=\"init()\" onunload=\"window.opener.redockComponent()\">
<div id=\"footer\"><center>
<style type=\"text/css\">\n";
for($i=1; $i<=count($img_name); $i++)
	$out .= "#slider$i { margin: 10px; width: 90%;}\n";

$out .="</style> 
<script type=\"text/javascript\">\n";
	
for($i=1; $i<=count($img_name); $i++)
	$out .= "$(document).ready(function() {
		\$(\"#slider$i\").slider({step:0.1, slide:function(event, ui){ slideOpacity(event, ui, $i); }, stop:function(event, ui){ slideOpacity(event, ui, $i);}, value:100});
	});\n";

$out .= "</script>
    
    <style>ul {list-style-type: none;}</style>
    <div id=\"options\" style=\"text-align:left;\">
        <ul id=\"controlToggle\">
            <li>
                <input type=\"radio\" name=\"type\" value=\"none\" id=\"noneToggle\" onclick=\"window.opener.toggleControl(this); window.opener.document.getElementById('noneToggle').checked='checked'\" />
                <label for=\"noneToggle\">navigate</label> <script>if(window.opener.document.getElementById('noneToggle').checked) {document.getElementById('noneToggle').checked = 'checked';}</script>
            </li>
	    <li>
		<button type=\"button\" id=\"centerButton\" style=\"display:inline;\" onclick=\"centerMap()\">Center Map</button>                            
	    </li>
            <li>
                <input type=\"radio\" name=\"type\" value=\"line\" id=\"lineToggle\" onclick=\"window.opener.toggleControl(this); window.opener.document.getElementById('lineToggle').checked='checked'\" />
                <label for=\"lineToggle\">measure distance</label> <script>if(window.opener.document.getElementById('lineToggle').checked) {document.getElementById('lineToggle').checked = 'checked';}</script>
            </li>
            <li>
                <input type=\"radio\" name=\"type\" value=\"measurePolygon\" id=\"polygonToggle\" onclick=\"window.opener.toggleControl(this); window.opener.document.getElementById('polygonToggle').checked='checked'\" />
                <label for=\"polygonToggle\">measure area</label> <script>if(window.opener.document.getElementById('polygonToggle').checked) {document.getElementById('polygonToggle').checked = 'checked';}</script>
            </li>
        </ul>
    </div>
    
<ul id=\"sortable\">\n";

for($i=1; $i<=count($img_name); $i++)
	$out .= "<li id='listItem$i' class='ui-state-default'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span>
    <input type=\"checkbox\" id=\"checkbox$i\" value=\"checkbox$i\" checked=\"checked\" onchange=\"switchLayer($i)\" />Layer \"".$img_name[$i-1]."\"
    <table width=\"100%\">   
        <tr width=\"100%\">
            <td>Opacity:</td>
            <td width=\"70%\"><div id=\"slider$i\" display=\"inline-block\"></div></td>
            <td><input type=\"text\" id=\"opacity$i\" size=\"2\" value=\"100\"/>%&nbsp;&nbsp;&nbsp;<button id=\"changeOpacity$i\" onclick=\"inputOpacity($i)\">Update</button></td>
        </tr>
    </table>
</li>\n";

$out .= "</ul>
</center></div>

<script>
window.innerHeight = document.documentElement.scrollHeight;
</script>					
</body>
</html>";

$fh = fopen("openlayers_".$stamp."_popout.html", "w");
fwrite($fh, $out);
fclose($fh);


echo "openlayers_".$stamp.".html";
?>
