/* eslint-disable camelcase */

import Parts from './Parts';
import GeoService from '../enums/GeoService';

export default class PartsLegacy extends Parts {

	// Properties
	// =========================================================================

	administrative_area_level_1 = '';
	administrative_area_level_2 = '';
	administrative_area_level_3 = '';
	administrative_area_level_4 = '';
	administrative_area_level_5 = '';
	airport = '';
	bus_station = '';
	colloquial_area = '';
	establishment = '';
	floor = '';
	intersection = '';
	locality = '';
	natural_feature = '';
	neighborhood = '';
	park = '';
	parking = '';
	point_of_interest = '';
	political = '';
	post_box = '';
	postal_code = '';
	postal_code_prefix = '';
	postal_town = '';
	premise = '';
	room = '';
	route = '';
	street_address = '';
	street_number = '';
	sublocality = '';
	sublocality_level_1 = '';
	sublocality_level_2 = '';
	sublocality_level_3 = '';
	sublocality_level_4 = '';
	sublocality_level_5 = '';
	subpremise = '';
	train_station = '';
	transit_station = '';

	administrative_area_level_1_short = '';
	administrative_area_level_2_short = '';
	administrative_area_level_3_short = '';
	administrative_area_level_4_short = '';
	administrative_area_level_5_short = '';
	airport_short = '';
	bus_station_short = '';
	colloquial_area_short = '';
	establishment_short = '';
	floor_short = '';
	intersection_short = '';
	locality_short = '';
	natural_feature_short = '';
	neighborhood_short = '';
	park_short = '';
	parking_short = '';
	point_of_interest_short = '';
	political_short = '';
	post_box_short = '';
	postal_code_short = '';
	postal_code_prefix_short = '';
	postal_town_short = '';
	premise_short = '';
	room_short = '';
	route_short = '';
	street_address_short = '';
	street_number_short = '';
	sublocality_short = '';
	sublocality_level_1_short = '';
	sublocality_level_2_short = '';
	sublocality_level_3_short = '';
	sublocality_level_4_short = '';
	sublocality_level_5_short = '';
	subpremise_short = '';
	train_station_short = '';
	transit_station_short = '';

	constructor (parts) {
		super(parts, GeoService.GoogleMaps);

		Object.keys(parts).forEach(key => {
			this[key] = parts[key];
		});
	}

}
