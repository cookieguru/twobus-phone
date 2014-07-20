<?php
/**
 * Returns a route number formatted for TTS in a manner users expect
 * "550" becomes "5 50" which is pronounced "Five fity" instead of 
 * "Five hundred fifty"
 *
 * @param mixed $route_number The route number which can be suffixed with letter(s)
 *
 * @return string Formatted string
 */
function say_route($route_number) {
	preg_match('/(\d+)(\D*)/', $route_number, $matches);
	if(is_numeric($matches[1]) && strlen($matches[1]) == 3) {
		return "{$matches[1][0]} {$matches[1][1]}{$matches[1][2]}{$matches[2]}";
	}
	//Are there four digit route numbers?

	return $route_number;
}

/**
 * Adds spaces between each character of a string.  # is replaced with the word "pound"
 * When read by text to speech, this becomes "1 2 3 4" instead of "one thousand two 
 * hundred thirty four"
 *
 * @param mixed $number The digits
 *
 * @return string A string with spaces between each character
 */
function say_digits($number) {
	return str_replace('#', 'pound', implode(' ', str_split($number)));
}

/**
 * Fix various words in a stop name.  If local file stop_pronunciations.xml exists
 * those will be read as well.  By default, fixes things like "4th Ave E" to 
 * "4th Avenue East".  Also converts the string to valid XML
 *
 * @param string $nmw The stop name
 *
 * @return string The corrected name
 */
function say_stop($name) {
	global $STOP_REPLACEMENTS;
	$name = ' ' . str_replace('.', '', $name) . ' ';

	$replacements = [
	' / '    => ' and ',
	' - '    => ' ',
	' N '    => ' north ',
	' NE '   => ' Northeast ',
	' E '    => ' East ',
	' SE '   => ' Southeast ',
	' S '    => ' South ',
	' SW '   => ' Southwest ',
	' W '    => ' West ',
	' NW '   => ' Northwest ',
	' ave '  => ' Avenue ',
	' av '   => ' Avenue ',
	' st '   => ' Street ',
	' Dr '   => ' Drive ',
	' Rd '   => ' Road ',
	' Blvd ' => ' Boulevard ',
	' Pkwy ' => ' Parkway ',
	' Pl '   => ' Place ',
	' Ct '   => ' Court ',
	' Ln '   => ' Lane ',
	' Wy '   => ' Way ',
	' Pt '   => ' Point ',
	' Sq '   => ' Square ',
	' AcRd ' => ' Access Road ',
	' TC '   => ' Transit Center ',
	' STA '  => ' Station ',
	' Intl ' => ' International ',
	' P&R '  => ' Park & Ride ',
	' hwy '  => ' Highway ',
	' fwy '  => ' Freeway ',
	' Frwy ' => ' Freeway ',
	' lk '   => ' lake ',
	];

	if($STOP_REPLACEMENTS != NULL) {
		$replacements = $STOP_REPLACEMENTS;
	} elseif(is_file(CONFIG_DIR . "stop_pronunciations.xml")) {
		if(!is_readable(CONFIG_DIR . "stop_pronunciations.xml")) {
			error_log('Cannot open ' . CONFIG_DIR . "stop_pronunciations.xml");
		}
		$xml = simplexml_load_file(CONFIG_DIR . "stop_pronunciations.xml");
		foreach($xml->Pronunciation as $pronunciation) {
			$replacements[(string)$pronunciation->attributes()->for] = (string)$pronunciation;
		}
		$STOP_REPLACEMENTS = $replacements;
	}

	return htmlentities(ucwords(strtolower(str_ireplace(array_keys($replacements), array_values($replacements), $name))), ENT_XML1);
}

$HEADSIGN_REPLACEMENTS = NULL;
/**
 * Fix various words in a trip headsign.  If local file headsign_pronunciations.xml exists
 * those will be read as well.  If the file does not exist, no corrections are made (save
 * for converting the string to valid XML)
 *
 * @param string $name The headsign
 *
 * @return string The corrected string
 */
