<?php
// Handles FSE template injection and sync

add_filter('block_template_paths', function ($paths) {
    $paths[] = OCD_PATH . 'block-templates/';
    return $paths;
});

// Make templates visible in the editor
add_filter('get_block_templates', function ($templates, $query, $template_type) {
    if ($template_type !== 'wp_template') return $templates;

    $template_files = glob(OCD_PATH . 'block-templates/*.html');
    foreach ($template_files as $file) {
        $slug = basename($file, '.html');
        [$type, $post_type] = explode('-', $slug, 2);
        $templates[] = (object)[
            'slug' => $post_type,
            'source' => 'plugin',
            'type' => $type,
            'theme' => wp_get_theme()->get_stylesheet(),
            'title' => ucfirst($post_type),
            'content' => file_get_contents($file),
        ];
    }
    return $templates;
}, 10, 3);

// Ensure templates are created for the active theme if missing
add_action('init', function () {
    $theme = wp_get_theme()->get_stylesheet();
    $template_files = glob(OCD_PATH . 'block-templates/*.html');

    foreach ($template_files as $file) {
        $slug = basename($file, '.html'); // e.g. archive-coffee_shop
        [$type, $post_type] = explode('-', $slug, 2);

        $exists = get_page_by_path($slug, OBJECT, 'wp_template');
        if (!$exists) {
            wp_insert_post([
                'post_title'   => ucfirst($type) . ': ' . str_replace('_', ' ', $post_type),
                'post_name'    => $slug,
                'post_type'    => 'wp_template',
                'post_status'  => 'publish',
                'post_content' => file_get_contents($file),
                'tax_input'    => ['wp_theme' => [$theme]],
                'meta_input'   => ['origin' => 'plugin'],
            ]);
        }
    }
});
