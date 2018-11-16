export default class Google {

	constructor () {
		this.session = new window.google.maps.places.AutocompleteSessionToken();
		this.service = new window.google.maps.places.AutocompleteService();
		this.places = new window.google.maps.places.PlacesService(
			document.createElement("div")
		);
	}

	search (query) {
		return new Promise(resolve => {
			this.service.getPlacePredictions({
				input: query,
				sessionToken: this.session,
			}, predictions => {
				if (predictions === null)
					return resolve([]);

				resolve(predictions.map(result => ({
					id: result.place_id,
					text: result.description,
					highlights: result.matched_substrings.map(m => ([m.offset, m.length])),
				})));
			});
		});
	}

	details (id) {
		return new Promise(resolve => {
			this.places.getDetails({
				placeId: id,
				fields: ["address_component"],
			}, place => {
				// TODO: Format
				resolve(place);
			});
		});
	}

}