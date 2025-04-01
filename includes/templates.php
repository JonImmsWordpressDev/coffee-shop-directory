<?php

// Handles FSE template injection and sync

add_filter('block_template_paths', function ($paths) {
    $paths[] = OCD_PATH . 'block-templates/';
    return $paths;
});

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
