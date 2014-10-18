/*====================================================
FUNCTIONS FOR DISPLAYING/MANIPULATING PREVIOUS RECORDS 
======================================================*/


function set_pagination_vars() {
	j10_2.when(j10_2.ajax({	// get count of records
		url: moduleBackendFolderPath+"action.get_count_transactions.php",
		dataType : 'json',
	})).then(
		function(count) {	// request successful
			if(count.hasOwnProperty("err")) {	// in case of error
				// <- point of failure for both submission of values and also page reload. Do user notification here
				console.log(count.err);
				return;
			}

			if(count == 0) {
				j10_2("#div_prevRecords_loading").fadeOut("fast", function(){
					var html = "<div id='div_prevRecords_error'>No previous transactions exist</div>";
					j10_2(html).hide().appendTo("#div_prevRecords").fadeIn("fast");
				});
				return;
			}
			noOfRecords = count;
			create_pageNo_block(count);
			get_previous_transactions(1);
		}, 
		function(jqXHR) {
			console.log(jqXHR);
		}
	);
}


function create_pageNo_block(count) {
	j10_2("#pageNums").fadeOut("fast").empty();
	var noOfPages = Math.ceil(count/limit);
	var i         = 1;

	for(i=1; i<=noOfPages; i++) {
		// create record block
		var pageNumBlockHTML = '';
		pageNumBlockHTML += "<div onClick='get_previous_transactions("+i+");' data-pageNum='"+i+"'>"+i+"</div>";
		j10_2("#pageNums").append(pageNumBlockHTML).fadeIn("fast");
	}
}


/**
 * Creates an individual record
 * @param 		{object}		record 			object of the form:
 * 
 * @return 		{string} 		recordHTML 		created html of the record		
 */
function create_record_html(record,updateMode) {
	var recordHTML = "";
	recordHTML = "<div data-cityID='"+record['CityId']+"' data-cityName='"+record['CityName']+"' data-celltype='root' class='record_rootBlock'>";
	recordHTML += "<div data-celltype='uploadedTime' class='record_block'>"+record['UploadedTime']+"</div>";

	recordHTML += "<div data-celltype='image' class='record_block'><img onClick='open_image_fullsize(this);' alt='"+record['ImagePath']+"' src='"+record['ImagePath']+"'></div>"; // <- remove data-imagepath

	recordHTML += "<div data-celltype='city' data-cell-cityid='"+record['CityId']+"' class='record_block'>"+record['CityName']+"</div>";

	recordHTML += "<div data-celltype='landingPageURL' class='record_block'>"+record['LandingPageURL']+"</div>";
	recordHTML += "<div data-celltype='GASkipText' class='record_block'>"+record['GASkipText']+"</div>";
	recordHTML += "<div data-celltype='startDate' class='record_block'>"+record['StartDate']+"</div>";
	recordHTML += "<div data-celltype='expireDate' class='record_block'>"+record['ExpireDate']+"</div>";
	// recordHTML += "<div data-celltype='editButton' class='record_block'> <input type='button' class='button' value='Edit'> </div>";

	if(record['Status']==1) {
		var buttonValue = "Active";
	}
	else if(record['Status']==0) {
		var buttonValue = "Inactive";
	}
	recordHTML += "<div data-celltype='changeStatusButton' data-status='"+record['Status']+"' class='record_block'> <input data-status='"+record['Status']+"' type='button' class='button' onClick='change_record_status(this);' value='"+buttonValue+"'> </div>";

	recordHTML += "</div>";
	return recordHTML;
}


/**
 * Updates the div that holds the records
 * 
 * @param 	{array of objects} 		records 		records in the form of [{record1}, {record2}, ...]
 *          {number} 				type 			1 : new. clear records and update new
 *                              					2 : update. Push new records to the top. Don't clear
 *          {number} 				pageNo 			page number in pagination
 */
function populate_records_list(records, type) {		// update/fill div_prevRecords
	if(type == 1) {
		j10_2("#div_prevRecords_body").html('');
	}
	var htmlToAppend = "";

	j10_2.each(records, function(no, record) {		// create the html
		htmlToAppend += create_record_html(record,type);
	});
	j10_2("#div_prevRecords_loading").fadeOut("fast");
	j10_2("#div_prevRecords_container").fadeIn("fast", function(){
		j10_2(htmlToAppend).hide().prependTo("#div_prevRecords_body").fadeIn("slow");
	});

}

function populate_search_record(record) {
	var htmlToAppend = "";
	htmlToAppend += create_record_html(record[0]);
	j10_2("#prevRecords_searchResult_result").fadeOut("fast", function() {
		j10_2(this).html(htmlToAppend).fadeIn("fast");
	});
	j10_2("#prevRecords_searchResult").fadeIn("medium");
}

