/* globals google */

/**
 * Load the google API into the dom
 *
 * @param {string} key - Google Maps API key
 * @param {string} locale - The locale
 * @static
 */
export const loadGoogleAPI = function (key, locale) {
	window.simpleMapsLoadingGoogle = true;
	
	const gmjs = document.createElement("script");
	gmjs.type = "text/javascript";
	gmjs.src = "https://www.google.com/jsapi?key=" + key;
	gmjs.onreadystatechange = function () {
		loadMapsApi(key, locale);
	};
	gmjs.onload = function () {
		loadMapsApi(key, locale);
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
export const loadMapsApi = function (key, locale) {
	google.load("maps", "3", {
		other_params: [
			"libraries=places",
			`key=${key}`,
			`language=${locale.replace("_", "-")}`,
			`region=${locale}`,
		].join("&"),
		callback: function () {
			document.dispatchEvent(new Event("SimpleMapsGAPILoaded"));
		}
	});
};