function say_headsign($name) {
	global $HEADSIGN_REPLACEMENTS;
	$replacements = [];

	if($HEADSIGN_REPLACEMENTS != NULL) {
		$replacements = $HEADSIGN_REPLACEMENTS;
	} elseif(is_file(CONFIG_DIR . "headsign_pronunciations.xml")) {
		if(!is_readable(CONFIG_DIR . "headsign_pronunciations.xml")) {
			error_log('Cannot open ' . CONFIG_DIR . "headsign_pronunciations.xml");
		}
		$xml = simplexml_load_file(CONFIG_DIR . "headsign_pronunciations.xml");
		foreach($xml->Pronunciation as $pronunciation) {
			$replacements[(string)$pronunciation->attributes()->for] = (string)$pronunciation;
		}
		$HEADSIGN_REPLACEMENTS = $replacements;
	}

	return htmlentities(ucwords(strtolower(str_ireplace(array_keys($replacements), array_values($replacements), $name))), ENT_XML1);
}

/**
 * Converts an agency name to valid XML.  Stub method for adding pronunciations later,
 * though that can already be modified with agency_modifications.xml
 *
 * @param string $name The agency name
 *
 * @return string The corrected agency name
 */
function say_agency($name) {
	return htmlentities(ucwords(strtolower($name)), ENT_XML1);
}

/**
 * Converts a number of seconds to a string for a text-to-speech engine.
 * Values under 60 seconds are left as-is; values between 1 minute and one
 * hour are converted to minutes; anything greater is converted to hours
 * and minutes. Minutes use the MINUTE_PRECISION constant to determine how
 * many, if any, decimal places will be used.
 *
 * @param mixed $raw_seconds The number of seconds
 *
 * @return string The string representing the number of $raw_seconds
 */
function say_seconds($raw_seconds) {
	$raw_seconds = abs($raw_seconds);
	if($raw_seconds < 60) {
		return $raw_seconds == 1 ? '1 second ' : round($raw_seconds) . ' seconds ';
	}

	$hourSeconds = $raw_seconds % 86400;
	$hours = floor($hourSeconds / 3600);

	$minuteSeconds = $hourSeconds % 3600;
	$minutes = floor($minuteSeconds / 60);

	$seconds = ceil($minuteSeconds % 60);

	$minutes += round($seconds / 60, MINUTE_PRECISION);

	return ($hours > 0 ? ($hours == 1 ? '1 hour ' : "$hours hours ") : NULL) . ($minutes > 0 ? ($minutes == 1 ? '1 minute ' : "$minutes minutes ") : NULL);
}

/**
 * Determines which phrase to use to describe the lateness of the passed
 * value. Positive number indicates the trip is running late and negative
 * indicates the trips is running early.
 *
 * @param mixed $number The number of seconds
 *
 * @return string The value "on time", "late", or "early"
 */
function say_lateness($scheduleDeviation) {
	return $scheduleDeviation == 0 ? 'on time' : (say_seconds($scheduleDeviation) . ($scheduleDeviation > 0 ? 'late' : 'early'));
}

/**
 * Returns the local stop cache file or builds it if it does not exist or cannot be read
 *
 * @param mixed $agency The ID of the agency operating the route
 * @param mixed $stop_number The stop number
 *
 * @return SimpleXMLElement A SimpleXMLElement representing the stop cache file
 */
function get_stop($stop_number) {
	if(is_file(CACHE_DIR . "stop_{$stop_number}.xml")) {
		if(!is_readable(CACHE_DIR . "stop_{$stop_number}.xml")) {
			error_log('Cannot open ' . CACHE_DIR . "stop_{$stop_number}.xml");
			return build_stop_cache($stop_number);
		}
		return simplexml_load_file(CACHE_DIR . "stop_{$stop_number}.xml");
	} else {
		return build_stop_cache($stop_number);
	}
}

/**
 * Builds a cache file of stop data.
 *
 * @param mixed $stop_number The stop number
 *
 * @return SimpleXMLElement A representation of the stop data
 */
