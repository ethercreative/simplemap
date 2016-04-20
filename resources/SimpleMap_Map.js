var SimpleMap = function (mapId, settings) {
	// Vars
	this.setup = false;
	this.settings = settings;
	this.mapEl = document.getElementById(mapId);
	this.address = document.getElementById(mapId + '-address');
	this.inputs = {
		lat: document.getElementById(mapId + '-input-lat'),
		lng: document.getElementById(mapId + '-input-lng'),
		zoom: document.getElementById(mapId + '-input-zoom'),
		address: document.getElementById(mapId + '-input-address')
	};

	// Check we have everything we need
	if (!this.mapEl || !this.address || !this.inputs.lat || !this.inputs.lng || !this.inputs.address) {
		SimpleMap.Fail('Map inputs with id ' + mapId + ' not found!');
		return;
	}

	// Setup settings
	this.settings = {
		height: this.settings.height,
		lat: parseFloat(this.settings.lat),
		lng: parseFloat(this.settings.lng),
		zoom: parseInt(this.settings.zoom)
	};

	// Stop submission on address field enter
	this.address.addEventListener('keydown', function (e) {
		if (e.keyCode === 13) e.preventDefault();
	});

	var self = this;

	// Load Google APIs if they aren't already
	if (typeof google === "undefined") {
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI();
	} else if (!google.maps || !google.maps.places) { // Load Google Maps APIs if the aren't already
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI.LoadMapsApi();
	} else {
		if (!self.setup) self.setupMap();
	}

	document.addEventListener('SimpleMapsGAPILoaded', function () {
		if (!self.setup) self.setupMap();
	});
};

SimpleMap.Fail = function (message) {
	Craft.cp.displayError('<strong>SimpleMap:</strong> ' + message);
	if (window.console) console.error.apply(console, ['%cSimpleMap: %c' + message, 'font-weight:bold;','font-weight:normal;']);
};

SimpleMap.LoadGoogleAPI = function () {
	window.simpleMapsLoadingGoogle = true;

	var gmjs = document.createElement('script');
	gmjs.type = 'text/javascript';
	gmjs.src = 'https://www.google.com/jsapi';//'https://maps.googleapis.com/maps/api/js?v=3&amp;sensor=false&amp;libraries=places&amp;callback=google.loader.callbacks.maps';
	gmjs.onreadystatechange = function () {
		SimpleMap.LoadGoogleAPI.LoadMapsApi();
	};
	gmjs.onload = function () {
		SimpleMap.LoadGoogleAPI.LoadMapsApi();
	};
	document.body.appendChild(gmjs);
};

SimpleMap.LoadGoogleAPI.LoadMapsApi = function () {
	google.load('maps', '3', { other_params: 'libraries=places', callback: function () {//sensor=false&
		document.dispatchEvent(new Event('SimpleMapsGAPILoaded'));
	}});
};

// Load Google Maps API
SimpleMap.prototype.loadMaps = function () {
	var self = this;
	google.load('maps', '3', { other_params: 'libraries=places', callback: function () {//sensor=false&
		self.setupMap();
	}});
};

