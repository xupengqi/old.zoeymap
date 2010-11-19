/*****************************************************
 * item.js
 *****************************************************/
function update_markers()
{
	$("#bounds").empty();
	$("#bounds").append(zm_map.map.getBounds().getNorthEast()+"<br />"+zm_map.map.getBounds().getSouthWest());

    $.post("item.php", {
    	action: "get_locations",
    	lat_max: zm_map.map.getBounds().getNorthEast().lat(),
    	lng_max: zm_map.map.getBounds().getNorthEast().lng(),
    	lat_min: zm_map.map.getBounds().getSouthWest().lat(),
    	lng_min: zm_map.map.getBounds().getSouthWest().lng()
    },
	function(data) {
    	var markers = eval(data);
    	setMarkers(markers);
    	$("#info").empty();
    	for (var i = 0; i < markers.length; i++) {
    		$("#info").append("<div class='player'>"+markers[i][2]+"</div>");
    	}
    	$("#number_of_items").text(markers.length);
    });
}

function setMarkers(items)
{
	for(var i=0; i<map_markers.length; i++){
		map_markers[i].setMap(null);
	}
	map_markers = new Array();
	
	for (var i = 0; i < items.length; i++) {
		var item = items[i];
		var latlng = new google.maps.LatLng(item[0], item[1]);
		map_markers[map_markers.length] = new google.maps.Marker({
			position: latlng,
			map: zm_map.map,
			title: item[2]
		});
	}
}

function placeMarker(location) {
	var clickedLocation = new google.maps.LatLng(location);
	if(map_markers_add != null) {
		map_markers_add.setMap(null);
	}
	
	map_markers_add = new google.maps.Marker({
		position: location, 
		map: zm_map.map
	});


	$("form.solo").hide();
	$("#add_location").show();
	$("#lat").text(location.lat());
	$("#lng").text(location.lng());

	geocoder.geocode({'latLng': location}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (results[1]) {
				$("#address").text(results[1].formatted_address);
			} else {
				$("#address").text("No results found");
			}
		} else {
			$("#address").text("Geocoder failed due to: " + status);
		}
	});
	
//	map.setCenter(location);
//	alert(map_markers_add.position);
}

/************************ above not deferable ************/

//$(document).ready(function() {
//	$(".race").click(function () {
//		map_marker_change($(this).val());
//	});
//
//});


//function map_marker_change(race)
//{
//	map_markers_add_path = "image/"+race+".png";
//	map_markers_add.setIcon(map_markers_add_path);
//	map_markers_add.setMap(map);
//}


function add_location()
{
//	var fail = false;
//
//	$("#name_combo .status").text('');
//	$("#code_combo .status").text('');
//	
//	if($("#name").val() == "") {
//		$("#name_combo .status").text('Required');
//		fail = true;
//	}
//	if($("#code").val() == "") {
//		$("#code_combo .status").text('Required');
//		fail = true;
//	}
//	
//	if(fail) {
//		return false;
//	}
	
    $.post("item.php", {
    	action: "new_location",
    	lat: $("#lat").text(),
    	lng: $("#lng").text(),
    	zoom: zm_map.map.getZoom()
    	},
        function(data) {
    		update_markers();
    });

	map_markers_add.setMap(null);
	map_markers_add = null;
	$("#add_location").hide();
	return false;
}