function build_stop_cache($stop_number) {
	$agencies = get_agency_data();
	$temp = [];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	foreach($agencies->agency as $agency) {
		if($agency->SearchStop) {
			curl_setopt($ch, CURLOPT_URL, API_BASE . "where/stop/{$agency->attributes()->id}_{$stop_number}.xml?version=2&includeReferences=false&key=" . API_KEY);
			$temp_xml = simplexml_load_string(curl_exec($ch));
			if((int)$temp_xml->code == 200) {
				$temp[(string)$agency->attributes()->id] = ['name' => (string)$temp_xml->data->entry->name];
			}
		}
	}
	curl_close($ch);

	$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><agencies></agencies>");
	_array_to_xml($temp, $xml);

	if(is_writable(CACHE_DIR)) {
		file_put_contents(CACHE_DIR . "stop_{$stop_number}.xml", $xml->asXML());
	} else {
		error_log('Cache directory ' . CACHE_DIR . ' is not writable!');
	}

	return $xml;
}

/**
 * Queries the One Bus Away API for the arrivals at the specified stop
 *
 * @param mixed $agency The ID of the agency of the stop
 * @param mixed $number The stop ID
 *
 * @return array A multi-dimensional array of arrivals
 */
function get_stop_arrivals($agency, $number) {
	$ch = curl_init(API_BASE . "where/arrivals-and-departures-for-stop/{$agency}_$number.xml?version=2&key=" . API_KEY); //minutesAfter causes permission denied error
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$xml = simplexml_load_string(curl_exec($ch));
	curl_close($ch);

	if((int)$xml->code != 200) {
		return [];
	}

	$return = [];

	foreach($xml->data->entry->arrivalsAndDepartures->arrivalAndDeparture as $arrival) {
		$temp = [];
		if(isset($arrival->routeShortName) || isset($arrival->routeLongName)) {
			$temp['route_name'] = isset($arrival->routeShortName) && !empty($arrival->routeShortName) ? (string)$arrival->routeShortName : (string)$arrival->routeLongName;
		} else {
			$route = $xml->data->references->routes->xpath('route[id="' . $arrival->routeId . '"]')[0];
			$temp['route_name'] = isset($route->shortName) ? (string)$route->shortName : (string)$route->longName;
		}

		if(isset($arrival->tripHeadsign)) {
			$temp['headsign'] = (string)$arrival->tripHeadsign;
		} else {
			$trip = $xml->data->references->trips->xpath('trip[id="' . $arrival->tripId . '"]')[0];
			$temp['headsign'] = isset($arrival->tripHeadsign) ? (string)$arrival->tripHeadsign : (string)$trip->tripHeadsign;
		}

		if($arrival->predicted) {
			$predicted = true;
			$prediction = bcsub($arrival->predictedDepartureTime, $xml->currentTime, 5) / 1000;
		} else {
			$predicted = false;
			$prediction = bcsub($arrival->scheduledDepartureTime, $xml->currentTime, 5) / 1000;
		}
		$temp['now'] = (int)$xml->currentTime;

		if(isset($return[$temp['route_name'] . $temp['headsign']])) {
			if(count($return[$temp['route_name'] . $temp['headsign']]['arrivals']) >= MAX_DEPARTURES)
				continue;
			$return[$temp['route_name'] . $temp['headsign']]['arrivals'][] = ['predicted' => $predicted, 'prediction' => $prediction];
		} else {
			$temp['arrivals'][] = ['predicted' => $predicted, 'prediction' => $prediction];
			$return[$temp['route_name'] . $temp['headsign']] = $temp;
		}
	}
	return $return;
}

/**
 * Returns the local route list cache file or builds it if it does not exist or cannot be read
 *
 * @param mixed $route_number The route number
 *
 * @return SimpleXMLElement A SimpleXMLElement representing the route list cache file
 */
function get_routes($route_number) {
	if(is_file(CACHE_DIR . "route_{$route_number}.xml")) {
		if(!is_readable(CACHE_DIR . "route_{$route_number}.xml")) {
			error_log('Cannot open ' . CACHE_DIR . "route_{$route_number}.xml");
			return get_route_candidates($stop_number);
		}
		return simplexml_load_file(CACHE_DIR . "route_{$route_number}.xml");
	} else {
		return get_route_candidates($route_number);
	}
}

