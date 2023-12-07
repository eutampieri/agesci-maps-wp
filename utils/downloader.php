<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function detect_os_arch() {
	$os = PHP_OS;
	$arch = null;
	switch($os) {
	case "Linux":
		$uname_data = explode(" ", php_uname());
		$arch = $uname_data[count($uname_data) - 1];
	}
	return [$os, $arch];
}
function download_pmtiles($path) {
	$os_arch = detect_os_arch();
	$os = $os_arch[0];
	$arch = $os_arch[1];
	$url = "https://github.com/protomaps/go-pmtiles/releases/latest/download/go-pmtiles_1.11.1_".$os."_".$arch.".tar.gz";
	try {
		$zip = file_get_contents($url);

	} catch (\Throwable $th) {
		//throw $th;
	}
}

function ensure_pmtiles($path) {
	if(!is_file($path)) {
		download_pmtiles($path);
	} else {
		$version = explode(" ", exec($path." version"))[1];
		$new_version = json_decode(file_get_contents("https://api.github.com/repos/protomaps/go-pmtiles/releases/latest"), true)["name"];
		$new_version = str_replace("v", "", $new_version);
		if(version_compare($new_version, $version) > 0) {
			download_pmtiles($path);
		}
	}
}
