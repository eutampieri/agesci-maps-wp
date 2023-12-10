<?php
function get_regions() {
	$folder = dirname(__FILE__)."/regioni";
	$result = [];
	foreach(glob($folder."/*.json") as $file) {
		$name = pathinfo($file, PATHINFO_FILENAME);
		$geojson = file_get_contents($file);
		$value = $geogjson["features"][0]["properties"]["reg_name"];
		$result[$name] = $value;
	}
	return $result;
}