// Setup Map
SimpleMap.prototype.setupMap = function () {
	this.setup = true;
	var self = this;

	// Geocoder (for address search)
	this.geocoder = new google.maps.Geocoder();

	// Set Map Height
	this.mapEl.style.height = this.settings.height + 'px';

	// Create Map
	this.map = new google.maps.Map(this.mapEl, {
		zoom:		this.settings.zoom,
		center:		new google.maps.LatLng(this.settings.lat, this.settings.lng),
		mapTypeId:	google.maps.MapTypeId.ROADMAP
	});

	// Setup address search
	var autocomplete = new google.maps.places.Autocomplete(this.address);
	autocomplete.map = this.map;
	autocomplete.bindTo('bounds', this.map);

	this.address.addEventListener('input', function () {
		if (this.value === '') self.clear();
	});

	// Add marker
	this.map.marker = new google.maps.Marker({
		draggable: true,
		raiseOnDrag: true,
		map: this.map
	});

	// Get the initial lat/lng/zoom, falling back to defaults if we don't have one
	var lat = this.inputs.lat.value   || this.settings.lat,
		lng = this.inputs.lng.value   || this.settings.lng,
		zoom = this.inputs.zoom.value || this.settings.zoom;

	// Update the marker location & center the map
	this.update(lat, lng, false, true).center();

	// Update map to saved zoom
	this.map.setZoom(parseInt(zoom));

	// When the auto-complete place changes
	google.maps.event.addListener(autocomplete, 'place_changed', function () {
		var address = self.address.value, lat, lng;
		self.inputs.address.value = address;

		// If a Lat/Lng
		var latLng = address.split(',');
		if (latLng.length === 2) {
			lat = latLng[0];
			lng = latLng[1];

			if (!isNaN(lat) && !isNaN(lng)) {
				self.update(parseFloat(lat), parseFloat(lng)).center();

				return;
			}
		}

		// If we have a place
		var place = this.getPlace();

		if (place.geometry) {
			lat = place.geometry.location.lat();
			lng = place.geometry.location.lng();

			self.update(lat, lng).center();

			return;
		}

		// If the client hit enter, search
		self.geo(address, function (loc) {
			var lat = loc.geometry.location.lat(),
				lng = loc.geometry.location.lng();

			self.update(lat, lng).center();
		});

	});

	// When the marker is dropped
	google.maps.event.addListener(this.map.marker, 'dragend', function () {
		self.sync(true);
	});

	// When map is clicked
	google.maps.event.addListener(this.map, 'click', function (e) {

		var lat = e.latLng.lat(),
			lng = e.latLng.lng();

		self.update(lat, lng).sync();
	});

	// When the zoom is changed
	google.maps.event.addListener(this.map, 'zoom_changed', function () {
		var zoom = this.getZoom();

		self.updateZoom(zoom).center();
	});
};

SimpleMap.prototype.update = function (lat, lng, leaveMarker, leaveFields) {
	var latLng = new google.maps.LatLng(lat, lng);

	if (!leaveFields) {
		this.inputs.lat.value = lat;
		this.inputs.lng.value = lng;
	}

	if (!leaveMarker) {
		this.map.marker.setPosition(latLng);
		this.map.marker.setVisible(true);
	}

	return this;
};

SimpleMap.prototype.updateZoom = function (zoom) {
	this.inputs.zoom.value = zoom;

	return this;
};

SimpleMap.prototype.center = function () {
	this.map.setCenter(this.map.marker.getPosition());

	return this;
};

SimpleMap.prototype.sync = function (update) {
	var pos = this.map.marker.getPosition(),
		self = this;

	// Update address / lat / lng based off marker location
	this.geo(pos, function (loc) {
		// if loc, set address to formatted_location, else to postion
		var address = loc ? loc.formatted_address : pos.lat() + ", " + pos.lng();

		// update address value
		self.address.value = address;
		self.inputs.address.value = address;
	});

	if (update) return this.update(pos.lat, pos.lng, true);
	return this;
};

SimpleMap.prototype.geo = function (latLng, callback) {
	var attr = {'latLng': latLng};
	if (!latLng.lat) attr = {'address': latLng};

	this.geocoder.geocode(attr, function (results, status) {
		var loc;

		// if location available, set loc to first result
		if (status == google.maps.GeocoderStatus.OK) {
			loc = results[0];

		// if zero_results, set loc to false
		} else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
			loc = false;

		// else return error message
		} else {
			SimpleMap.Fail('Geocoder failed as a result of ' + status);
			return;
		}

		callback(loc);
	});
};

SimpleMap.prototype.clear = function () {
	this.inputs.lat.value = '';
	this.inputs.lng.value = '';
	this.inputs.zoom.value = '';
	this.inputs.address.value = '';
};

window.SimpleMap = SimpleMap;