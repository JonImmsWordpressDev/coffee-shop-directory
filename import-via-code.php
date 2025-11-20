<?php
/**
 * Import coffee shops programmatically using wp_insert_post()
 *
 * This script provides an alternative to WP-CLI for importing coffee shops.
 * It loads the WordPress environment and inserts posts one-by-one.
 *
 * Usage:
 * 1. From terminal: php import-via-code.php
 * 2. Or visit: http://your-site.local/wp-content/plugins/coffee-shop-directory/import-via-code.php
 *
 * WARNING: This will create duplicate posts if run multiple times!
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';

if (!file_exists($wp_load_path)) {
    die("Error: Could not find WordPress. Expected at: {$wp_load_path}\n");
}

require_once $wp_load_path;

// Check if we're allowed to run this
if (!current_user_can('edit_posts') && php_sapi_name() !== 'cli') {
    die("Error: You don't have permission to import posts.\n");
}

// Load the import data
$import_file = __DIR__ . '/coffee-shops-import.json';

if (!file_exists($import_file)) {
    die("Error: Import file not found. Run generate-import.php first.\n");
}

$coffee_shops = json_decode(file_get_contents($import_file), true);

if (!$coffee_shops) {
    die("Error: Could not parse import file.\n");
}

echo "========================================\n";
echo "Coffee Shop Directory - Import Script\n";
echo "========================================\n\n";
echo "Found " . count($coffee_shops) . " coffee shops to import.\n";
echo "Starting import...\n\n";

$imported = 0;
$failed = 0;
$errors = [];

foreach ($coffee_shops as $index => $shop_data) {
    $shop_name = $shop_data['post_title'];
    echo "[" . ($index + 1) . "/" . count($coffee_shops) . "] Importing: {$shop_name}...";

    // Prepare post data
    $post_data = [
        'post_type' => $shop_data['post_type'],
        'post_title' => $shop_data['post_title'],
        'post_status' => $shop_data['post_status'],
        'post_content' => $shop_data['post_content'],
    ];

    // Insert the post
    $post_id = wp_insert_post($post_data, true);

    if (is_wp_error($post_id)) {
        echo " FAILED\n";
        $failed++;
        $errors[] = "  Error: " . $post_id->get_error_message();
        continue;
    }

    // Add meta fields
    if (isset($shop_data['meta_input'])) {
        foreach ($shop_data['meta_input'] as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }
    }

    // Add taxonomy terms
    if (isset($shop_data['tax_input']['neighborhood'])) {
        $neighborhood_slug = $shop_data['tax_input']['neighborhood'][0];

        // Create or get the term
        $term = term_exists($neighborhood_slug, 'neighborhood');

        if (!$term) {
            // Create the term with a nice label
            $term_label = ucwords(str_replace('-', ' ', $neighborhood_slug));
            $term = wp_insert_term($term_label, 'neighborhood', [
                'slug' => $neighborhood_slug
            ]);
        }

        if (!is_wp_error($term)) {
            wp_set_object_terms($post_id, (int)$term['term_id'], 'neighborhood');
        }
    }

    echo " SUCCESS (ID: {$post_id})\n";
    $imported++;
}

echo "\n========================================\n";
echo "Import Complete!\n";
echo "========================================\n";
echo "✓ Successfully imported: {$imported}\n";
echo "✗ Failed: {$failed}\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}

echo "\n";
echo "Next steps:\n";
echo "1. Visit /coffee-shop/ to see the archive\n";
echo "2. Check individual coffee shop pages\n";
echo "3. Test the map and filtering\n";
echo "4. Add featured images if desired\n";

// Flush rewrite rules to ensure URLs work
flush_rewrite_rules();

echo "\n✓ Rewrite rules flushed.\n";
