<?php
// this file contains the contents of the popup window
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Insert Map List</title>
<meta http-equiv="Expires" content="Sat, 1 Jan 2000 08:00:00 GMT" />

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script language="javascript" type="text/javascript" src="../../../../../wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="../../js/admin/ShortcodeWizard.js"></script>

<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="../../css/admin/ShortcodeWizard.css?v=0_1"></link>
</head>
<body>
    <form action="/" method="get" accept-charset="utf-8">
    <div id="ScrollContainer">
        <div id="Scroller">
        <!--Pick file list type-->
        <div id="MapTypeChoice" class="scrollPanel">
            <a href="#" class="pickFiles btn">Pick Locations<span>See a list of locations and choose which ones to show.</span><div class="image"></div></a>
            <a href="#" class="generateList btn">Pick Categories<span>See a list of location categories and choose which ones to show.</span><div class="image"></div></a>
        </div>

        <!-- Pick locations from a list -->
        <div id="PickLocations" class="scrollPanel hidden">
            <h2>Pick Locations</h2>            
            <p>Pick the locations you want to show from the list below.</p>
            <div class="fieldError hidden" id="FileError">Please pick at least one location to display.</div>
            <div id="AllLocations" class="loading"></div>
            <div class="buttonBox">
                <a href="#" class="back btn goToFirst">Back</a>
                <a href="#" class="next btn">Next</a>
            </div>
        </div>

        <!-- Pick locations by criteria -->
        <div id="GenerateList" class="scrollPanel hidden">
            <h2>Generate List</h2>
            <p>Show all locations in a particular category. Leave blank to show all.</p>
            <div class="fieldError hidden" id="TypeError">Show the following categories:</div>
            <div id="CategoryList" class="loading"></div>

            <div class="buttonBox">
                <a href="#" class="back btn goToFirst">Back</a>
                <a href="#" class="next btn">Next</a>
            </div>
        </div>

        <!-- Choose map or list, and map styles -->
        <div id="ListMapType" class="scrollPanel">
            <h2>List Styles</h2>
            <h3>Type of map list</h3>
            <p>Map and List, Map only, or List only.</p>
            <div id="ListType">
                <ul class="clearfix unstyled">
                    <li><a href="#" id="both" class="both selected" data-listtype="both">Map and list</a></li>
                    <li><a href="#" class="maponly" data-listtype="maponly">Map only</a></li>
                    <li><a href="#" class="listonly" data-listtype="listonly">List only</a></li>
                </ul>
            </div>
            <div id="MapPosition">
                <h3>Map position</h3>
                <p>Pick how you want the map and list to appear.</p>
                <div id="MapType">
                    <ul class="clearfix unstyled">
                        <li><a href="#" class="selected above" data-maptype="above">Map above</a></li>
                        <li><a href="#" class="leftmap" data-maptype="leftmap">Map to left</a></li>
                        <li><a href="#" class="rightmap" data-maptype="rightmap">List to right</a></li>
                    </ul>
                </div>
            </div>
            <div id="MarkerClustering">
                <h3>Marker Clustering</h3>
                <p>Enable clustering for maps with a large number of markers.</p>
                <div id="MarkerType">
                    <ul class="clearfix unstyled">
                        <li><a href="#" class="selected unclustered" data-markerclustering="false">Unclustered</a></li>
                        <li><a href="#" class="clustered"  data-markerclustering="true">Clustered</a></li>
                    </ul>
                </div>
            </div>            
            <div class="buttonBox">
                <a href="#" class="back btn goToFirst">Back</a>
                <a href="#" class="next btn">Next</a>
            </div>
        </div>

        <!-- Map specific options -->
        <div id="MapDetails" class="scrollPanel">
            <h2>Map Options</h2>
            <!--TODO: add error check for default and lat long-->
            <h3>Start position</h3>
            <div style="height:325px;">
            <div class="fieldError hidden" id="LatLongError">Please specify a valid latitiude and longitude.</div>
            <label style="display:block;margin:10px 0;"><input type="checkbox" id="autozoom" name="autozoom" checked="checked" value="true">Automatically calculate the map centre and zoom level to fit all markers</label>
            <div class="manualzoomsettings" id="manualzoomsettings" style="display:none;">
            <label>Default zoom level
                <select class="defaultzoom" name="defaultzoom" id="defaultzoom">
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      <option value="5">5</option>
                      <option value="6">6</option>
                      <option value="7">7</option>
                      <option value="8">8</option>
                      <option value="9" selected="selected">9</option>
                      <option value="10">10</option>
                      <option value="11">11</option>
                      <option value="12">12</option>
                      <option value="13">13</option>
                      <option value="14">14</option>
                      <option value="15">15</option>
                      <option value="16">16</option>
                      <option value="17">17</option>
                      <option value="18">18</option>
                      <option value="19">19</option>
                      <option value="20">20</option>
                      <option value="21">21</option>
                </select>
                </label>
                <span class="help-block">This is the zoom level that the map starts at. 1 is the whole world, 21 is building level.</span>                
            <ul class="twoColList clearfix">
                <li><label>Latitude <input type="text" id="startlat" name="startlat" value=""></label></li>
                <li><label>Longitude <input type="text" id="startlong" name="startlong" value=""></label></li>
            </ul>
            </div>

            <h3>Other options</h3>
            <ul class="twoColList clearfix">
                <li><label>Selected zoom level<br />
                <select class="selectedzoomlevel" name="selectedzoomlevel" id="selectedzoomlevel">
                      <option value="nozoom" selected="selected">No zoom</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      <option value="5">5</option>
                      <option value="6">6</option>
                      <option value="7">7</option>
                      <option value="8">8</option>
                      <option value="9">9</option>
                      <option value="10">10</option>
                      <option value="11">11</option>
                      <option value="12">12</option>
                      <option value="13">13</option>
                      <option value="14">14</option>
                      <option value="15">15</option>
                      <option value="16">16</option>
                      <option value="17">17</option>
                      <option value="18">18</option>
                      <option value="19">19</option>
                      <option value="20">20</option>
                      <option value="21">21</option>
                </select>
                </label>                
                <label style="display:block;margin:10px 0;"><input type="checkbox" id="keepzoomlevel" name="keepzoomlevel" value="true">Stay zoomed in when a location is closed</label>
                <span class="help-block">This is the zoom level when a single location is selected. 1 is the whole world, 21 is building level.</span>
                </li>
                <li>
                    <label>Search type
                    <select class="searchtype" name="searchtype" id="searchtype">
                          <option value="none">None (Hide search fields)</option>
                          <option value="simple" selected="selected">Text</option>
                          <option value="locationbased">Location based</option>
                          <option value="combo">Location and text</option>
                    </select>
                    </label>
                    <span class="help-block">The normal search looks at the text for each location. The location based search finds all locations within range of the searched for location.</span>
                    <div id="CountryContainer" style="display:none">
                    <label>Search suffix<input type="text" id="country" name="country" value=""></label>
                    <span class="help-block">(optional) Use this to restrict location searches to a particular place (e.g. UK)</span>
                    </div>
                </li>
            </ul>
            </div>
            <div class="buttonBox">
                <a href="#" class="back btn">Back</a>
                <a href="#" class="next btn goToFinal">Next</a>
            </div>
        </div>

        <!-- Choose options -->
        <div id="ChooseOptions" class="scrollPanel">
            <h2>List options</h>
            <h3>Sorting</h3>
            <div style="height:325px;">
                <div class="fieldContainer">
                        <p><label for="FieldToSortBy">Initially sort</label>
                        <select name="DirectionToSortBy" class="" id="DirectionToSortBy">
                            <option value="ASC">Ascending (A-Z)</option>
                            <option value="DESC">Descending (Z-A)</option>
                        </select></p>
                </div>
                <h3>Other options</h3>
                <div class="fieldContainer" id="OtherOptions">

                    <ul class="twoColList clearfix">
                        <li><label><input type="checkbox" id="hidefilter" name="hidefilter" value="true">Hide filter button</label></li>
                        <li><label><input type="checkbox" id="hidesort" name="hidesort" value="true">Hide sort button</label></li>
                        <li><label><input type="checkbox" id="hidedirections" name="hidedirections" value="true">Hide Directions</label></li>
                        <li><label><input type="checkbox" id="hideviewdetailbuttons" name="hideviewdetailbuttons" value="true">Hide view details button</label></li>
                        <li><label><input type="checkbox" id="hidecategoriesonitems" name="hidecategoriesonitems" value="true">Hide categories on items</label></li>
                        <li><label><input type="checkbox" id="openinnew" name="openinnew" value="true">Open view details link in a new window</label></li>
                    </ul>

                    <ul class="clearfix unstyled">
                        <li><label><input type="checkbox" id="geoenabled" name="geoenabled" value="true">Geo enabled map</label>
                                <p class="help-block">This option makes browsers prompt the user for their location on page load, and allows locations to be sorted by distance.</p>
                        </li>
                        <li>
                        <p><label for="FilesPerPage">Locations per page</label>
                        <select name="FilesPerPage" class="" id="FilesPerPage">
                            <option value="3">3</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                        </select> </p>
                        </li>
                        </ul>
                </div>
            </div>
            <div class="buttonBox">
                <a href="" class="back btn">Back</a>
                <a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" class="create btn">Insert</a>
            </div>
        </div>

        </div><!--EOF:Scroller-->
    </div>
    </div>
</body>
</html>