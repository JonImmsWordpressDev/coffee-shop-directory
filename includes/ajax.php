<?php

add_action('wp_ajax_ocd_filter_coffee_shops', 'ocd_handle_ajax_filter');
add_action('wp_ajax_nopriv_ocd_filter_coffee_shops', 'ocd_handle_ajax_filter');

function ocd_handle_ajax_filter() {
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $meta_query = [];
    $tax_query = [];

    if (!empty($_POST['wifi'])) {
        $meta_query[] = ['key' => '_ocd_wifi', 'value' => '1'];
    }

    if (!empty($_POST['drive_thru'])) {
        $meta_query[] = ['key' => '_ocd_drive_thru', 'value' => '1'];
    }

    if (!empty($_POST['neighborhood'])) {
        $tax_query[] = [
            'taxonomy' => 'neighborhood',
            'field' => 'slug',
            'terms' => sanitize_text_field($_POST['neighborhood']),
        ];
    }

    $zipcode = sanitize_text_field($_POST['zipcode'] ?? '');
    $radius = isset($_POST['radius']) ? floatval($_POST['radius']) : 0;

    $user_lat = $user_lng = null;
    $filtered_ids = [];

    // Step 1: Get ZIP coordinates if radius is specified
    if ($zipcode && $radius > 0) {
        $geo = ocd_get_lat_lng_for_zip($zipcode);

        if ($geo) {
            $user_lat = floatval($geo['lat']);
            $user_lng = floatval($geo['lng']);
        } else {
            error_log("❌ Failed to geocode ZIP: $zipcode");
        }
    }

    // Step 2: Query all shops matching basic filters
    $query_args = [
        'post_type' => 'coffee_shop',
        'posts_per_page' => 999,
        'meta_query' => $meta_query,
        'tax_query' => $tax_query,
    ];

    $query = new WP_Query($query_args);

    // Step 3: Filter by radius if ZIP was valid
    if ($user_lat !== null && $user_lng !== null) {
        foreach ($query->posts as $post) {
            $lat = get_post_meta($post->ID, '_ocd_latitude', true);
            $lng = get_post_meta($post->ID, '_ocd_longitude', true);

            if ($lat && $lng) {
                $distance = ocd_haversine_distance($user_lat, $user_lng, $lat, $lng, 'miles');
                if ($distance <= $radius) {
                    $filtered_ids[] = $post->ID;
                }
            }
        }
    } else {
        // No ZIP filter — include all
        $filtered_ids = wp_list_pluck($query->posts, 'ID');
    }
    if (empty($filtered_ids)) {
        wp_send_json([
            'html' => '<p>No coffee shops match your filters.</p>',
            'markers' => []
        ]);
    }

    wp_reset_postdata();

    // Step 4: Run paginated query using filtered IDs
    $paged_query = new WP_Query([
        'post_type' => 'coffee_shop',
        'posts_per_page' => 6,
        'paged' => $paged,
        'post__in' => $filtered_ids,
        'orderby' => 'post__in',
    ]);

    $response = ['html' => '', 'markers' => []];

    // The map shows every matching shop, not just this page. Load More
    // pages keep the markers drawn for page 1.
    if ($paged <= 1) {
        foreach ($filtered_ids as $shop_id) {
            $lat = get_post_meta($shop_id, '_ocd_latitude', true);
            $lng = get_post_meta($shop_id, '_ocd_longitude', true);

            if ($lat && $lng) {
                $response['markers'][] = [
                    'title' => html_entity_decode(wp_strip_all_tags(get_the_title($shop_id)), ENT_QUOTES, 'UTF-8'),
                    'lat' => $lat,
                    'lng' => $lng,
                    'url' => get_permalink($shop_id),
                ];
            }
        }
    }

    ob_start();
    if ($paged_query->have_posts()) {
        echo '<div class="coffee-grid">';
        while ($paged_query->have_posts()) {
            $paged_query->the_post();

            $lat = get_post_meta(get_the_ID(), '_ocd_latitude', true);
            $lng = get_post_meta(get_the_ID(), '_ocd_longitude', true);

// 🔥 Make $distance available in card.php
            global $distance;
            $distance = null;

            if ($user_lat !== null && $user_lng !== null && $lat && $lng) {
                $distance = ocd_haversine_distance($user_lat, $user_lng, $lat, $lng, 'miles');
            }

            include OCD_PATH . 'templates/card.php';
        }
        echo '</div>';

        if ($paged < $paged_query->max_num_pages) {
            echo '<button class="load-more" data-next-page="' . ($paged + 1) . '">Load More</button>';
        }
    } else {
        echo '<p>No coffee shops match your filters.</p>';
    }

    $response['html'] = ob_get_clean();
    wp_send_json($response);
}

// Geocode a US ZIP code (5 digits or ZIP+4). Results are cached, since
// Nominatim's usage policy asks for caching and at most 1 request per second.
function ocd_get_lat_lng_for_zip($zip) {
    if (!preg_match('/^(\d{5})(?:-\d{4})?$/', trim((string) $zip), $m)) {
        return null;
    }
    $zip = $m[1];
    $cache_key = 'ocd_zip_geo_' . $zip;

    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }
    if ($cached === 'miss') {
        return null;
    }

    $url = "https://nominatim.openstreetmap.org/search?format=json&country=us&postalcode=" . urlencode($zip);
    $response = wp_remote_get($url, ['user-agent' => 'Coffee Shop Directory (' . home_url('/') . ')']);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        // Upstream error or rate limit: back off briefly instead of retrying on every request.
        set_transient($cache_key, 'miss', 5 * MINUTE_IN_SECONDS);
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
        $geo = ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
        // ZIP centroids don't move.
        set_transient($cache_key, $geo, 30 * DAY_IN_SECONDS);
        return $geo;
    }

    set_transient($cache_key, 'miss', HOUR_IN_SECONDS);
    return null;
}

// Haversine distance in miles or km
function ocd_haversine_distance($lat1, $lon1, $lat2, $lon2, $unit = 'miles') {
    $radius = ($unit === 'miles') ? 3959 : 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $radius * $c;
}
