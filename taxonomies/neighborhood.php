<?php

add_action('init', function () {
    register_taxonomy('neighborhood', ['coffee_shop'], [
        'labels' => [
            'name' => 'Neighborhoods',
            'singular_name' => 'Neighborhood',
            'menu_name' => 'Neighborhoods',
        ],
        'public' => true,
        'show_ui' => false,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'neighborhood'],
    ]);
});
