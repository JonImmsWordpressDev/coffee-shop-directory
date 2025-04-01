<?php
add_action('init', function () {
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    foreach ($days as $day) {
        foreach (['_open', '_close'] as $suffix) {
            $key = "_ocd_hours_{$day}{$suffix}";

            register_post_meta('coffee_shop', $key, [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'string',
                        'format' => 'time',
                    ]
                ],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }
});