/**
 * Get all routes for all agencies
 *
 * @return SimpleXMLElement A representation of all routes in the system
 */
function build_all_route_cache() {
	$agencies = get_agency_data();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$routes = [];

	foreach($agencies->agency as $agency) {
		if(!$agency->SearchRoute)
			continue;

		curl_setopt($ch, CURLOPT_URL, API_BASE . "where/routes-for-agency/{$agency->attributes()->id}.xml?version=2&includeReferences=false&key=" . API_KEY);
		$agency_routes = simplexml_load_string(curl_exec($ch));
		if($agency_routes->code != 200)
			continue;

		foreach($agency_routes->data->list->route as $route) {
			$route_data = [];
			if(isset($route->shortName))
				$route_data['shortName'] = $route->shortName;
			if(isset($route->longName))
				$route_data['longName'] = $route->longName;
			if(isset($route->description))
				$route_data['description'] = $route->description;
			$routes[(string)$route->id] = $route_data;
		}
		$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><routes></routes>");
		_array_to_xml($routes, $xml, 'route');
	}
	curl_close($ch);

	if(is_writable(CACHE_DIR)) {
		file_put_contents(CACHE_DIR . "all_routes.xml", $xml->asXML());
	} else {
		error_log('Cache directory ' . CACHE_DIR . ' is not writable!');
	}

	return $xml;
}

/**
 * Get all routes that match the specified $route_number. This could be the route's shortName,
 * longName or description but NOT the id from the GTFS
 *
 * @param mixed $route_number The route number
 *
 * @return SimpleXMLElement A SimpleXMLElement with the list of routes
 */
function get_route_candidates($route_number) {
	if(is_file(CACHE_DIR . "all_routes.xml")) {
		if(!is_readable(CACHE_DIR . 'all_routes.xml')) {
			error_log('Cannot open ' . CACHE_DIR . 'all_routes.xml');
			$xml = build_all_route_cache();
		}
		$xml = simplexml_load_file(CACHE_DIR . 'all_routes.xml');
	} else {
		$xml = build_all_route_cache();
	}

	$route_candidates = $xml->xpath("//*[text() = '$route_number']/..");

	$agencies = get_agency_data();

	$temp = [];
	foreach($route_candidates as $candidate) {
		$agency_id = explode('_', $candidate->attributes()->id)[0];
		$temp[$agency_id] = ['name' => $agencies->xpath("//agency[@id='$agency_id']")[0]->name, 'routeId' => $candidate->attributes()->id];
	}


	$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><agencies></agencies>");
	_array_to_xml($temp, $xml);

	if(is_writable(CACHE_DIR)) {
		file_put_contents(CACHE_DIR . "route_{$route_number}.xml", $xml->asXML());
	} else {
		error_log('Cache directory ' . CACHE_DIR . ' is not writable!');
	}

	return $xml;
}

/**
 * Builds a cache file of route stop list data.
 *
 * @param mixed $agency The agency operating the route
 * @param mixed $route_id The ID of the route (from GTFS)
 *
 * @return SimpleXMLElement A representation of the route stop list data
 */
