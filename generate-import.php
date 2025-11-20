<?php
/**
 * Generate WP-CLI JSON import file for Omaha coffee shops
 *
 * This script:
 * 1. Reads coffee shop data from omaha-coffee-data.json
 * 2. Geocodes addresses using OpenStreetMap Nominatim API
 * 3. Infers neighborhoods from addresses
 * 4. Generates WP-CLI JSON format for import
 *
 * Usage: php generate-import.php
 */

// Load the coffee shop data
$json_data = file_get_contents(__DIR__ . '/omaha-coffee-data.json');
$coffee_shops = json_decode($json_data, true);

if (!$coffee_shops) {
    die("Error: Could not load coffee shop data.\n");
}

echo "Processing " . count($coffee_shops) . " coffee shops...\n\n";

// Function to geocode an address using Nominatim
function geocode_address($address) {
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'format' => 'json',
        'q' => $address,
        'limit' => 1
    ]);

    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: WordPress Coffee Directory\r\n"
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (empty($data)) {
        return null;
    }

    return [
        'lat' => $data[0]['lat'],
        'lng' => $data[0]['lon']
    ];
}

// Function to infer neighborhood from address
function infer_neighborhood($address) {
    // Common Omaha neighborhoods mapped to address patterns
    $neighborhoods = [
        'Old Market' => ['Jones St', 'Howard St', 'Farnam St.*6810[0-2]', 'Jackson St.*6810[0-2]', 'S 1[0-6]th St'],
        'Blackstone' => ['Farnam St.*6813', '39th.*Farnam', '38th.*Farnam', '40th.*Farnam'],
        'Dundee' => ['Underwood Ave', 'Dodge St.*6813[0-2]', 'Cass St.*6813[0-2]'],
        'Benson' => ['Maple St.*6810[4-5]', 'Military Ave.*6810[4-5]', '60th.*Maple', '61st.*Maple'],
        'Midtown' => ['Dodge St.*6810', 'Farnam St.*6810', 'Leavenworth St.*6810[0-5]', 'Cuming St', 'California St'],
        'Downtown' => ['Dodge St.*6810[0-2]', 'Farnam St.*6810[0-2]', 'Douglas St.*6810[0-2]', 'Capitol'],
        'Little Bohemia' => ['S 13th St.*6810[6-9]', 'S 14th St.*6810[6-9]'],
        'NoDo' => ['Millwork Ave', 'N 1[0-5]th St.*6810[2-3]'],
        'Gifford Park' => ['N 3[0-5]rd St.*6813[01]', 'Pacific St.*6813'],
        'Aksarben' => ['S.*6810[6-7]', 'Center St.*6810[6-7]'],
        'West Omaha' => ['1[34][0-9]th.*St', 'W Dodge Rd', 'Pacific St.*6811[4-6]', '15[0-9]th'],
        'Millard' => ['Q St.*6812[7-8]', '1[0-9][0-9]th St.*6812'],
        'Ralston' => ['Harrison St.*Ralston'],
        'Chalco' => ['Meadows Blvd', '144th.*6813[8-9]'],
        'Highlander' => ['N 30th St.*6811[0-1]']
    ];

    foreach ($neighborhoods as $neighborhood => $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $address)) {
                return strtolower(str_replace(' ', '-', $neighborhood));
            }
        }
    }

    // Default to generic based on ZIP
    if (preg_match('/6813[0-9]/', $address)) {
        return 'central-omaha';
    } elseif (preg_match('/6814[0-9]/', $address)) {
        return 'west-omaha';
    } elseif (preg_match('/6810[0-3]/', $address)) {
        return 'downtown';
    } elseif (preg_match('/6810[4-5]/', $address)) {
        return 'north-omaha';
    } elseif (preg_match('/6811[0-9]/', $address)) {
        return 'north-omaha';
    } elseif (preg_match('/6812[0-9]/', $address)) {
        return 'south-omaha';
    }

    return 'omaha';
}

// Process each coffee shop
$wp_posts = [];
$rate_limit_delay = 1; // 1 second between API calls to be respectful

foreach ($coffee_shops as $index => $shop) {
    echo "Processing: {$shop['name']}...\n";

    // Geocode the address
    $coords = geocode_address($shop['address']);

    if ($coords) {
        echo "  ✓ Geocoded: {$coords['lat']}, {$coords['lng']}\n";
    } else {
        echo "  ✗ Geocoding failed\n";
        $coords = ['lat' => '', 'lng' => ''];
    }

    // Infer neighborhood
    $neighborhood = infer_neighborhood($shop['address']);
    echo "  ✓ Neighborhood: {$neighborhood}\n";

    // Build meta fields for opening hours
    $meta = [
        '_ocd_address' => $shop['address'],
        '_ocd_neighborhood' => $neighborhood,
        '_ocd_wifi' => $shop['wifi'] ? '1' : '0',
        '_ocd_drive_thru' => $shop['drive_thru'] ? '1' : '0',
        '_ocd_latitude' => $coords['lat'],
        '_ocd_longitude' => $coords['lng'],
        '_ocd_website' => $shop['website']
    ];

    // Add opening hours
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $day) {
        if (isset($shop['hours'][$day]) && $shop['hours'][$day] !== null) {
            $meta["_ocd_hours_{$day}_open"] = $shop['hours'][$day]['open'];
            $meta["_ocd_hours_{$day}_close"] = $shop['hours'][$day]['close'];
        }
    }

    // Create WP-CLI post object
    $wp_posts[] = [
        'post_type' => 'coffee_shop',
        'post_title' => $shop['name'],
        'post_status' => 'publish',
        'post_content' => '',
        'tax_input' => [
            'neighborhood' => [$neighborhood]
        ],
        'meta_input' => $meta
    ];

    // Rate limiting
    if ($index < count($coffee_shops) - 1) {
        sleep($rate_limit_delay);
    }

    echo "\n";
}

// Generate the WP-CLI JSON file
$output_file = __DIR__ . '/coffee-shops-import.json';
file_put_contents($output_file, json_encode($wp_posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "=================================\n";
echo "✓ Successfully processed " . count($wp_posts) . " coffee shops\n";
echo "✓ Output file: {$output_file}\n";
echo "=================================\n\n";
echo "To import, run:\n";
echo "wp post generate --format=json --from-file=wp-content/plugins/coffee-shop-directory/coffee-shops-import.json\n\n";
echo "Or use the REST API import script (coming next).\n";

?>