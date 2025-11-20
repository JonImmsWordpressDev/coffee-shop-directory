# ☕ Coffee Shop Directory

A modern, block-based WordPress plugin for creating a beautiful, filterable directory of coffee shops — complete with maps, opening hours, "open now" logic, and ZIP-based radius filtering.

## 🔥 Features

- Custom Post Type: `coffee_shop`
- Custom Taxonomy: `neighborhood`
- Custom Gutenberg block: `Map & Filters`
- Interactive map using Leaflet
- AJAX filtering (neighborhood, WiFi, drive-thru, open now, ZIP radius)
- Admin meta panel for entering all shop details
- Distance calculation with ZIP-based proximity search
- Full compatibility with Full Site Editing (FSE)
- Weekly opening hours support (per-day or same every day)
- Live “Open Now” status with time zone handling
- SCSS-powered styles and utility classes
- Responsive design out of the box
- Directions link to Google Maps
- Ready for GitHub and WP Plugin Directory

---

## 🧩 Block Editor Support

The plugin includes a custom block: `ocd/map-filter`  
Add it to any block template or post/page to show the filter + map.

Templates provided:

- `archive-coffee_shop`
- `single-coffee_shop`

These are auto-registered and visible under **Appearance → Editor → Templates**.

---

## 🧰 Custom Fields (Meta)

Each Coffee Shop post supports:

- Address (auto-geocoded)
- Latitude / Longitude
- Website URL
- WiFi toggle
- Drive-thru toggle
- Weekly Opening Times (or same hours for all days)
- Neighborhood (via custom taxonomy)

All fields are managed through the **Gutenberg sidebar panel**:  
🛠 **Coffee Shop Details**

---

## 📍 AJAX Filtering

Supports:

- Neighborhood select
- WiFi / Drive-thru toggles
- "Open Now" checkbox
- ZIP code input
- Radius (miles) select

Filter results update the:

- Coffee shop cards
- Map markers (via Leaflet.js)

---

## 📦 Installation

1. Clone this repo to your plugins folder:

    ```bash
    git clone https://github.com/JonImmsWordpressDev/coffee-shop-directory.git wp-content/plugins/coffee-shop-directory
    ```

2. Activate the plugin in **Plugins → Installed Plugins**

3. Visit **Coffee Shops** in the admin menu and start adding shops.

---

## 📥 Import Tools

The plugin includes three methods for importing coffee shop data:

### 1. WP-CLI Import (Recommended)

Fast, reliable import using WP-CLI:

```bash
wp eval-file wp-content/plugins/coffee-shop-directory/wp-cli-import.php
```

Requires a JSON file at `coffee-shops-import.json` with the proper format.

### 2. Generate Import Script

Create a geocoded import file from raw coffee shop data:

```bash
php wp-content/plugins/coffee-shop-directory/generate-import.php
```

This script:
- Reads coffee shop data from a source JSON file
- Geocodes addresses using OpenStreetMap Nominatim API
- Infers neighborhoods from addresses
- Generates a WP-CLI compatible JSON import file

### 3. Programmatic Import

Alternative import method via PHP (no WP-CLI needed):

```bash
php wp-content/plugins/coffee-shop-directory/import-via-code.php
```

Or visit the file directly in your browser (requires admin permissions).

**Import File Format:**

```json
[
  {
    "post_type": "coffee_shop",
    "post_title": "Shop Name",
    "post_status": "publish",
    "post_content": "",
    "tax_input": {
      "neighborhood": ["neighborhood-slug"]
    },
    "meta_input": {
      "_ocd_address": "123 Main St, City, State 12345",
      "_ocd_latitude": "41.2565",
      "_ocd_longitude": "-95.9345",
      "_ocd_wifi": "1",
      "_ocd_drive_thru": "0",
      "_ocd_website": "https://example.com",
      "_ocd_hours_monday_open": "07:00",
      "_ocd_hours_monday_close": "18:00"
    }
  }
]
```

See `IMPORT-INSTRUCTIONS.md` for detailed documentation.

---

## 🛠 Development

### SCSS

SCSS files are located in `/scss`. To compile:

    npm install
    npm run watch

Or manually:

    sass --watch scss/style.scss css/style.css

### JS

- `/js/filter.js` — handles AJAX filter + map markers  
- `/js/editor.js` — meta panel in the post editor  
- `/js/theme-mode.js` — detects system theme preference

---

## 🚀 Roadmap

- Saved favorites
- Pagination UI
- Better drag/drop ordering
- ✅ Custom post importer
- Category-level stats
- Premium version?

---

## 🧑‍💻 Author

Built with ☕ and ❤️ by [Jon Imms](https://jonimms.com)

---

## 📄 License

MIT — free to use, modify, and share.
