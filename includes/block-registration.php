<?php

// Registers custom Gutenberg blocks like map-filter

add_action('init', function () {
    register_block_type(OCD_PATH . 'blocks/map-filter', [
        'render_callback' => function ($attributes, $content, $block) {
            return '
<div class="map-filter-wrapper">
    <div id="coffee-map" style="height: 400px; margin-bottom: 1.5rem;"></div>

    <form method="POST" class="coffee-filter-form" id="ocd-filters">
        <label>
            Neighborhood:
            <select name="neighborhood">
                <option value="">All</option>'
                . wp_get_neighborhood_options() .
                '</select>
        </label>
        <label>
            ZIP Code:
            <input type="text" name="zipcode" placeholder="e.g. 68102">
        </label>
        
        <label>
            Radius (miles):
            <select name="radius">
                <option value="">Any</option>
                <option value="1">1 mile</option>
                <option value="3">3 miles</option>
                <option value="5">5 miles</option>
                <option value="10">10 miles</option>
                <option value="25">25 miles</option>
            </select>
        </label>


        <div class="checkbox-group">
          <input type="checkbox" name="wifi" value="1" id="wifi" />
          <label for="wifi">WiFi Available</label>
        </div>

        <div class="checkbox-group">
          <input type="checkbox" name="drive_thru" value="1" id="drive_thru" />
          <label for="drive_thru">Drive-Thru</label>
        </div>

        <button type="button" id="clear-filters">Clear</button>
    </form>

    <div id="ocd-results"></div>
</div>';

        }
    ]);
});
