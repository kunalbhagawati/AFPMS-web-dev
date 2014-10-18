/*====================================================
FUNCTION DEFINATIONS FOR FORM
======================================================*/

// toggles the loading/busy overlay
function toggle_busy() {	
	if(flagToggleBusyOverlay == 0) {	
		j10_2("#div_overlay").fadeIn("fast");	
		flagToggleBusyOverlay = 1;
	}
	else {
		j10_2("#div_overlay").fadeOut("fast");
		flagToggleBusyOverlay = 0;      
	}
}


// check if value exists in an object
function check_val_in_objArr(needle, haystackObj) {
	var flagExists = 0;

	j10_2.each(haystackObj, function(prop, val) {
		if(val.value == needle) {
			flagExists = 1; 
		}
	});
	return flagExists;
}


/**
 * Stores retrieved list for target location autocomplete as arrays
 * 
 * @param 		{inputFor} 			inputFor 			city, state
 *              {arrayReturnVals} 	arrayReturnVals 	array of records. [{obj1}, {obj2}, ...]
 */
function store_temp_list(inputFor, arrayReturnVals) {
	if(inputFor=='state') {
		tempStateArray = arrayReturnVals;
	}
	else if(inputFor=='city') {
		tempCityArray = arrayReturnVals;
	}
}


/**
 * Create list of cities after selecting a state
 * @param	{object}		citiesRecieved 		object as list of cities
 */
function create_fetchedCityList(citiesRecieved){
	j10_2("#div_targetLocation_fetchedCities").fadeIn("fast");	

	var noOfCities = Object.keys(citiesRecieved).length;
	var citiesPerBlock = Math.ceil(noOfCities/4);
	var cityCounter = 0;
	var blockCounter = 1;
	var newCityButtonHTML = "";

	j10_2(".div_targetLocation_fetchedCities_cols").html("");
	j10_2.each(citiesRecieved, function(prop, val){
		cityCounter++;
		newCityButtonHTML = "<div class='cityButton' data-cityID='"+val.idOfThis+"' data-cityName='"+val.value+"' onClick='remove_cityAddButton(this)'>"+val.value+"</div>";

		j10_2("#div_targetLocation_fetchedCities_col"+blockCounter).append(newCityButtonHTML);

		if(cityCounter == citiesPerBlock) {
			blockCounter++;
			cityCounter = 0;
		}
	});
}


// Clear list of cities for each state
function clear_state_list() {		// <- ! Not tested fully. Works fine
	j10_2(".div_targetLocation_fetchedCities_cols").each(function() {
		j10_2(this).fadeOut("fast", function() {
			j10_2(this).html('').fadeIn();
		});
	});

	j10_2("#div_targetLocation_fetchedCities").fadeOut("fast");
}


// Remove button after adding city
function remove_cityAddButton(cityButton) {		// <-
	var cityID = j10_2(cityButton).attr('data-cityID');
	var cityName = j10_2(cityButton).attr('data-cityName');

	add_city(cityID, cityName);

	j10_2(cityButton).fadeOut("fast",function(){
		j10_2(this).remove();
	});
}


// remove added city and its button 
function remove_city(cityObj) {		// <-
	var cityID = j10_2(cityObj).attr('data-cityID');
	j10_2(cityObj).fadeOut("medium",function(){
		j10_2(this).remove();
	});

	for(var i in selectedCities) {
		if(cityID == selectedCities[i]) {
			selectedCities.splice(i, 1);
		}
	}

	if(selectedCities.length == 0) {
		j10_2("#div_targetLocation_selectedCities").fadeOut("fast");	
	}
}


// make a new button for each city
function add_city(cityID, cityValue) {
	
	j10_2("#div_targetLocation_selectedCities").fadeIn("fast");	

	if(selectedCities.indexOf(cityID) == -1) {	// if city already in list
		selectedCities.push(cityID);
		var buttonHTML = "<div class='selected' data-cityID="+cityID+"><span>" + cityValue + "</span><span class='closeButton' onClick='remove_city(this.parentNode);'>x</span></div>";

		j10_2(buttonHTML).hide().appendTo("#div_targetLocation_selectedCities").fadeIn("slow");
	}
}


