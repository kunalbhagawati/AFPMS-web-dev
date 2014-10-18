<?php

/**
* Author : Kunal Bhagawati
*
* This file provides a form to the end user (operations/product team) to enter the neccessary values for the roadblock popup that initially appears in the search page (search/searchres.php).  It sends these values to the backend where they are stored as JSON files, and also in the DB as backup.
*
* Flow of the system can be found in the README file in this directory
*
*/ 

ini_set("display_errors","1");
error_reporting(E_ALL);

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);

// // Header
include_once $DOCROOTPATH."/template/admin-header.php";

// // authentication
require_once $DOCROOTPATH."/admin/ipadminauthenticate.cil14";

require_once $DOCROOTBASEPATH."/iplib/ipgenericfunctions.cil14";

$currentUserID = getAdminCookieInfo();

require_once "basicFunctions.php";	
?>



<!-- 

 |`    _  __|_. _  _  _ |._|_    __|_ _  __|_ _  |_  _  _ _ 
~|~|_|| |(_ | |(_)| |(_||| |\/  _\ | (_||  | _\  | |(/_| (/_
							/                               
 -->

<?php ini_set("display_errors","1"); error_reporting(E_ALL); ?> 	<!-- Display server errors -->

<link rel="stylesheet" type="text/css" href="css/moduleStyles.css">
<link rel="stylesheet" type="text/css" href="jquery-ui-1.11.0.custom/jquery-ui.css">

<style>
.ui-autocomplete-loading {
   background: white url("jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif") right center no-repeat;
 }
</style>


<!-- ===
PAGE CONTENT STARTS
=== -->
<br>
<div id="div_overlay"> <div id="div_overlay_text"><img src="jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif"><br><br>Loading...</div> </div> <!-- Overlay for loading, error etc -->

<div id="heading">Manage Search Page Roadblocks</div>

<br/>
<br/>

<div id="inner-container">
	<div id="div_takeVals">	<!-- Form to enter a transaction -->
		<form id="form_textValues">
			<div id ="div_targetLocation">

				<div id="div_targetLocation_components" class="multipleLocationSelector">
					<div class="div_targetLocation_component inline_block">
						<div class="div_label_targetLocation"><label class="inner" for="input_state">State </label></div>
						<input type="text" class="input_targetLocation" name="state" data-for="state" id="input_state" size='30' placeholder="Enter any 3 letters to begin">
					</div>
					
					<div class="div_targetLocation_component inline_block">
						<div class="div_label_targetLocation"><label class="inner" for="input_city">City </label></div>
						<input type="text" class="input_targetLocation" name="city" data-for="city" id="input_city" size='30' placeholder="Enter any 3 letters to begin">
					</div>
				</div>

				<div id="div_targetLocation_info">
				</div>

				<br>
				<div>
				<div id="div_targetLocation_fetchedCities">
					<div class="clearButton" onClick='clear_state_list();'>Clear List</div>
					<div id="div_targetLocation_fetchedCities_col1" class='div_targetLocation_fetchedCities_cols'></div>
					<div id="div_targetLocation_fetchedCities_col2" class='div_targetLocation_fetchedCities_cols'></div>
					<div id="div_targetLocation_fetchedCities_col3" class='div_targetLocation_fetchedCities_cols'></div>
					<div id="div_targetLocation_fetchedCities_col4" class='div_targetLocation_fetchedCities_cols'></div>
				</div>
				<br>
				<div id="div_targetLocation_selectedCities"></div>
			</div>

			<div>
				<div>
					<label class="outer" for="input_uploadImage">Roadblock Banner : </label>
					<input  type="file" name="RBImage" id="input_uploadImage">
					<div id="div_notValidImage" class="important" style="display: inline"></div>
				</div>
				<div id="div_selected_image" style="display:none"> </div>
			</div>

			<br>

			<div id="div_landingPage">
				<label class="outer" for="input_landingPage">Landing Page Url : </label>
				<input  type="text" id="input_landingPage" name="landingPage" placeholder="http://www.indiaproperty.com/landing-page-url">
				<div id="div_notValidURL" class="important" style="display: inline"></div>
			</div>

			<br>

			<div id="div_selectDates">
				<div class="inline">
					<label class="outer" for="input_startDate">Start Date : </label>
					<input  class="" type="text" id="input_startDate" name="startDate" placeholder="dd-mm-yyyy">
				</div>
				<div class="space inline_block"></div>
				<div class="inline">
					<label class="outer" for="input_expireDate">Expire Date : </label>
					<input  class="" type="text" id="input_expireDate" name="expireDate" placeholder="dd-mm-yyyy">
				</div>
				
				<div id="div_notValidDates" class="important" style="display: inline"></div>
			</div>

			<br><br>

			<div id="div_GASkipText">
				<label class="outer" for="input_GASkipText">GA Code Skip Text: </label>
				<input  type="text" size="30" id="input_GASkipText" name="GASkipText" placeholder="City name not required">
			</div>

			<br><br>

			<input  class="button" type="button" value="Submit" id="submit_values">
				<div id="div_submitStatus" style="display: inline"></div>
		</form>

	</div>

<br>
	<div id='div_prevRecords'>
		<div id='div_prevRecords_loading'>
			<img src='jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif'><br><br>Loading...
		</div>

		<div id="div_prevRecords_pagination">
			<div onClick="get_previous_transactions();" id="buttonResetRecords">Refresh</div>
			<div id="pageNums"></div>
		</div>
	
		<div class='prevRecords' id='div_prevRecords_container'>
			<div class="prevRecords_head" id="div_prevRecords_head">	<!-- table head -->
				<div class="head_block" data-colType='updateTime'>Update Time</div>
				<div class="head_block" data-colType='image'>RoadBlock Image</div>
				<div class="head_block" data-colType='city'>City</div>
				<div class="head_block" data-colType='landingPageURL'>Landing Page</div>
				<div class="head_block" data-colType='GASkipText'>GA Skip Text</div>
				<div class="head_block" data-colType='startDate'>Start Date</div>
				<div class="head_block" data-colType='expireDate'>Expire Date</div>
				<div class="head_block" data-colType='searchCity'>
					<input  class="input_targetLocation" name="city" data-for="city" id="input_searchRecord" size='18' placeholder="Search for a city here">
				</div>
			</div>
			<div id="prevRecords_searchResult"> <span>Search Reults</span>
				<div class="clearButton" onClick="clear_search()">Clear Search Result</div>
				<div id='prevRecords_searchResult_result'></div>

			</div>
			<div class='prevRecords_body' id='div_prevRecords_body'></div>		<!-- table body -->
		</div>
	</div>	<!-- List of already entered transactions -->

</div>	<!-- end of inner-container  -->

<!-- /*====================================================
PAGE CONTENT ENDS
======================================================*/-->


<script src="jquery-ui-1.11.0.custom/external/jquery/jquery.js"></script>
<script src="jquery-ui-1.11.0.custom/jquery-ui.min.js"></script>

<script type="text/javascript">
j10_2 = $.noConflict(true);		// holds jQuery 1.10.2

/*====================================================
GLOBAL VARIABLES
======================================================*/
var flagFileValidated            = '';
var flagSubmitReady              = 0;
var flagToggleBusyOverlay        = 1;	// 0 : hidden, 1 : visible 
var flagMultipleLocations        = 0;
var flagPreviousRecordsPopulated = 0;

var currentUserID                = <?php echo $currentUserID ?>;		// current user handling the transactions

var domain                    = "http://www.indiaproperty.com/";
var adminFolderPath           = domain+"admin/";
var roadBlockFolder           = "roadBlock";
var roadBlockFolderPath       = adminFolderPath+roadBlockFolder+"/";
var moduleBackendFolder       = "backend";
var moduleBackendFolderPath   = roadBlockFolderPath+moduleBackendFolder+"/";
var moduleFrontendFolder      = "frontend";
var moduleFrontendFolderPath  = roadBlockFolderPath+moduleFrontendFolder+"/";

var recordImageFolder         = "RB_images";
var recordImageInactiveFolder = "inactive";

var selectedCities     = [];
var fetchedRecordsList = [];

var noOfRecords = 0;	// <- may not be necessary
var limit       = 15;

var divOverlayLoadingHTML = "<div id='div_overlay_text'><img src='jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif'><br><br>Loading...</div>";
var divOverlaySubmitNotReadyHTML = "<div id='div_overlay_text'>&lt;&nbsp;&nbsp;Some of the fields seem to be empty. Have a look again?</div>";
var initialStateInfoText = "<span class=\"note\">Search for a state to populate the list of cities for that state. Or you could also just start searching for a city directly. </span>";
var divPreviousRecordsLoadingHTML = "<img src='jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif'><br><br>Loading...";
var submitNotReadyHTML = "<span id='span_submitNotReady' class=''>&nbsp;! Form not ready to be submitted. Please check all the fields again?</span>"

</script>

<script src="frontend/formFunctions.js"></script>
<script src="frontend/historicalDataFunctions.js"></script>

<script>
/*

|~\ _  _    _ _  _  _ _|_  |  _  _  _| _  _|
|_/(_)(_|_|| | |(/_| | |   |_(_)(_|(_|(/_(_|
																								 
*/

document.addEventListener('DOMContentLoaded', function() {	
// not using .ready() to avoid conflict, revert if functionality fails

j10_2("#div_targetLocation_info").html(initialStateInfoText);

/*---
jQuery initializers
---*/

	j10_2(".input_targetLocation").each(function() {		// fill the selectboxes
		var inputFor = j10_2(this).attr('data-for');
		var idOfThis = "#"+j10_2(this).attr('id');
		var arrayReturnVals=[];				
		var ACminLength = 3;
		var ACdelay = 750;
		var IDOfResponseItem = '';
		var textboxType = null;

		if(j10_2(this).attr('id') == 'input_searchRecord') {
			textboxType = 2;
		}
		else {
			textboxType = 1;
		}

		/*---
		AUTOCOMPETE LOGIC STARTS HERE  // ! Everything here will be converted to objects once the initial version is pushed
		---*/
		j10_2(this).autocomplete({		
			minLength: ACminLength,
			delay: ACdelay,

			// // GET LIST OF CITIES/STATES
			source: function(uiReq,uiRes) {

				j10_2.ajax({
					url: moduleBackendFolderPath+"action.send_targetLocation.php?term="+uiReq.term+"&requestedBy="+inputFor,
					type : 'GET',
					dataType : 'json',

					error : function(jqXHO) {
						j10_2(idOfThis).removeClass("ui-autocomplete-loading");	// get rid of loading circle
						
						if(textboxType == 1) {	// location search
							var errorText = "<span class=\"important\">Something went wrong. Could not connect to the server. Try again in sometime?</span>";
							j10_2("#div_targetLocation_info").fadeOut("fast",function() {
								j10_2(this).html(errorText).fadeIn("fast");
							});
						}
						else if(textboxType == 2) {					// record search

						}
						console.log(jqXHO);
					},

					success : function(ajaxRes) {
						arrayReturnVals = ajaxRes;
						j10_2(idOfThis).removeClass("ui-autocomplete-loading");	// get rid of loading circle
						
						if(textboxType == 1) {	// location search
							store_temp_list(inputFor, arrayReturnVals);		// <- just in case required later

							if(arrayReturnVals == 0 || arrayReturnVals === false) {
								j10_2(idOfThis).autocomplete("close");	// <- !Check once before testing
								store_temp_list(inputFor, arrayReturnVals);		// just in case required later
								var errorText = "<span class=\"important\">Could not get any results. Please refine your search further?</span>";
								j10_2("#div_targetLocation_info").fadeOut("fast",function() {
									j10_2(this).html(errorText).fadeIn("fast");
								});
								return;
							}
						}	
						else if(textboxType == 2) {					// record search
							if(arrayReturnVals == 0 || arrayReturnVals === false) {
								j10_2(idOfThis).autocomplete("close");	// <- !Check once before testing
								// do something if no values returned
								return;
							}	
						}
						uiRes(arrayReturnVals);	// print list of values
					},

					complete : function() {
						j10_2(idOfThis).removeClass("ui-autocomplete-loading");	// get rid of loading circle
					}
				});
			},	// SOURCE ends

			select: function( event, ui ) {
				IDOfResponseItem = ui.item.idOfThis;
				j10_2(idOfThis).attr("data-id", IDOfResponseItem);

				if(textboxType == 1) {		// for location search		
					if(inputFor == 'state') {	// IF STATE SELECTED
						j10_2.ajax({	// send request to the server to get the list of cities
							url: moduleBackendFolderPath+"action.send_cities_for_state.php?&stateID="+IDOfResponseItem,
							dataType : "json",

							success : function(citiesRecieved) {	// // create button for each individual city
								create_fetchedCityList(citiesRecieved);
							},	

							// DO IF_AJAX_FAIL FALLBACK HERE LATER <-				
						});
					}

					else if(inputFor == 'city') {	// IF CITY SELECTED
						add_city(IDOfResponseItem, ui.item.value);
					}
				}
				else if(textboxType == 2) {					// record search
					j10_2.ajax({	// get record list
						url: moduleBackendFolderPath+"action.get_previous_transactions.php",
						dataType : "json",
						type : "POST",
						data : {'cityID' : IDOfResponseItem},
						
						success : function(record) {
							if(record == null || record == 0) {
								j10_2("#prevRecords_searchResult_result").fadeOut("fast", function() {
									j10_2(this).html("<span>Sorry, no records for <span class='important'>"+ui.item.value+"</span> found. <br>You could create a new one by filling up the form above.</span>").fadeIn("fast");
								});
								j10_2("#prevRecords_searchResult").fadeIn("fast");
								return;
							}
							populate_search_record(record);
						}
					});	
				}
			},	// SELECT ends
		});


		j10_2(this).blur(function() {	// if value not in dropdown after entering
			if(textboxType == 1) {
				var valOfField = j10_2.trim(j10_2(this).val());

				if( !(valOfField == null || valOfField == '') && (check_val_in_objArr(valOfField, arrayReturnVals) == 0) ) {
					var errorText = "<span class=\"important\"> <span class='capitalize'> " + inputFor + "</span> not in list. Please select a <span class='capitalize'> " + inputFor + "</span> from the drop down list</span>";

					j10_2("#div_targetLocation_info").fadeOut("fast",function() {
						j10_2(this).html(errorText).fadeIn("fast");
					});

					j10_2(this).val('');
				}
			}
		}); 

		j10_2(this).click(function() {	// remove error message on clicking another textarea
			j10_2(this).val('');
			
			if(textboxType == 1) {
				if(j10_2("#div_targetLocation_info").html().trim() !== initialStateInfoText.trim()) {
					j10_2("#div_targetLocation_info").fadeOut("fast", function(){
						j10_2(this).html(initialStateInfoText).fadeIn("fast");
					});
				}
			}
			else if(textboxType == 2) {

			}
		});
	});

	/*---
	AUTOCOMPETE ENDS HERE
	---*/

	// <- Make these responsive later
	
	j10_2( "#input_expireDate" ).datepicker({			// jQuery-ui datepicker
		numberOfMonths: 3,
		dateFormat : "d-m-yy",
	});

	j10_2( "#input_startDate" ).datepicker({			// jQuery-ui datepicker
		numberOfMonths: 3,
		dateFormat : "d-m-yy",
	});


/*---*/


	toggle_busy();	// toggles overlay div. Getting rid of overlay once page loaded

	set_pagination_vars();	//creates history of records

	// // IMAGE VALIDATION
	j10_2("#input_uploadImage").change(function() {		
		var imageToCheck = this.files[0]; 

		// // check size
		if((imageToCheck.size/1024) > 80) {		// should be less than 80kb
			j10_2("#div_notValidImage").fadeIn().html("<span>&nbsp;! </span>File size exceeded. File must be less than 80Kb.");
			j10_2(this).addClass("error").click(function() {
				remove_errorClass(this);
				j10_2("#div_notValidImage").fadeOut("fast").html("");
			});
			this.value = null;
			return;
		}

		// // check file extensions
		if(!(imageToCheck.type.indexOf("image") == 0)) {
			j10_2("#div_notValidImage").fadeIn().html("<span>&nbsp;! </span>File is not an image.");
			j10_2(this).addClass("error").click(function() {
				remove_errorClass(this);
				j10_2("#div_notValidImage").fadeOut("fast").html("");
			});
			this.value = null;
			return;
		}	
	});
	// ends


	// // URL VALIDATION
	j10_2("#input_landingPage").change(function() {
		var urlPatt = /^http:\/\/www\.indiaproperty\.com\//; // just checks for http://www.indiaproperty.com right now. Make more specific as requirements come

		if(urlPatt.test(j10_2(this).val()) == false ) {
			j10_2("#div_notValidURL").fadeIn().html("<span>&nbsp;! </span>This is not a valid URL");
			j10_2(this).addClass("error").click(function() {
				remove_errorClass(this);
				j10_2("#div_notValidURL").fadeOut("fast").html("");
			});
			this.value = null;
			return;
		}
	});
	// ends


	// // DATE VALIDATION
	j10_2("#input_expireDate, #input_startDate").change(function() {		// check if dates are valid
		var startDateAsArray                       = j10_2("#input_startDate").val().split('-');
		var expireDateAsArray                      = j10_2("#input_expireDate").val().split('-');

		var startDate = Date.UTC( startDateAsArray[2], startDateAsArray[1]-1, startDateAsArray[0],0,0,0,0);  // month is 0 to 11 not 1 to 12
		var expireDate = Date.UTC( expireDateAsArray[2], expireDateAsArray[1]-1, expireDateAsArray[0],0,0,0,0);

		if(startDate > expireDate) {
			j10_2("#div_notValidDates").fadeIn().html("<span>&nbsp;! </span>Start Date cannot be greater than expiry date");
			
			j10_2(this).addClass("error").click(function() {
				remove_errorClass(this);
				j10_2("#div_notValidDates").fadeOut("fast").html("");
			});
			this.value = null;
			return;
		}
	});
	// ends

	// SUBMIT FORM
	j10_2("#submit_values").click(submit_form);

});

</script>

<!--

 |`    _  __|_. _  _  _ |._|_    _  _  _| _  |_  _  _ _ 
~|~|_|| |(_ | |(_)| |(_||| |\/  (/_| |(_|_\  | |(/_| (/_
							/                           
-->


<?php
// Footer
include_once $DOCROOTPATH."/template/admin-footer.php";
?>