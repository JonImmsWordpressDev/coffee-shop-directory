<?php

// Reusable helper to check if a shop is open right now
function ocd_is_open_now($post_id) {
    $day = strtolower(current_time('l')); // e.g. 'monday'

    // Get current date + time in local timezone
    $current_time_str = current_time('Y-m-d H:i');
    $now = strtotime($current_time_str);

    // Use "same" hours if defined
    $range = get_post_meta($post_id, '_ocd_hours_same', true);
    if ($range && preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $range, $m)) {
        $start = strtotime(current_time('Y-m-d') . " {$m[1]}:{$m[2]}");
        $end   = strtotime(current_time('Y-m-d') . " {$m[3]}:{$m[4]}");
        return ($now >= $start && $now <= $end);
    }

    // Otherwise check per-day times
    $open  = get_post_meta($post_id, "_ocd_hours_{$day}_open", true);
    $close = get_post_meta($post_id, "_ocd_hours_{$day}_close", true);

    if ($open && $close) {
        $start = strtotime(current_time('Y-m-d') . " $open");
        $end   = strtotime(current_time('Y-m-d') . " $close");
        return ($now >= $start && $now <= $end);
    }

    return false;
}

// Get dropdown options for neighborhood filter
function wp_get_neighborhood_options() {
    $terms = get_terms(['taxonomy' => 'neighborhood', 'hide_empty' => false]);
    $options = '';

    foreach ($terms as $term) {
        $options .= '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
    }

    return $options;
}
