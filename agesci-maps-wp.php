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

add_filter( 'the_content', 'agesci_maps_append_to_post', 1 );
wp_enqueue_style("agesci_maps_maplibre_style", "https://cdn.jsdelivr.net/npm/maplibre-gl@3.5.1/dist/maplibre-gl.min.css");

function agesci_maps_get_map_single_location($lat, $lon) {
    wp_enqueue_script("agesci_maps_maplibre", "https://cdn.jsdelivr.net/npm/maplibre-gl@3.5.1/dist/maplibre-gl.min.js");
    wp_enqueue_script("agesci_maps_pmtiles", "https://cdn.jsdelivr.net/npm/pmtiles@2.11.0/dist/index.min.js");
    return "<div id=\"map\" style=\"height: 400px;\"></div>".'
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
        let position = '.json_encode([$lon, $lat]).';
        let marker = new maplibregl.Marker()
            .setLngLat(position)
            .addTo(myMap);
        myMap.flyTo({center: position, zoom: 9});

      })
    })});
  </script>
    ';
}

function agesci_maps_append_to_post( $content ) {

    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        $meta = get_post_meta(get_post()->ID);
        if(isset($meta["geo_latitude"])) {
            $lat = floatval($meta["geo_latitude"][0]);
            $lon = floatval($meta["geo_longitude"][0]);
            return $content . agesci_maps_get_map_single_location($lat, $lon);
        }
    }

    return $content;
}