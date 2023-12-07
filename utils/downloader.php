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
function ensure_pmtiles($path) {
    if(!is_file($path)) {
        $os_arch = detect_os_arch();
        $os = $os_arch[0];
        $arch = $os_arch[1];
        $url = "";
        try {
            $zip = file_get_contents($url);
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
