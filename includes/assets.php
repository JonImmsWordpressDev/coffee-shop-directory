<?php

/**
 * Enqueue scripts and styles for the Coffee Shop Directory plugin.
 *
 * @package Coffee_Shop_Directory
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('ocd-ajax-filter', OCD_URL . 'js/filter.js', ['jquery'], filemtime(OCD_PATH . 'js/filter.js'), true);
    wp_localize_script('ocd-ajax-filter', 'ocd_ajax_obj', ['ajax_url' => admin_url('admin-ajax.php'),]);

    wp_enqueue_script('ocd-theme-mode', OCD_URL . 'js/theme-mode.js', [], filemtime(OCD_PATH . 'js/theme-mode.js'), true);

    wp_enqueue_style('ocd-styles', OCD_URL . 'css/style.css', [], filemtime(OCD_PATH . 'css/style.css'));

    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);

    wp_enqueue_style('phosphor-icons', 'https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css');
});

add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_script('ocd-editor', OCD_URL . 'js/editor.js', ['wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-plugins'], filemtime(OCD_PATH . 'js/editor.js'), true);
    wp_enqueue_script('ocd-map-block',OCD_URL . 'blocks/map-filter/index.js', ['wp-blocks', 'wp-element'], filemtime(OCD_PATH . 'blocks/map-filter/index.js'),true);
});
