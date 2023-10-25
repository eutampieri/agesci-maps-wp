<?php
function detect_os_arch() {
    return ["Linux", "x86_64"];
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
echo exec("uname -a");