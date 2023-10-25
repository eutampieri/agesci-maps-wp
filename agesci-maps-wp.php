<?php
/**
 * Plugin Name: AGESCI Maps
 * Plugin URI: https://github.com/eutampieri/agesci-maps-wp
 * Description: Adds Location to WordPress
 * Version: 0.1
 * Requires at least: 4.9
 * Requires PHP: 7.0
 * Author: Eugenio Tampieri
 * Author URI: https://github.com/eutampieri
 *
 */

remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'agesci_maps_append_to_post', 1 );
wp_enqueue_style("agesci_maps_maplibre_style", "https://cdn.jsdelivr.net/npm/maplibre-gl@3.5.1/dist/maplibre-gl.min.css");

function agesci_maps_get_map_markers($locs) {
    wp_enqueue_script("agesci_maps_maplibre", "https://cdn.jsdelivr.net/npm/maplibre-gl@3.5.1/dist/maplibre-gl.min.js");
    wp_enqueue_script("agesci_maps_pmtiles", "https://cdn.jsdelivr.net/npm/pmtiles@2.11.0/dist/index.min.js");
    return "<p><strong>Mappa</strong></p><div id=\"map\" style=\"height: 400px;\"></div>".'
  <script>
    window.addEventListener("load", function() {
    let mode = "light";
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      // mode = "dark";
    }
    const protocol = new pmtiles.Protocol();
    maplibregl.addProtocol("pmtiles", protocol.tile);
    fetch(`${location.protocol}//${location.host}/wp-content/uploads/style.json`).then(x => x.json()).then(style => {
      const myMap = new maplibregl.Map({
        //hash: true,
        container: "map",
        style: {
          version: 8,
          glyphs: `${location.protocol}//${location.host}/wp-content/uploads/fonts/{fontstack}/{range}.pbf`,
          sources: {
            protomaps: {
              type: "vector",
              url: `pmtiles://${location.protocol}//${location.host}/wp-content/uploads/emiro.pmtiles`,
              attribution:
                \'<a href="https://protomaps.com">Protomaps</a> © <a href="https://openstreetmap.org">OpenStreetMap</a>\',
            },
          },
          layers: style,
        },
      });
      myMap.on("load", () => {
        const myBounds = myMap.getSource("protomaps").bounds;
        //myMap.fitBounds(myBounds);
        let nav = new maplibregl.NavigationControl();
        myMap.addControl(nav, "bottom-right");
        let mapData = '.json_encode($locs).';
        let positions = mapData.positions;
        for(const position of positions) {
            let marker = new maplibregl.Marker().setLngLat(position.coords);
            if(position.popup !== undefined) {
                marker.setPopup(new maplibregl.Popup().setHTML(position.popup));
            }
            marker.addTo(myMap);
        }
        if(positions.length == 1) {
            myMap.flyTo({center: positions[0].coords, zoom: 9});
        } else {
            myMap.fitBounds(new maplibregl.LngLatBounds(mapData.bbox));
        }
      })
    })});
  </script>
    ';
}

function agesci_maps_append_to_post( $content ) {
    global $wpdb;

    $postID = get_post()->ID;
    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        $meta = get_post_meta($postID);
        if(isset($meta["geo_latitude"])) {
            $lat = floatval($meta["geo_latitude"][0]);
            $lon = floatval($meta["geo_longitude"][0]);
            return wpautop($content) . agesci_maps_get_map_markers(["positions" => [["coords" => [$lon, $lat]]]]);
        }
    }
    $query = $wpdb->prepare("SELECT p.ID, p.post_title, m.meta_key, m.meta_value FROM {$wpdb->prefix}posts p, {$wpdb->prefix}postmeta m WHERE p.post_parent = %d AND p.ID = m.post_id AND (m.meta_key = \"geo_latitude\" OR m.meta_key = \"geo_longitude\") ORDER BY p.ID, m.meta_key", $postID);
    $results = $wpdb->get_results($query);
    if(count($results) > 0) {
        $markers = [];
        foreach ($results as $result) {
            if(!isset($markers[$result->ID])) {
                $markers[$result->ID] = ["coords" => [null, null], "popup" => "<p><a href=\"/?page_id=".$result->ID."\">".$result->post_title."</a></p>"];
            }
            if($result->meta_key == "geo_longitude") {
                $markers[$result->ID]["coords"][0] = floatval($result->meta_value);
            } else if($result->meta_key == "geo_latitude") {
                $markers[$result->ID]["coords"][1] = floatval($result->meta_value);
            }
        }
        $points = array_values($markers);
        return wpautop($content) . agesci_maps_get_map_markers(["positions" => $points, "bbox" => [
            [max(array_map(fn($value): float => $value["coords"][0], $points)) + 0.25, min(array_map(fn($value): float => $value["coords"][1], $points)) - 0.25],
            [min(array_map(fn($value): float => $value["coords"][0], $points)) - 0.25, max(array_map(fn($value): float => $value["coords"][1], $points)) + 0.25]
        ]]);
    }

    return wpautop($content);
}