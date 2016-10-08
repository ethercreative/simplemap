var SimpleMap = function (key, mapId, settings) {
	if (!key) {
		SimpleMap.Fail('Missing API Key!');
		return;
	}

	// Vars
	this.setup = false;
	this.settings = settings;
	this.mapEl = document.getElementById(mapId);
	this.address = document.getElementById(mapId + '-address');
	this.inputs = {
		lat: document.getElementById(mapId + '-input-lat'),
		lng: document.getElementById(mapId + '-input-lng'),
		zoom: document.getElementById(mapId + '-input-zoom'),
		address: document.getElementById(mapId + '-input-address'),
		parts: document.getElementById(mapId + '-input-address-parts'),
		partsBase: document.getElementById(mapId + '-input-parts-base')
	};

	// Setup settings
	this.settings = {
		height: this.settings.height,
		lat: parseFloat(this.settings.lat),
		lng: parseFloat(this.settings.lng),
		zoom: parseInt(this.settings.zoom),
		hideMap: this.settings.hideMap,

		country: this.settings.country,
		type: this.settings.type,
		boundary: this.settings.boundary
	};

	// Stop submission on address field enter
	this.address.addEventListener('keydown', function (e) {
		if (e.keyCode === 13) e.preventDefault();
	});

	if (this.settings.hideMap) {
		this.AutoCompleteOnly(key);
		return;
	}

	// Check we have everything we need
	if (!this.mapEl || !this.address || !this.inputs.lat || !this.inputs.lng || !this.inputs.address || !this.inputs.parts) {
		SimpleMap.Fail('Map inputs with id ' + mapId + ' not found!');
		return;
	}

	var self = this;

	// Load Google APIs if they aren't already
	if (typeof google === "undefined") {
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI(key);
	} else if (!google.maps || !google.maps.places) { // Load Google Maps APIs if the aren't already
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI.LoadMapsApi(key);
	} else {
		if (!self.setup) self.setupMap();
	}

	document.addEventListener('SimpleMapsGAPILoaded', function () {
		if (!self.setup) self.setupMap();
	});

	// Re-draw map on tab change
	if (document.getElementById('tabs')) {
		[].slice.call(document.getElementById('tabs').getElementsByTagName('a')).forEach(function (el) {
			el.addEventListener('click', function () {
				var x = self.map.getZoom(),
					c = self.map.getCenter();

				setTimeout(function () {
					google.maps.event.trigger(self.map, 'resize');
					self.map.setZoom(x);
					self.map.setCenter(c);
				}, 1);
			});
		});
	}
};

SimpleMap.prototype.AutoCompleteOnly = function (key) {
	var self = this;

	// Load Google APIs if they aren't already
	if (typeof google === "undefined") {
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI(key);
	} else if (!google.maps || !google.maps.places) { // Load Google Maps APIs if the aren't already
		if (!window.simpleMapsLoadingGoogle) SimpleMap.LoadGoogleAPI.LoadMapsApi(key);
	} else {
		if (!self.setup) self.setupAutoComplete();
	}

	document.addEventListener('SimpleMapsGAPILoaded', function () {
		if (!self.setup) self.setupAutoComplete();
	});
};

SimpleMap.Fail = function (message) {
	Craft.cp.displayError('<strong>SimpleMap:</strong> ' + message);
	if (window.console) console.error.apply(console, ['%cSimpleMap: %c' + message, 'font-weight:bold;','font-weight:normal;']);
};

SimpleMap.LoadGoogleAPI = function (key) {
	window.simpleMapsLoadingGoogle = true;

	var gmjs = document.createElement('script');
	gmjs.type = 'text/javascript';
	gmjs.src = 'https://www.google.com/jsapi?key=' + key;
	gmjs.onreadystatechange = function () {
		SimpleMap.LoadGoogleAPI.LoadMapsApi(key);
	};
	gmjs.onload = function () {
		SimpleMap.LoadGoogleAPI.LoadMapsApi(key);
	};
	document.body.appendChild(gmjs);
};

SimpleMap.LoadGoogleAPI.LoadMapsApi = function (key) {
	google.load('maps', '3', { other_params: 'libraries=places&key='+key, callback: function () {
		document.dispatchEvent(new Event('SimpleMapsGAPILoaded'));
	}});
};

