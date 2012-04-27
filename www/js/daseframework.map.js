$(document).ready(function() {
	var mylat = $('meta[name="dataset-lat"]').attr('content');
	var mylng = $('meta[name="dataset-lng"]').attr('content');

	var zoom = 11;
	if (!mylat && !mylng) {
		zoom = 2;
	}

	var myOptions = {
		center: new google.maps.LatLng(mylat,mylng),
		zoom: zoom,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	if (mylat && mylng) {

		var ll = new google.maps.LatLng(mylat,mylng);
		var image = "https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=|ff6666|000000";
		var marker = new google.maps.Marker({
			position: ll,
			map: map,
			icon: image,
			zIndex: 2
		});
		marker.red = 1
		//google.maps.event.addListener(marker, 'click', Dase.toggleBounce);
	} else {
		var image = "https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=|ff6666|000000";
		var marker = new google.maps.Marker({
			map: map,
			icon: image,
			zIndex: 2
		});
		marker.red = 1
	}

	map.marker = marker;

	/*
	$('#locform').submit(function() {
		var data = $(this).serialize();
		var url = $(this).attr('action');
		$.ajax({
			type: "POST",
			url: url,
			data: data,
		});
	});
	*/

	google.maps.event.addListener(map, "click", function(event)
	{
		var latLng = event.latLng;
		var newlat = latLng.lat();
		var newlng = latLng.lng();
		var ll = new google.maps.LatLng(newlat,newlng);
		map.marker.setPosition(ll);
		$('input[name="lat"]').val(newlat);
		$('input[name="lng"]').val(newlng);
		$("#lng").html(newlng);
	});
});


