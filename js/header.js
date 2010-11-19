/*****************************************************
 * Header.js
 *****************************************************/

$(document).ready(function() {
	$(".fancybox_iframe").fancybox({
		'width'				: '75%',
		'height'			: '75%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
});

var zm_map = new function() {
	this.map;
	
	this.init = function () {
		var map_options = {
			zoom: zm_user.default_zoom,
			disableDefaultUI: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
			
		zm_map.map = new google.maps.Map(document.getElementById("map_canvas"), map_options);
		
		if(!get_last_location()) {
			zm_map.map.setCenter(new google.maps.LatLng(33.688924,-117.772064));
		}
			
		switch(zm_user.default_view) {
			case 'default':
				geocoder = new google.maps.Geocoder();
				getGeoLocation();
				break;
			case 'current':
				zm_map.map.setCenter(zm_user.default_location);
				break;
			case 'detect':
				geocoder = new google.maps.Geocoder();
				getGeoLocation();
				break;
			case 'last':
				break;
		}
		


		google.maps.event.addListener(zm_map.map, 'click', function(event) {
			placeMarker(event.latLng);
		});

		google.maps.event.addListener(zm_map.map, 'tilesloaded', function () {
			update_markers();
			update_broadcasts();
		});

		google.maps.event.addListener(zm_map.map, 'zoom_changed', function() {
			zoom_changed();
		});
	};
};

var map_markers_add;
var map_markers_add_path = "image/Random.png";
var geocoder;
var map_markers = new Array();
var map_broadcasts = new Array();



function zoom_changed()
{
	if(zm_map.map.getZoom() < 10) {
		$("#new_broadcast").hide();
		$("#new_broadcast_message").show();
	}
	else {
		$("#new_broadcast").show();
		$("#new_broadcast_message").hide();
	}
}

function getGeoLocation()
{
	if(navigator.geolocation) {
		// Try W3C Geolocation method (Preferred)
		navigator.geolocation.getCurrentPosition( function(position) {
			zm_map.map.setCenter(new google.maps.LatLng(position.coords.latitude,position.coords.longitude));
		}, function() {
			handleNoGeolocation();
		});
	} else if (google.gears) {
		// Try Google Gears Geolocation
		var geo = google.gears.factory.create('beta.geolocation');
		geo.getCurrentPosition(function(position) {
			zm_map.map.setCenter(new google.maps.LatLng(position.latitude,position.longitude));
		}, function() {
			handleNoGeolocation();
		});
	} else {
		// Browser doesn't support Geolocation
		handleNoGeolocation();
	}
}

function handleNoGeolocation() {
}

function get_last_location()
{
	var last_view = cookie_get('view');
	
	if(last_view != "") {
		last_view = eval(last_view);
		zm_map.map.setCenter(new google.maps.LatLng(last_view[0], last_view[1]));
		zm_map.map.setZoom(last_view[2]);
		return true;
	}
	return false;
}

/***************below are deferable ***/

function my_location()
{
	zm_map.map.setCenter(zm_user.home);
	zm_map.map.setZoom(zm_user.home_zoom);
	
	return false;
}

function cookie_set(name, value, expiredays)
{
	var host=window.location.hostname;		
	var array=host.split('.');
	var domain=host.replace("www", "");	
	if(array.length==2)
		domain="."+host;
	else if(array.length==3&&array[0]!="www")
		domain="."+array[1]+"."+array[2];	
	
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie="zm_"+name+ "=" +escape(value)+
	((expiredays==null) ? "" : ";expires="+exdate.toGMTString())+";path=/;domain="+domain;		
}

function cookie_get(name)
{
	if (document.cookie.length>0)
	{
		c_start=document.cookie.indexOf("zm_"+name + "=");
		if (c_start!=-1)
	    {
			c_start=c_start + name.length+4;
			c_end=document.cookie.indexOf(";",c_start);
			if (c_end==-1) c_end=document.cookie.length;			
			return unescape(document.cookie.substring(c_start,c_end));
	    }
	}
	return "";
}


function cancel_solo()
{
	map_markers_add.setMap(null);
	map_markers_add = null;
	
	$("form.solo").hide();
	return false;
}