function build_route_stop_cache($agency_route_id) {
	$ch = curl_init(API_BASE . "where/stops-for-route/$agency_route_id.xml?version=2&includePolylines=false&key=" . API_KEY);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$xml = simplexml_load_string(curl_exec($ch));
	curl_close($ch);

	if((int)$xml->code != 200) {
		return [];
	}

	$name = NULL;
	$route_info = $xml->data->references->routes->xpath("route[id='$agency_route_id']")[0];
	if(isset($route_info->longName) && !empty($route_info->longName)) {
		$name = $route_info->longName;
	} elseif(isset($route_info->description) && !empty($route_info->description)) {
		$name = $route_info->description;
	}

	//Following assumes $xml->data->entry->stopGroupings->stopGrouping->type = direction  and $xml->data->entry->stopGroupings->stopGrouping->ordered = true

	$out_xml = '<?xml version="1.0"?><Route name="' . htmlspecialchars($name, ENT_XML1) . '">';
	foreach($xml->data->entry->stopGroupings->stopGrouping->stopGroups->stopGroup as $direction) {
		$out_xml .= '<Direction name="' . htmlspecialchars($direction->name->names->string[0], ENT_XML1) . '">';
		foreach($direction->stopIds->string as $stop) {
			$out_xml .= "<stop id=\"$stop\">" . htmlspecialchars($xml->data->references->stops->xpath("stop[id='$stop']")[0]->name, ENT_XML1) . '</stop>';
		}
		$out_xml .= '</Direction>';
	}
	$out_xml .= '</Route>';

	if(is_writable(CACHE_DIR)) {
		file_put_contents(CACHE_DIR . "route_stops_$agency_route_id.xml", $out_xml);
	} else {
		error_log('Cache directory ' . CACHE_DIR . ' is not writable!');
	}

	return simplexml_load_string($out_xml);
}

/**
 * Returns the local route stop list cache file or builds it if it does not exist or cannot be read
 *
 * @param mixed $agency_route The underscore-delimited agency/route number, e.g. 1_2 for agency 1 route ID 2
 *
 * @return SimpleXMLElement A SimpleXMLElement representing the route stop list cache file
 */
function get_route_stops($agency_route) {
	if(is_file(CACHE_DIR . "route_stops_{$agency_route}.xml")) {
		if(!is_readable(CACHE_DIR . "route_stops_{$agency_route}.xml")) {
			error_log('Cannot open ' . CACHE_DIR . "route_stops_{$agency_route}.xml");
			return build_route_stop_cache($agency_route);
		}
		return simplexml_load_file(CACHE_DIR . "route_stops_{$agency_route}.xml");
	} else {
		return build_route_stop_cache($agency_route);
	}
}

/**
 * Queries the OneBusAway API for the specified vehicle ID.  Check the "code" element of the returned
 * array to determine how to proceed.  Likely values are 200 (found, has data) or 404 (not found, not
 * operating, or some other reason)
 *
 * @param mixed $agency The ID of the agency operating the vehicle
 * @param mixed $number The vehicle number
 *
 * @return array An array of data for the specified vehicle
 */
function get_vehicle_info($agency, $number) {
	$ch = curl_init(API_BASE . "where/trip-for-vehicle/{$agency}_$number.xml?version=2&key=" . API_KEY);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$xml = simplexml_load_string(curl_exec($ch));
	curl_close($ch);

	$return['code'] = (int)$xml->code;

	if($return['code'] == 404) {
		return $return;
	}

	$return['status'] = $xml->data->entry->status;

	$return['trip'] = $xml->data->references->trips->xpath('trip[id="' . $return['status']->activeTripId . '"]')[0];

	$return['route'] = $xml->data->references->routes->xpath('route[id="' . $return['trip']->routeId . '"]')[0];

	$return['route_name'] = isset($return['route']->shortName) ? (string)$return['route']->shortName : (string)$return['route']->longName;

	$return['next_stop_name'] = isset($return['status']->nextStop) ? (string)$xml->data->references->stops->xpath('stop[id="' . $return['status']->nextStop . '"]')[0]->name : NULL;
	$return['next_stop_time'] = isset($return['status']->nextStopTimeOffset) ? (int)$return['status']->nextStopTimeOffset : NULL;
	$return['closest_stop_name'] = isset($return['status']->closestStop) ? (string)$xml->data->references->stops->xpath('stop[id="' . $return['status']->closestStop . '"]')[0]->name : NULL;
	$return['closest_stop_time'] = isset($return['status']->closestStopTimeOffset) ? (int)$return['status']->closestStopTimeOffset : NULL;

	return $return;
}

/**
 * Stub function to sort route and stop files by the order specified in agency_modifications
 *
 * @param SimpleXMLElement $simplexml Unsorted data
 *
 * @return SimpleXMLElement The sorted data
 */
