export default class Parts {

	// Properties
	// =========================================================================

	_parts = null;

	constructor (parts, service) {
		// TODO: Normalize parts based off service
		// NOTE: `parts` will be null for MapKit
		this._parts = parts;
	}

}