// check if a feild in the form is empty
function check_field_empty(idOfField) {
	return (j10_2.trim(j10_2(idOfField).val()) == '') || (j10_2(idOfField).val() == null);
}


// REMOVE '.error' class
function remove_errorClass(id) {
	var idOfThis = "#"+j10_2(id).attr('id');

	if(idOfThis == "#input_city" || idOfThis == "#input_state") 
		j10_2("#input_state, #input_city").removeClass("error");
	else if(idOfThis == "#input_startDate" || idOfThis == "#input_expireDate") 
		j10_2("#input_startDate, #input_expireDate").removeClass("error");
	else 
		j10_2(idOfThis).removeClass("error");
}


// Smooth scroll funtion
function go_to_by_scroll(id) {
	var winH = j10_2(window).height();
	var offsetDiff = (winH-(0.55*winH));

    j10_2('html,body').animate({
        scrollTop: j10_2(id).offset().top-offsetDiff,
    },
    800);
}


/**
 * function to submit the form to the backend
 * 
 * @recieved 		{[multiple]}		resValues 		0 : transaction fail / could not update
 *             											array : array of objects in the form of 
 *             												[{record1}, {record2}, ...]
 */
function submit_form() {
	j10_2("#div_submitStatus").removeClass("important").html("<div><img src='jquery-ui-1.11.0.custom/images/ui-anim_basic_16x16.gif'> Submitting Values...</div>");
	flagSubmitReady                            = 1;	// give green light to form 
	
	/**
	 * Array to hold empty fields in the format 
	 * 			['flagCitiesEmpty', 'inputID1', 'inputID2', ...] where flagCitiesEmpty means no city selected
	 */
	var emptyFieldsArray                       = [];
	
	if(selectedCities.length                   == 0) emptyFieldsArray.push("flagCitiesEmpty");
	if(check_field_empty("#input_uploadImage") == 1) emptyFieldsArray.push("#input_uploadImage");
	if(check_field_empty("#input_startDate")   == 1) emptyFieldsArray.push("#input_startDate");
	if(check_field_empty("#input_expireDate")  == 1) emptyFieldsArray.push("#input_expireDate");
	if(check_field_empty("#input_GASkipText")  == 1) emptyFieldsArray.push("#input_GASkipText");

	
	if(emptyFieldsArray.length > 0) {	// check if any field is empty
		flagSubmitReady = 0;

		j10_2.each(emptyFieldsArray, function(val, inputbox) {
			if(inputbox == "flagCitiesEmpty") {
				j10_2("#input_state, #input_city").addClass("error").click(function() {remove_errorClass(this);});
    			go_to_by_scroll('#input_state');           
    		}
			else {
				j10_2(inputbox).addClass("error").click(function() {remove_errorClass(this);});;
    			go_to_by_scroll(inputbox);           
			}

			j10_2("#div_overlay").html(divOverlaySubmitNotReadyHTML).fadeIn("fast").click(function() {
				j10_2(this).fadeOut("fast");
			});

			j10_2("#div_submitStatus").html("");
			return; // return to main script 
		});
	}

	// // submit
	if(flagSubmitReady == 0) {	// <-
		return;
	}
	else {
		if(check_field_empty("#input_landingPage") == 1) {
			j10_2("#input_landingPage").val('0');	
		}
		var formToSend = new FormData(j10_2('#form_textValues')[0]);

		formToSend.append('citiesListAsString', selectedCities);
		formToSend.append('currentUserID', currentUserID);

		j10_2.ajax({
			url: moduleBackendFolderPath+"action.recieve_form.php",
			type: "POST",
			data: formToSend,
			processData: false,
			contentType: false,
			dataType : "json",

			error : function(jqXHO) {
				// console.log(jqXHO);
			},

			success : function(resValues) {
				if(resValues.hasOwnProperty("err")) {	// in case of error
					console.log(resValues.err);

					j10_2("#div_submitStatus").addClass("important").html("<span>&nbsp;! <span>Sorry, something went wrong. Please contact the technical team for debugging.");

					return;
				}
				else {
					console.log("Submit/success");
					j10_2("#div_submitStatus").addClass("note").html("Cities are updated now.");
					set_pagination_vars();
				}
			},
		});
	}
}