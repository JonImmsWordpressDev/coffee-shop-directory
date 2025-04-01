<?php
add_action('init', function () {
    $fields = [
        '_ocd_address',
        '_ocd_neighborhood',
        '_ocd_wifi',
        '_ocd_drive_thru',
        '_ocd_latitude',
        '_ocd_longitude',
        '_ocd_website',
        '_ocd_hours_same',
        '_ocd_hours_monday',
        '_ocd_hours_tuesday',
        '_ocd_hours_wednesday',
        '_ocd_hours_thursday',
        '_ocd_hours_friday',
        '_ocd_hours_saturday',
        '_ocd_hours_sunday',
    ];

    foreach ($fields as $field) {
        register_post_meta('coffee_shop', $field, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    // Register proper time fields
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    foreach ($days as $day) {
        register_post_meta('coffee_shop', "_ocd_hours_{$day}_open", [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
        register_post_meta('coffee_shop', "_ocd_hours_{$day}_close", [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});
register_rest_field('coffee_shop', 'is_open_now', [
    'get_callback' => function ($post_arr) {
        return apply_filters('ocd_is_open_now', $post_arr['id']);
    },
    'schema' => [
        'type' => 'boolean',
        'description' => 'Whether the coffee shop is currently open',
        'context' => ['view', 'edit'],
    ],
]);
