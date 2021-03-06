// Store the inventory as an array of JavaScript objects
var inventory = [];

// Keep a counter to generate unique identifiers for the items
var nextId = 1;

// This is the event we'll catch for clicks or touches. Depending on what kind of device
// this application is running, it will either be "mousedown" or "touchstart".
var buttonEvent;

// This function is called when the page has finished loaded and is our main point of entry
$(document).ready(function() {
	// Determine if this device supports touch events, otherwise use mouse events
	if (typeof(document.ontouchstart)!="undefined")
		buttonEvent = "touchstart";
	else
		buttonEvent = "mousedown";
	
	// Create templates that the jQuery plugin will need later
	$.template("itemTemplate", $("#itemTemplate"));
	$.template("askValueTemplate", $("#askValueTemplate"));
	
	// Open the inventory contents from storage
	openInventory();
	
	// If no data is found, put some sample data into the inventory to better explain
	// the first interface that the user sees.
	if (inventory.length == 0) {
		inventory.push({ id:1, name:"Table", count:18 });
		inventory.push({ id:2, name:"Chair", count:7 });
		inventory.push({ id:3, name:"Bookshelf", count:3 });
		nextId = 4;
		$("h6#msg").text("The inventory has been filled with three sample items to ease the testing/demoing.").css("display", "");
	}
	else {
		// Find the highest id in the inventory and add one to that. That will be the nextId.
		for (var pos = 0; pos < inventory.length; pos++) {
			if (inventory[pos].id > nextId)
				nextId = inventory[pos].id;
		}
		nextId++;
	}
	
	// Use template and output all items in the web page
	$.tmpl("itemTemplate", inventory).appendTo("#list").find("ol").bind(buttonEvent, onItem);
	
	// Catch button events
	$("li#reset").bind(buttonEvent, onReset);
	$("li#deleteAll").bind(buttonEvent, onDeleteAll);
	$("li#add").bind(buttonEvent, onAdd);
	
	// Catch resize event to keep any showing dialog box in the center of the screen (desktop only)
	$(window).resize(function() {
		if (currentDialog) {
			centerElement(currentDialog);
		}
	});
});

// This function is called when the user clicks/touches anywhere inside an item row (the OL element)
function onItem(ev) {
	// Event object "ev" properties:
	// ev.currentTarget is the element that we called .bind() for, which is always the OL element for the row
	// ev.target is the element that actually caused the event to happen, most likely the A element inside
	// the LI element, which are all contained inside the OL element
	// <ol><li><a>xyz</a></li></ol>
	
	// We need to find the id of the currently clicked item row. The id is set for the OL element,
	// but has the "item" prefix so we need to extract everything after the first 4 characters.
	var id = ev.currentTarget.id.substring(4);
	
	// Next, find the class for the parent LI item, which is our only way of separating the different columns
	var className = $(ev.target).parent("LI").attr("class");
	
	// Call the function for the column the user clicked
	if (className=="countcolumn")
		onCount(id);
	else if (className=="namecolumn")
		onName(id);
	else if (className=="incpackcolumn")
		onIncreasePack(id);
	else if (className=="inccolumn")
		onIncrease(id);

	// Always prevent default handling of the event		
	ev.preventDefault();
}

// Search array of inventory for an item with specified id
function getItem(id) {
	for (var pos = 0; pos < inventory.length; pos++) {
		if (inventory[pos].id == id)
			return inventory[pos];
	}
	return null;
}

// Open inventory from local storage and put in global variable
function openInventory() {
	if (window.localStorage) {
		inventory = eval(window.localStorage.getItem("inventory"));
	}
	if (inventory==null)
		inventory = [];
}

// Save global inventory variable to local storage, converting it to JSON string
function saveInventory() {
	if (window.localStorage) {
		window.localStorage.setItem("inventory", $.toJSON(inventory));
	}
}

// This function is called when Add button is clicked
function onAdd(ev) {
	ev.preventDefault();
	askValue("Name of new item", "", function(value) {
		// Create new item object and set startup values
		var item = {};
		item.id = nextId;
		item.name = value;
		item.count = 0;
		nextId++;
		
		// Add new item object to inventory array
		inventory.push(item);
		
		// Output new item into web page
		$.tmpl("itemTemplate", item).appendTo("#list").find("ol").bind(buttonEvent, onItem);
		
		// Store the changed inventory to local storage
		saveInventory();
	});
}

