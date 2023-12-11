<?php
if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
}

function get_regions() {
	$folder = dirname(dirname(__FILE__))."/regioni";
	$result = [];
	foreach(glob($folder."/*.json") as $file) {
		$name = pathinfo($file, PATHINFO_FILENAME);
		$geojson = json_decode(file_get_contents($file), true);
		$value = $geojson["features"][0]["properties"]["reg_name"];
		$result[$name] = $value;
	}
	return $result;
}

