<article class="coffee-card">
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

    <?php
    $terms = get_the_terms(get_the_ID(), 'neighborhood');
    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<p><i class="ph ph-map-pin"></i> ' . esc_html($terms[0]->name) . '</p>';
    }
    ?>

    <p><i class="ph ph-wifi-high"></i>
        <?= get_post_meta(get_the_ID(), '_ocd_wifi', true) === '1' ? 'WiFi Available' : 'No WiFi'; ?>
    </p>

    <p><i class="ph ph-car-profile"></i>
        <?= get_post_meta(get_the_ID(), '_ocd_drive_thru', true) === '1' ? 'Drive-Thru' : 'No Drive-Thru'; ?>
    </p>

    <?php
    $website = get_post_meta(get_the_ID(), '_ocd_website', true);
    if ($website) {
        echo '<p><i class="ph ph-globe"></i> <a href="' . esc_url($website) . '" target="_blank" rel="noopener">' . parse_url($website, PHP_URL_HOST) . '</a></p>';
    }

    $lat = get_post_meta(get_the_ID(), '_ocd_latitude', true);
    $lng = get_post_meta(get_the_ID(), '_ocd_longitude', true);

    if ($lat && $lng) {
        $title = urlencode(get_the_title());
        $directions_url = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}&destination_place_id={$title}";

        echo '<p><i class="ph ph-navigation-arrow"></i> <a href="' . esc_url($directions_url) . '" target="_blank" rel="noopener">Get Directions</a></p>';
    }

    // Grab distance for this card if available
    global $distance;
    $distance_miles = is_numeric($distance) ? round($distance, 1) : null;
    $distance_display = $distance_miles !== null ? " – {$distance_miles} miles away" : '';

    // Determine open/closed status
    $day = strtolower(current_time('l'));
    $now = strtotime(current_time('H:i'));
    $open = get_post_meta(get_the_ID(), "_ocd_hours_{$day}_open", true);
    $close = get_post_meta(get_the_ID(), "_ocd_hours_{$day}_close", true);

    $is_open = false;
    if ($open && $close) {
        $start = strtotime($open);
        $end = strtotime($close);
        $is_open = ($now >= $start && $now <= $end);
    }

    echo $is_open
        ? "<span class='badge badge-open'>Open Now{$distance_display}</span>"
        : "<span class='badge badge-closed'>Closed{$distance_display}</span>";

    echo '<ul class="opening-hours">';
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $today = strtolower(current_time('l'));
    foreach ($days as $d) {
        $slug = strtolower($d);
        $open_time = get_post_meta(get_the_ID(), "_ocd_hours_{$slug}_open", true);
        $close_time = get_post_meta(get_the_ID(), "_ocd_hours_{$slug}_close", true);

        $time_str = ($open_time && $close_time)
            ? date('g:i A', strtotime($open_time)) . ' – ' . date('g:i A', strtotime($close_time))
            : '<span class="closed">Closed</span>';

        $highlight = $slug === $today ? 'today' : '';
        echo "<li class='{$highlight}'><i class='ph ph-clock'></i> <strong>{$d}:</strong> {$time_str}</li>";
    }
    echo '</ul>';
    ?>
</article>