function sort_by_agency($simplexml) {
	//TODO: Finish
	return $simplexml;
}

/**
 * Returns the local agency cache file or builds it if it does not exist or cannot be read
 *
 * @return SimpleXMLElement A SimpleXMLElement representing the agency cache file
 */
function get_agency_data() {
	if(is_file(CACHE_DIR . 'agencies.xml')) {
		if(!is_readable(CACHE_DIR . 'agencies.xml')) {
			error_log('Cannot open ' . CACHE_DIR . 'agencies.xml!');
			return build_agency_cache();
		}
		return simplexml_load_file(CACHE_DIR . 'agencies.xml');
	} else {
		return build_agency_cache();
	}
}

/**
 * Builds a cache file of agency data.  If agency_modifications.xml exists in the
 * config directory, it is used to override values returned by the API
 *
 * @return SimpleXMLElement A representation of the agency data
 */
function build_agency_cache() {
	$agencies = $order = $return = [];
	if(is_file(DOC_ROOT . 'config/agency_modifications.xml')) {
		$xml = simplexml_load_file(DOC_ROOT . 'config/agency_modifications.xml');
		foreach($xml->agency as $agency) {
			if(!isset($agency->attributes()->id) || strlen($agency->attributes()->id) == 0)
				continue;

			if(isset($agency->Ignore) && strtolower($agency->Ignore) == 'true')
				continue;

			if(isset($agency->Order))
				$order[(string)$agency->attributes()->id] = (int)$agency->Order;

			if(isset($agency->Name) && !empty($agency->Name))
				$agencies[(string)$agency->attributes()->id]['name'] = (string)$agency->Name;

			$agencies[(string)$agency->attributes()->id]['SearchRoute'  ] = isset($agency->SearchRoute  ) && strtolower($agency->SearchRoute  ) == 'false' ? 'false' : 'true';
			$agencies[(string)$agency->attributes()->id]['SearchStop'   ] = isset($agency->SearchStop   ) && strtolower($agency->SearchStop   ) == 'false' ? 'false' : 'true';
			$agencies[(string)$agency->attributes()->id]['SearchVehicle'] = isset($agency->SearchVehicle) && strtolower($agency->SearchVehicle) == 'false' ? 'false' : 'true';
		}
	}
	$xml = simplexml_load_file(API_BASE . 'where/agencies-with-coverage.xml?version=2&key=' . API_KEY);
	if(empty($order)) {
		$max = 0;
	} elseif(count($order) == 1) {
		$max = array_values($order)[0];
	} else {
		$max = max($order);
	}
	foreach($xml->data->references->agencies->agency as $agency) {
		if(isset($agencies[(string)$agency->id])) {
			$agencies[(string)$agency->id] = array_merge(['name' => (string)$agency->name], $agencies[(string)$agency->id]);
			if(!isset($order[(string)$agency->id]))
				$order[(string)$agency->id] = ++$max;
		} else {
			$agencies[(string)$agency->id] = ['name' => (string)$agency->name, 'SearchRoute' => 'true', 'SearchStop' => 'true', 'SearchVehicle' => 'true'];
			$order[(string)$agency->id] = ++$max;
		}
	}

	asort($order);
	foreach($order as $key => $unused) {
		$return[$key] = $agencies[$key];
		unset($agencies[$key]);
	}

	$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><agencies></agencies>");

	_array_to_xml($return, $xml);

	if(is_writable(CACHE_DIR)) {
		file_put_contents(CACHE_DIR . 'agencies.xml', $xml->asXML());
	} else {
		error_log('Cache directory ' . CACHE_DIR . ' is not writable!');
	}

	return $xml;
}

function _array_to_xml($array, &$xml, $child_name = 'agency') {
	foreach($array as $key => $value) {
		if(is_array($value)) {
			$subnode = $xml->addChild($child_name);
			$subnode->addAttribute('id', $key);
			_array_to_xml($value, $subnode);
		} else {
			$xml->addChild("$key", htmlspecialchars($value, ENT_XML1));
		}
	}
}