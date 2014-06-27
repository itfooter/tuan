function initialize(address) {
  var map = new GMap2(document.getElementById("map_canvas"));
  var geocoder = new GClientGeocoder();
  geocoder.getLatLng(
   address,
   function(point)
   {		
		map.setCenter(point, 14);
        var marker = new GMarker(point);
        map.addOverlay(marker);
   });
  
}