function clear_search() {
	j10_2("#prevRecords_searchResult").fadeOut("fast", function() {
		j10_2("#prevRecords_searchResult_result").html('');
	});
}

function open_image_fullsize(image) {
	var imgURL = j10_2(image).attr("src");

	var winW = j10_2(window).width();
	var winH = j10_2(window).height();
	
	var imgRealWidth, imgRealHeight;

	var tmpImg = j10_2("<img/>") // Make in memory copy of image 
	    .attr("src", imgURL)
	    .load(function() {
	        imgRealWidth = this.width;
	        imgRealHeight = this.height; 
        	j10_2(this).attr("height", imgRealHeight);
        	j10_2(this).attr("width", imgRealWidth);
        	j10_2(tmpImg).css({
        		"top" : ((winH-imgRealHeight)/2),
        		"left": ((winW-imgRealWidth)/2), 
        		'position' : 'fixed',
        		'cursor' : 'pointer',
        		'z-index' : 10,
        	});

        	outerhtml = j10_2('<div>').append(j10_2(this).clone()).html();
			overlayHTML = "<div><div id='div_imgOverlay'></div>"+outerhtml+"</div>";

			j10_2(overlayHTML).hide().appendTo("#container").fadeIn("fast").click(function(){
				j10_2(this).fadeOut("fast", function(){
					console.log(this);
					j10_2(this).remove();
				});
			});
	    });
}


/**
 * Change the status of the record from active to inactive and vice versa
 * 
 * @param 	inputButton 		the input button that contains the status
 */
function change_record_status(inputButton) {
	var status = j10_2(inputButton).attr('data-status');
	var cityID = j10_2(inputButton).parents(".record_rootBlock").attr('data-cityID');
	var cityName = j10_2(inputButton).parents(".record_rootBlock").attr('data-cityName');
	var updateVals = {'status' : status, 'cityID' : cityID, 'cityName' : cityName};
	var updateStatus = {};

	updateStatus = update_record(updateVals, 1);		// update the record
}


function change_record_button(cityID, status) {
	var recordParentBlock = j10_2(".record_rootBlock[data-cityid="+cityID+"]");
	var imageDiv          = j10_2(recordParentBlock).find("div[data-celltype=image]");
	var statusButtonDiv   = j10_2(recordParentBlock).find("div[data-celltype=changeStatusButton]")

	if(status == 1) {
		var newStatus         = 0;
		var newButtonValue    = "Inactive";
	}
	else if(status==0) {
		var newStatus         = 1;
		var newButtonValue    = "Active";
	}

	j10_2(statusButtonDiv).attr('data-status',newStatus);
	j10_2(statusButtonDiv).find("input").attr('data-status',newStatus);
	j10_2(statusButtonDiv).find("input").attr('value',newButtonValue);
}


/**
 * Common call for both edit_fields() and change_record_status() to update the transaction in the db and folders
 * 
 * @params	 	{object}		updateVals 					values to be sent to the backend
 *           	{number}		updateType					1 : update Status from active to inactive and vice versa
 *           												2 : update the whole transaction with new values
 *
 * @return 		{boolean}		0 : fail
 *                            	1 : success
 */
function update_record(updateVals, updateType) {
	updateVals.updateType = updateType;

	// send the data to the server asking it to update it
	j10_2.ajax({
		url: moduleBackendFolderPath+"action.update_transaction.php",
		type : 'POST',
		dataType : 'json',
		data : updateVals,

		error : function(jqXHR) {

		},

		success : function(response) {
			if(updateType == 1) {		// status changed
				if(response.hasOwnProperty("err")) {	// error from server
					console.log(response.err);
					return;
				}

				change_record_button(updateVals.cityID, updateVals.status);
			}
		},

		complete : function() {
		},
	}); 
}


function get_previous_transactions(pageNo) {
	if(typeof(pageNo) === 'undefined' || !pageNo) {
		pageNo = 1;
	}

	j10_2.ajax({	// get records
		url: moduleBackendFolderPath+"action.get_previous_transactions.php",
		dataType : "json",
		type : "POST",
		data : {'pageNo' : pageNo},

		error : function(jqXHR, error) {
			console.log(error);
		},

		success : function(recordsRecieved) {
			if(recordsRecieved.hasOwnProperty("err")) {	// in case of error
				console.log(recordsRecieved.err);
				// <- more error handling here
				return;
			}
			fetchedRecordsList = recordsRecieved; 
			populate_records_list(recordsRecieved, 1);	// <- use res
		},
	});
}