// This function is called when the Delete All button is clicked
function onDeleteAll(ev) {
	ev.preventDefault();
	if (confirm("Are you sure you want to clear and delete all items?")) {
		// Set global inventory variable to empty array
		inventory = [];
		
		// Remove all items from the web page
		$("#list").empty();
		
		// Store the changed inventory to local storage
		saveInventory();
	}
}

// This function is called when the Reset button is clicked
function onReset(ev) {
	ev.preventDefault();
	if (confirm("Are you sure you want to reset the counter to zero for all items?")) {
		// Go though all items in inventory and set each count to zero
		for (var pos = 0; pos < inventory.length; pos++) {
			inventory[pos].count = 0;
		}
		
		// Regenerate the html by removing all items from the web page and then outputting them again
		$("#list").empty();
		$.tmpl("itemTemplate", inventory).appendTo("#list").find("ol").bind(buttonEvent, onItem);
		
		// Store the changed inventory to local storage
		saveInventory();
	}
}

// This function is called when the counter box (leftmost on item row) is clicked
function onCount(id) {
	// Find the item that the user clicked
	var item = getItem(id);
	askValue(item.name, item.count, function(value) {
		// Set item count to whatever the user typed in the dialog box
		item.count = parseInt(value);
		
		// Update the count value in the box on the web page
		$("#item" + id + " li:nth-child(1) a").text(item.count);
		
		// Store the changed inventory to local storage
		saveInventory();
	});
}

// This function is called when the name of the item is clicked
function onName(id) {
	// Find the item that the user clicked
	var item = getItem(id);
	askValue(item.name, item.name, function(value) {
		// Set item name to whatever the user typed in the dialog box
		item.name = value;
		
		// Update the name value in the box on the web page
		$("#item" + id + " li:nth-child(2) a").text(item.name);
		
		// Store the changed inventory to local storage
		saveInventory();
	});
}

// This function is called when the increase button with the higher value is clicked
function onIncreasePack(id) {
	// Find the item that the user clicked
	var item = getItem(id);
	
	// Add a dozen to the count
	item.count += 10;
	
	// Update the count value in the box on the web page
	$("#item" + id + " li:nth-child(1) a").text(item.count);
	
	// Store the changed inventory to local storage
	saveInventory();
}

// This function is called when the increase button is clicked
function onIncrease(id) {
	// Find the item that the user clicked
	var item = getItem(id);
	
	// Increase the count by one
	item.count++;
	
	// Update the count value in the box on the web page
	$("#item" + id + " li:nth-child(1) a").text(item.count);

	// Store the changed inventory to local storage
	saveInventory();
}

// Remember if a dialog box is showing on the page
var currentDialog = null;

// Call this function to show a dialog box and ask the user for a single value
function askValue(label, value, whenOk) {
	// Use templating enginge to put label and value in html for the dialog box
	var data = new Object();
	data.title = label;
	data.value = value;
	var dlg = $.tmpl("askValueTemplate", data).appendTo("body").hide();
	centerElement(dlg);
	
	// Make sure the dialog box is closed (removed) when a button is clicked
	dlg.find("input[type='button']").click(function(event) {
		// Only do this animation on non-touch devices
		if (buttonEvent=="mousedown")
			dlg.fadeOut("slow");
		else
			dlg.hide();
		
		// Check if OK button was clicked to close the dialog box
		if (event.target.value=="OK") {
			// Retrieve the value that the user typed in the dialog box field
			value = dlg.find("input[type='text']").val();
			
			// Call supplied function to tell caller that the dialog box is finished with a new value
			whenOk(value);
		}
		
		// Make sure all a-tags work again
		$("a").unbind("click");
		
		// No dialog box shown anymore on the page
		currentDialog = null;
	});
	
	// Show dialog box and set focus to the text field. Only do the animation on non-touch devices
	if (buttonEvent=="mousedown")
		dlg.fadeIn();
	else	
		dlg.show();
	dlg.find("input[type='text']").focus();
	
	// Remember that a dialog box is showing
	currentDialog = dlg;
	
	// Make sure all a-tags do NOT work during the display of the dialog box
	$("a").click(function() { return false; });
}

// Call this function to center the specified element in the browser window
function centerElement(element) {
	var left = (document.documentElement.clientWidth - element.outerWidth()) / 2;
	var top = (document.documentElement.clientHeight - element.outerHeight()) / 2;
	element.css("position", "absolute").css("left", left+"px").css("top", top+"px");
}
