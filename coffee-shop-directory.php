<?php
/**
 * Plugin Name: Coffee Shop Directory
 * Description: A directory of coffee shops with maps, filtering, and full block support.
 * Version: 1.0
 * Author: Jon Imms
 * Author URI: https://jonimms.com
 */

define('OCD_PATH', plugin_dir_path(__FILE__));
define('OCD_URL', plugin_dir_url(__FILE__));

// Load all core components
foreach (glob(OCD_PATH . 'includes/*.php') as $file) {
    require_once $file;
}

// Register post types, meta fields, and taxonomies
foreach (['post-types', 'meta', 'taxonomies'] as $dir) {
    foreach (glob(OCD_PATH . "{$dir}/*.php") as $file) {
        require_once $file;
    }
}

// Flush rewrite rules on activation
register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
