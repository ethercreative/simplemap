/* globals google */

/**
 * Load the google API into the dom
 *
 * @param {string} key - Google Maps API key
 * @param {string} locale - The locale
 * @param {boolean=} includeDrawing - Include the drawing library
 * @static
 */
export const loadGoogleAPI = function (key, locale, includeDrawing = false) {
	window.simpleMapsLoadingGoogle = true;
	
	const gmjs = document.createElement("script");
	gmjs.type = "text/javascript";
	gmjs.src = "https://www.google.com/jsapi?key=" + key;
	gmjs.onreadystatechange = function () {
		loadMapsApi(key, locale, includeDrawing);
	};
	gmjs.onload = function () {
		loadMapsApi(key, locale, includeDrawing);
	};
	document.body.appendChild(gmjs);
};

/**
 * Load the google maps API into the dom
 *
 * @param {string} key - Google Maps API key
 * @param {string} locale - The locale
 * @param {boolean=} includeDrawing - Include the drawing library
 * @static
 */
export const loadMapsApi = function (key, locale, includeDrawing = false) {
	google.load("maps", "3", {
		other_params: [
			"libraries=places" + (includeDrawing ? ",drawing" : ""),
			`key=${key}`,
			`language=${locale.replace("_", "-")}`,
			`region=${locale}`,
		].join("&"),
		callback: function () {
			document.dispatchEvent(new Event("SimpleMapsGAPILoaded"));
		}
	});
};