/*****************************************************
 * user.js
 *****************************************************/
var zm_user = new function() {
	this.home = new google.maps.LatLng(0, 0);
	this.home_zoom = 10;
	this.default_location = new google.maps.LatLng(0, 0);
	this.default_zoom = 10;
	this.default_view = "default";
};