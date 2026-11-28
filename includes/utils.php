<?php

// Opening range for one day as [open, close] in minutes after midnight, or null when closed.
// "Same hours" (e.g. "07:00 - 18:00") applies to every day and wins over the per-day fields.
function ocd_hours_range($post_id, $day) {
    $same = get_post_meta($post_id, '_ocd_hours_same', true);
    if ($same && preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $same, $m)) {
        return [(int) $m[1] * 60 + (int) $m[2], (int) $m[3] * 60 + (int) $m[4]];
    }

    $open  = ocd_time_to_minutes(get_post_meta($post_id, "_ocd_hours_{$day}_open", true));
    $close = ocd_time_to_minutes(get_post_meta($post_id, "_ocd_hours_{$day}_close", true));

    return ($open !== null && $close !== null) ? [$open, $close] : null;
}

// "HH:MM" (or "H:MM", optional seconds) to minutes after midnight, or null.
function ocd_time_to_minutes($time) {
    if (!is_string($time) || !preg_match('/^\s*(\d{1,2}):(\d{2})/', $time, $m)) {
        return null;
    }
    return (int) $m[1] * 60 + (int) $m[2];
}

// Pure check: is a shop with today's and yesterday's ranges open at $minutes after midnight?
// A range whose close is earlier than its open runs past midnight into the next day.
function ocd_is_open_at($today, $yesterday, $minutes) {
    if ($today) {
        [$open, $close] = $today;
        if ($open < $close && $minutes >= $open && $minutes < $close) {
            return true;
        }
        if ($close < $open && $minutes >= $open) {
            return true;
        }
    }

    if ($yesterday) {
        [$open, $close] = $yesterday;
        if ($close < $open && $minutes < $close) {
            return true;
        }
    }

    return false;
}

// Reusable helper to check if a shop is open right now (site timezone).
function ocd_is_open_now($post_id) {
    $now       = current_time('timestamp');
    $today     = strtolower(gmdate('l', $now));
    $yesterday = strtolower(gmdate('l', $now - DAY_IN_SECONDS));
    $minutes   = (int) gmdate('G', $now) * 60 + (int) gmdate('i', $now);

    return ocd_is_open_at(
        ocd_hours_range($post_id, $today),
        ocd_hours_range($post_id, $yesterday),
        $minutes
    );
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
