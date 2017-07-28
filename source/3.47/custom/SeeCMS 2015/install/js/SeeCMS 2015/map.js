
$(document).ready(function(){
  initialize();
});

function initialize() {
  var myLatlng = new google.maps.LatLng(53.986594, -1.099459);

  var myOptions = {
    zoom: 12,
    center: myLatlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  }
  
  var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
  
  var marker = new google.maps.Marker({
      position: myLatlng,
      map: map,
      title:"York"
  });  
}