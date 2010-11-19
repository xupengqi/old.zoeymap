/*****************************************************
 * broadcast.js
 *****************************************************/
var zm_broadcast = new function() {
	this.show_polygons = false;
	
	this.new_polygons = function (broadcasts) {
		for(var i=0; i<map_broadcasts.length; i++){
			map_broadcasts[i].setMap(null);
		}
		map_broadcasts = new Array();
		
		for (var i = 0; i < broadcasts.length; i++) {
			var item = broadcasts[i];
		    var coords = [
		                  new google.maps.LatLng(item[0], item[1]),
		                  new google.maps.LatLng(item[2], item[1]),
		                  new google.maps.LatLng(item[2], item[3]),
		                  new google.maps.LatLng(item[0], item[3])
		                      ];
		    map_broadcasts[map_broadcasts.length] = new google.maps.Polygon({
	            paths: coords,
	            strokeColor: "#FF0000",
	            strokeOpacity: 0.8,
	            strokeWeight: 1,
	            fillColor: "#000000",
	            fillOpacity: 0.2,
	            map: map
			});
		}
	};
};

function update_broadcasts()
{
	$("#northeast").text(zm_map.map.getBounds().getNorthEast().toString());
	$("#southwest").text(zm_map.map.getBounds().getSouthWest().toString());

    $.post("broadcast.php", {
    	action: "get_broadcasts",
    	lat_max: zm_map.map.getBounds().getNorthEast().lat(),
    	lng_max: zm_map.map.getBounds().getNorthEast().lng(),
    	lat_min: zm_map.map.getBounds().getSouthWest().lat(),
    	lng_min: zm_map.map.getBounds().getSouthWest().lng()
    },
	function(data) {
    	var broadcasts = eval(data);
    	if(zm_broadcast.show_polygons) {
    		zm_broadcast.new_polygons(broadcasts);
    	}
    	$("#broadcasts").empty();
    	for (var i = 0; i < broadcasts.length; i++) {
    		$("#broadcasts").append("<div class='broadcast'><a href='#' onclick='return get_broadcast("+broadcasts[i][5]+", \""+broadcasts[i][4]+"\");'>"+broadcasts[i][4]+"</a></div>");
    	}
    	$("#number_of_broadcasts").text(broadcasts.length);
    });
    
    cookie_set('view', "["+zm_map.map.getCenter().lat()+","+zm_map.map.getCenter().lng()+","+zm_map.map.getZoom()+"]", 30);
}

/************************ above not deferable ************/

function new_broadcast()
{
	$("form.solo").hide();
	$("#add_broadcast").show();
}

function add_broadcast()
{
	var fail = false;

	$("#broadcast_title_combo .status").text('');
	
	if($("#title").val() == "") {
		$("#broadcast_title_combo .status").text('Required');
		fail = true;
	}
	
	if(fail) {
		return false;
	}
	
    $.post("broadcast.php", {
    	action: "new_broadcast",
    	nelat: zm_map.map.getBounds().getNorthEast().lat(),
    	nelng: zm_map.map.getBounds().getNorthEast().lng(),
    	swlat: zm_map.map.getBounds().getSouthWest().lat(),
    	swlng: zm_map.map.getBounds().getSouthWest().lng(),
    	title: $("#title").val(),
    	text: $("#text").val(),
    	zoom: zm_map.map.getZoom()
    	},
        function(data) {
    		update_broadcasts();
    });
	
	$("#add_broadcast").hide();
	return false;
}

function get_broadcast(broadcastid, bc_title)
{
	$("#broadcast_info").show();
	
	if(bc_title != null) {
		$("#bc_title").text(bc_title);
	}

    $.post("broadcast.php", {
    	action: "get_broadcast",
    	broadcastid: broadcastid
    	},
        function(data) {
    		$("#bc_body").empty();
    		$("#bc_body").append(data);
    });
    
    return false;
}

function close_broadcast()
{
	$("#broadcast_info").hide();
	
	return false;
}

function new_post(broadcastid, title)
{
    $.post("broadcast.php", {
    	new_post: "starcraft",
    	broadcastid: broadcastid,
    	text: $("#bc_new_post_text").val()
    	},
        function(data) {
    		$("#bc_body").empty();
    		$("#bc_body").append(data);
    });
    
    get_broadcast(broadcastid, null)
    
    return false;
}