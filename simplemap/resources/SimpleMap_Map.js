;(function (window) {
	
	const __MOBILE__ = (function() {
		let check = false;
		(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
		return check;
	})();
	
	/**
	 * SimpleMap Class
	 *
	 * @param {string} key - Google Maps API key
	 * @param {string} mapId - Map field ID
	 * @param {object} settings - The map settings object
	 * @param {string} locale - The entries locale
	 * @constructor
	 */
	const SimpleMap = function (key, mapId, settings, locale) {
		if (!key) {
			SimpleMap.Fail('Missing API Key!');
			return;
		}
		
		// Vars
		this.setup = false;
		this.updateByAddress = false;
		this.settings = settings;
		this.mapEl = document.getElementById(mapId);
		this.address = document.getElementById(mapId + '-address');
		this.inputs = {
			lat: document.getElementById(mapId + '-input-lat'),
			lng: document.getElementById(mapId + '-input-lng'),
			zoom: document.getElementById(mapId + '-input-zoom'),
			address: document.getElementById(mapId + '-input-address'),
		};
		
		// Setup settings
		this.settings = {
			height: this.settings.height,
			lat: parseFloat(this.settings.lat),
			lng: parseFloat(this.settings.lng),
			zoom: parseInt(this.settings.zoom),
			hideMap: this.settings.hideMap,
			hideLatLng: this.settings.hideLatLng,
			
			country: this.settings.country,
			type: this.settings.type,
			boundary: this.settings.boundary,
			
			locale: locale,
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
		if (
			!this.mapEl ||
			!this.address ||
			!this.inputs.lat ||
			!this.inputs.lng
		) {
			SimpleMap.Fail('Map inputs with id ' + mapId + ' not found!');
			return;
		}
		
		const self = this;
		
		// Load Google APIs if they aren't already
		if (typeof google === "undefined") {
			if (!window.simpleMapsLoadingGoogle)
				SimpleMap.LoadGoogleAPI(key, locale);
		} else if (!google.maps || !google.maps.places) {
			// Load Google Maps APIs if the aren't already
			if (!window.simpleMapsLoadingGoogle)
				SimpleMap.LoadGoogleAPI.LoadMapsApi(key, locale);
		} else {
			if (!self.setup)
				self.setupMap();
		}
		
		document.addEventListener('SimpleMapsGAPILoaded', function () {
			if (!self.setup)
				self.setupMap();
		});
		
		// Bind Lat / Lng input events
		if (!this.settings.hideLatLng) {
			this.inputs.lat.addEventListener('input', this.onLatLngChange.bind(this));
			this.inputs.lat.addEventListener('change', this.onLatLngChange.bind(this));
			
			this.inputs.lng.addEventListener('input', this.onLatLngChange.bind(this));
			this.inputs.lng.addEventListener('change', this.onLatLngChange.bind(this));
		}
		
		// Re-draw map on tab change
		if (document.getElementById('tabs')) {
			[].slice.call(
				document.getElementById('tabs').getElementsByTagName('a')
			).map(function (el) {
				el.addEventListener('click', function () {
					const x = self.map.getZoom()
						, c = self.map.getCenter();
					
					setTimeout(function () {
						google.maps.event.trigger(self.map, 'resize');
						self.map.setZoom(x);
						self.map.setCenter(c);
					}, 1);
				});
			});
		}
	};
	
	/**
	 * Setup only the auto-complete
	 *
	 * @param {string} key - Google Maps API key
	 */
	SimpleMap.prototype.AutoCompleteOnly = function (key) {
		const self = this;
		
		// Load Google APIs if they aren't already
		if (typeof google === "undefined") {
			if (!window.simpleMapsLoadingGoogle)
				SimpleMap.LoadGoogleAPI(key, this.settings.locale);
		} else if (!google.maps || !google.maps.places) {
			// Load Google Maps APIs if the aren't already
			if (!window.simpleMapsLoadingGoogle)
				SimpleMap.LoadGoogleAPI.LoadMapsApi(key, this.settings.locale);
		} else {
			if (!self.setup)
				self.setupAutoComplete();
		}
		
		document.addEventListener('SimpleMapsGAPILoaded', function () {
			if (!self.setup)
				self.setupAutoComplete();
		});
	};
	
	/**
	 * Log an error message to the console and screen
	 *
	 * @param {string} message - The error message
	 * @static
	 */
	SimpleMap.Fail = function (message) {
		Craft.cp.displayError('<strong>SimpleMap:</strong> ' + message);
		if (window.console) {
			console.error.apply(console, [
				'%cSimpleMap: %c' + message,
				'font-weight:bold;',
				'font-weight:normal;'
			]);
		}
	};
	
	/**
	 * Load the google API into the dom
	 *
	 * @param {string} key - Google Maps API key
	 * @param {string} locale - The locale
	 * @static
	 */
	SimpleMap.LoadGoogleAPI = function (key, locale) {
		window.simpleMapsLoadingGoogle = true;
		
		const gmjs = document.createElement('script');
		gmjs.type = 'text/javascript';
		gmjs.src = 'https://www.google.com/jsapi?key=' + key;
		gmjs.onreadystatechange = function () {
			SimpleMap.LoadGoogleAPI.LoadMapsApi(key, locale);
		};
		gmjs.onload = function () {
			SimpleMap.LoadGoogleAPI.LoadMapsApi(key, locale);
		};
		document.body.appendChild(gmjs);
	};
	
	/**
	 * Load the google maps API into the dom
	 *
	 * @param {string} key - Google Maps API key
	 * @param {string} locale - The locale
	 * @static
	 */
	SimpleMap.LoadGoogleAPI.LoadMapsApi = function (key, locale) {
		google.load('maps', '3', {
			other_params: 'libraries=places&key=' + key +
			'&language=' + locale.replace('_', '-') +
			'&region=' + locale,
			callback: function () {
				document.dispatchEvent(new Event('SimpleMapsGAPILoaded'));
			}
		});
	};
	
	/**
	 * Formats the map boundary from two sets of LatLnds
	 */
	SimpleMap.prototype.formatBoundary = function () {
		if (this.settings.boundary !== '') {
			const ne = new google.maps.LatLng(
					this.settings.boundary.ne.lat,
					this.settings.boundary.ne.lng
				),
				sw = new google.maps.LatLng(
					this.settings.boundary.sw.lat,
					this.settings.boundary.sw.lng
				);
			this.settings.boundary = new google.maps.LatLngBounds(ne, sw);
		}
	};
	
	/**
	 * Setup the Map!
	 */
	SimpleMap.prototype.setupMap = function () {
		this.setup = true;
		const self = this;
		
		this.formatBoundary();
		
		// Geocoder (for address search)
		this.geocoder = new google.maps.Geocoder();
		
		// Set Map Height
		this.mapEl.style.height = this.settings.height + 'px';
		
		// Create Map
		this.map = new google.maps.Map(this.mapEl, {
			zoom:		 this.settings.zoom,
			scrollwheel: false,
			center:		 new google.maps.LatLng(
						 	 this.settings.lat,
							 this.settings.lng
						 ),
			mapTypeId:	 google.maps.MapTypeId.ROADMAP,
		});
		
		this.setupAutoComplete();
		
		// Add marker
		this.map.marker = new google.maps.Marker({
			draggable: true,
			raiseOnDrag: true,
			map: this.map
		});
		
		// Get the initial lat/lng/zoom,
		// falling back to defaults if we don't have one
		const lat = this.inputs.lat.value   || this.settings.lat
			, lng = this.inputs.lng.value   || this.settings.lng
			, zoom = this.inputs.zoom.value || this.settings.zoom;
		
		// Update the marker location & center the map
		this.update(lat, lng, false, true).center();
		
		// Update map to saved zoom
		this.map.setZoom(parseInt(zoom));
		
		if (__MOBILE__) {
			// Hide the marker
			this.map.marker.setVisible(false);
			
			// Add a custom marker
			this.addCentredMarker();
			
			// If we're on mobile, lock the marker to the centre and use the
			// drag to position it
			google.maps.event.addListener(this.map, 'dragend', function () {
				const center = self.map.getCenter();
				self.update(center.lat(), center.lng()).sync();
			});
		} else {
			// When the marker is dropped
			google.maps.event.addListener(this.map.marker, 'dragend', function () {
				self.sync(true);
			});
			
			// When map is clicked
			google.maps.event.addListener(this.map, 'click', function (e) {
				
				const lat = e.latLng.lat()
					, lng = e.latLng.lng();
				
				self.update(lat, lng).sync();
			});
		}
		
		// When the zoom is changed
		google.maps.event.addListener(this.map, 'zoom_changed', function () {
			self.updateZoom(this.getZoom()).center();
		});
	};
	
	/**
	 * Setup the auto-complete!
	 */
	SimpleMap.prototype.setupAutoComplete = function () {
		if (!this.setup) {
			this.setup = true;
			this.formatBoundary();
		}
		if (!this.geocoder) this.geocoder = new google.maps.Geocoder();
		const self = this;
		
		// Setup address search
		const opts = {};
		
		if (this.settings.country !== '')
			opts.componentRestrictions = {country: this.settings.country};
		
		if (this.settings.type !== '')
			opts.types = [this.settings.type];
		
		if (this.settings.boundary !== '')
			opts.bounds = this.settings.boundary;
		
		const autocomplete = new google.maps.places.Autocomplete(
			this.address,
			opts
		);
		
		if (!this.settings.hideMap) {
			autocomplete.map = this.map;
			autocomplete.bindTo('bounds', this.map);
		}
		
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
			let address = self.address.value, lat, lng;
			
			self.updateByAddress = true;
			
			// If we have a place
			const place = this.getPlace();
			
			if (place && place.geometry) {
				lat = place.geometry.location.lat();
				lng = place.geometry.location.lng();
				
				self.update(lat, lng).sync().center();
				
				return;
			}
			
			// If the client hit enter, search
			self.geo(address, function (loc) {
				const lat = loc.geometry.location.lat()
					, lng = loc.geometry.location.lng();
				
				self.update(lat, lng).sync().center();
			});
			
		});
	};
	
	/**
	 * Updates the maps location when the Lat/Lng fields change
	 */
	SimpleMap.prototype.onLatLngChange = function () {
		const lat = this.inputs.lat.value
			, lng = this.inputs.lng.value;
		
		this.update(lat, lng, false, true);
		this.center();
	};
	
	/**
	 * Update the map location
	 *
	 * @param {float|Number} lat - Latitude
	 * @param {float|Number} lng - Longitude
	 * @param {boolean=} leaveMarker - Leave the marker in it's old position
	 * @param {boolean=} leaveFields - Leave the lat/lng fields with their old
	 *     values
	 *
	 * @return {SimpleMap}
	 */
	SimpleMap.prototype.update = function (lat, lng, leaveMarker, leaveFields) {
		const latLng = new google.maps.LatLng(lat, lng);
		
		if (!leaveFields) {
			this.inputs.lat.value = lat;
			this.inputs.lng.value = lng;
		}
		
		if (!leaveMarker && !this.settings.hideMap) {
			this.map.marker.setPosition(latLng);
			this.map.marker.setVisible(!__MOBILE__);
		}
		
		return this;
	};
	
	/**
	 * Update the zoom input
	 *
	 * @param {number} zoom - Zoom level
	 * @return {SimpleMap}
	 */
	SimpleMap.prototype.updateZoom = function (zoom) {
		this.inputs.zoom.value = zoom;
		
		return this;
	};
	
	/**
	 * Center the map around the marker
	 *
	 * @return {SimpleMap}
	 */
	SimpleMap.prototype.center = function () {
		if (this.settings.hideMap) return this;
		
		this.map.setCenter(this.map.marker.getPosition());
		
		return this;
	};
	
	/**
	 * Sync the hidden fields to the maps location
	 *
	 * @param {boolean=} update - Update the map
	 * @return {SimpleMap}
	 */
	SimpleMap.prototype.sync = function (update) {
		const self = this
			, pos = this.settings.hideMap
				? new google.maps.LatLng(this.inputs.lat.value, this.inputs.lng.value)
				: this.map.marker.getPosition();
		
		if (!this.updateByAddress) {
			// Update address / lat / lng based off marker location
			this.geo(pos, function (loc) {
				// if loc, set address to formatted_location, else to position
				// update address value
				self.address.value = loc
					? loc.formatted_address : pos.lat() + ", " + pos.lng();
			});
		}
		
		this.updateByAddress = false;
		
		if (update) return this.update(pos.lat(), pos.lng(), true);
		return this;
	};
	
	/**
	 * Get GeoCode data from a LatLng
	 *
	 * @param {google.maps.LatLng|string} latLng - The location to search
	 * @param {SimpleMap~geoCallback} callback
	 * @param {number=} tryNumber
	 */
	SimpleMap.prototype.geo = function (latLng, callback, tryNumber) {
		if (typeof tryNumber === typeof undefined)
			tryNumber = 0;
		
		const self = this;
		
		let attr = {'latLng': latLng};
		if (!latLng.lat) attr = {'address': latLng};
		
		this.geocoder.geocode(attr, function (results, status) {
			let loc;
			
			// if location available, set loc to first result
			if (status === google.maps.GeocoderStatus.OK) {
				loc = results[0];
				
				// if zero_results, set loc to false
			} else if (status === google.maps.GeocoderStatus.ZERO_RESULTS) {
				loc = false;
				
				// if over_query_limit, wait and try again
			} else if (
				status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT &&
				tryNumber <= 5
			) {
				setTimeout(function () {
					self.geo(latLng, callback, tryNumber + 1);
				}, 1000);
				
				// else return error message
			} else {
				SimpleMap.Fail('Geocoder failed as a result of ' + status);
				return;
			}
			
			callback(loc);
		});
	};
	
	/**
	 * Clears the map
	 */
	SimpleMap.prototype.clear = function () {
		this.inputs.lat.value = '';
		this.inputs.lng.value = '';
		this.inputs.zoom.value = '';
		this.address.value = '';
	};
	
	SimpleMap.prototype.addCentredMarker = function () {
		const marker = document.createElement("img");
		marker.src = "https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi.png";
		marker.setAttribute(
			"srcset",
			"https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi.png 1x, https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi_hdpi.png 2x"
		);
		marker.style.position = "absolute";
		marker.style.zIndex = 3;
		marker.style.top = "50%";
		marker.style.left = "50%";
		marker.style.transform = "translate3d(-50%, -100%, 0)";
		
		this.mapEl.insertBefore(marker, this.mapEl.firstElementChild);
	};
	
	/**
	 * The SimpleMap.prototype.geo callback
	 *
	 * @callback SimpleMap~geoCallback
	 * @param {object|boolean} loc - The found location
	 */
	
	window.SimpleMap = SimpleMap;
})(window);