SimpleMap.prototype.formatBoundary = function () {
	if (this.settings.boundary !== '') {
		var ne = new google.maps.LatLng(this.settings.boundary.ne.lat, this.settings.boundary.ne.lng),
			sw = new google.maps.LatLng(this.settings.boundary.sw.lat, this.settings.boundary.sw.lng);
		this.settings.boundary = new google.maps.LatLngBounds(ne, sw);
	}
};

// Setup Map
SimpleMap.prototype.setupMap = function () {
	this.setup = true;
	var self = this;

	this.formatBoundary();

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

	this.setupAutoComplete();

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

SimpleMap.prototype.setupAutoComplete = function () {
	if (!this.setup) {
		this.setup = true;
		this.formatBoundary();
	}
	if (!this.geocoder) this.geocoder = new google.maps.Geocoder();
	var self = this;

	// Setup address search
	var opts = {};
	if (this.settings.country !== '') opts.componentRestrictions = {country: this.settings.country};
	if (this.settings.type !== '') opts.types = [this.settings.type];
	if (this.settings.boundary !== '') opts.bounds = this.settings.boundary;

	var autocomplete = new google.maps.places.Autocomplete(this.address, opts);
	if (!this.settings.hideMap) {
		autocomplete.map = this.map;
		autocomplete.bindTo('bounds', this.map);
	}

	// Initial Update
	setTimeout(function () {
		google.maps.event.trigger(autocomplete, 'place_changed');
	}, 1);

	// Update map on paste
	this.address.addEventListener('paste', function () {
		setTimeout(function () {
			google.maps.event.trigger(autocomplete, 'place_changed');
		}, 1);
	});

	this.address.addEventListener('input', function () {
		if (this.value === '') self.clear();
	});

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
				self.update(parseFloat(lat), parseFloat(lng)).sync().center();

				return;
			}
		}

		// If we have a place
		var place = this.getPlace();

		if (place && place.geometry) {
			lat = place.geometry.location.lat();
			lng = place.geometry.location.lng();

			self.update(lat, lng).sync().center();

			return;
		}

		// If the client hit enter, search
		self.geo(address, function (loc) {
			var lat = loc.geometry.location.lat(),
				lng = loc.geometry.location.lng();

			self.update(lat, lng).sync().center();
		});

	});
};

SimpleMap.prototype.update = function (lat, lng, leaveMarker, leaveFields) {
	var latLng = new google.maps.LatLng(lat, lng);

	if (!leaveFields) {
		this.inputs.lat.value = lat;
		this.inputs.lng.value = lng;
	}

	if (!leaveMarker && !this.settings.hideMap) {
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
	if (this.settings.hideMap) return this;

	this.map.setCenter(this.map.marker.getPosition());

	return this;
};

SimpleMap.prototype.sync = function (update) {
	var pos = this.settings.hideMap ? new google.maps.LatLng(this.inputs.lat.value, this.inputs.lng.value) : this.map.marker.getPosition(),
		self = this;

	// Update address / lat / lng based off marker location
	this.geo(pos, function (loc) {
		// if loc, set address to formatted_location, else to position
		var address = loc ? loc.formatted_address : pos.lat() + ", " + pos.lng();

		// update address value
		self.address.value = address;
		self.inputs.address.value = address;

		// update address parts
		while (self.inputs.parts.firstChild)
			self.inputs.parts.removeChild(self.inputs.parts.firstChild);

		var name = self.inputs.partsBase.name;
		console.log(loc.address_components);
		loc.address_components.forEach(function (el) {
			var input = document.createElement('input'),
				n = el.types[0];
			if (!n) return;
			if (n === 'postal_code_prefix') n = 'postal_code';
			input.type = 'hidden';
			input.name = name + '[' + n + ']';
			input.value = el.long_name;
			self.inputs.parts.appendChild(input);

			var inputS = input.cloneNode(true);
			inputS.name = name + '[' + n + '_short]';
			inputS.value = el.short_name;
			self.inputs.parts.appendChild(inputS);
		});
	});

	if (update) return this.update(pos.lat(), pos.lng(), true);
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

	while (this.inputs.parts.firstChild)
		this.inputs.parts.removeChild(this.inputs.parts.firstChild);
};

window.SimpleMap = SimpleMap;