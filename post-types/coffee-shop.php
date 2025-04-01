<?php
add_action('init', function () {
    register_post_type('coffee_shop', [
        'labels' => [
            'name' => 'Coffee Shops',
            'singular_name' => 'Coffee Shop',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'coffee-shop'],
        'show_in_rest'  => true,
        'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_menu'  => true,
    ]);
});
