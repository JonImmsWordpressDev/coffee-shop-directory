<?php
/**
 * WP-CLI Import Script for Coffee Shops
 *
 * Usage: wp eval-file wp-content/plugins/coffee-shop-directory/wp-cli-import.php
 */

// Load the import data
$import_file = __DIR__ . '/coffee-shops-import.json';

if (!file_exists($import_file)) {
    WP_CLI::error("Import file not found: {$import_file}");
}

$coffee_shops = json_decode(file_get_contents($import_file), true);

if (!$coffee_shops) {
    WP_CLI::error("Could not parse import file.");
}

WP_CLI::line("Found " . count($coffee_shops) . " coffee shops to import.");
WP_CLI::line("");

$imported = 0;
$failed = 0;

$progress = \WP_CLI\Utils\make_progress_bar('Importing coffee shops', count($coffee_shops));

foreach ($coffee_shops as $shop_data) {
    // Insert the post
    $post_id = wp_insert_post([
        'post_type' => $shop_data['post_type'],
        'post_title' => $shop_data['post_title'],
        'post_status' => $shop_data['post_status'],
        'post_content' => $shop_data['post_content'],
    ], true);

    if (is_wp_error($post_id)) {
        WP_CLI::warning("Failed to import: {$shop_data['post_title']} - " . $post_id->get_error_message());
        $failed++;
        $progress->tick();
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
            $term_label = ucwords(str_replace('-', ' ', $neighborhood_slug));
            $term = wp_insert_term($term_label, 'neighborhood', [
                'slug' => $neighborhood_slug
            ]);
        }

        if (!is_wp_error($term)) {
            wp_set_object_terms($post_id, (int)$term['term_id'], 'neighborhood');
        }
    }

    $imported++;
    $progress->tick();
}

$progress->finish();

WP_CLI::line("");
WP_CLI::success("Imported {$imported} coffee shops.");

if ($failed > 0) {
    WP_CLI::warning("{$failed} shops failed to import.");
}

// Flush rewrite rules
flush_rewrite_rules();
WP_CLI::line("Rewrite rules flushed.");
