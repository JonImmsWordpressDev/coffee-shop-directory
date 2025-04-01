# ☕ Omaha Coffee Shop Directory

A professional-grade WordPress plugin for showcasing and filtering coffee shops in Omaha — with custom blocks, maps, radius search, and a beautiful UI. Fully block-based and built for Full Site Editing.

---

## 🚀 Features

- ✅ Gutenberg-compatible with custom block (`Map & Filters`)
- 🗺️ Leaflet.js-powered interactive map
- 🔍 AJAX filtering by:
  - Neighborhood
  - WiFi Available
  - Drive-Thru
  - “Open Now” status
  - ZIP code + Radius
- 📅 Opening hours (per day or same every day)
- ⏰ “Open Now” logic with local timezones
- 📍 Distance display (“2.5 miles away”)
- 🎨 Clean, responsive SCSS-based design
- 🌗 Automatic light/dark mode support
- 🧱 FSE-ready archive and single templates
- ⭐️ Future features: Favorites, Geolocation, Reviews

---

## 🧩 Custom Block: `Map & Filters`

- Inserts filter UI + map in any post, page, or template
- Register with `ocd/map-filter`
- Fully dynamic with AJAX-powered results

---

## 📦 Installation

1. Clone or download this plugin into your WordPress install:
git clone https://github.com/yourusername/coffee-shop-directory.git wp-content/plugins/coffee-shop-directory

2. Activate the plugin in **Plugins > Installed Plugins**

3. Optionally, use the provided FSE block templates:
   - `archive-coffee_shop.html`
   - `single-coffee_shop.html`

4. Go to **Coffee Shops > Add New** to start adding listings

5. Edit the archive template in the Site Editor and insert the `Map & Filters` block

---

## 🛠 Development

### SCSS

SCSS files are located in `/scss`. To compile:

npm install
npm run watch

 or

sass --watch scss/style.scss css/style.css

---

## 📍 Radius + ZIP Filtering

- Users can enter a ZIP code and radius (in miles)
- ZIPs are geocoded using OpenStreetMap Nominatim
- Haversine formula is used to calculate distance between ZIP and each coffee shop
- Listings are filtered and sorted accordingly
- Distance is displayed on each card (e.g. “3.2 miles away”)

---

## 🕰 Opening Hours

- You can set:
  - Same opening hours for all days **OR**
  - Specific open/close times per weekday
- Fields saved via custom post meta
- Admin UI uses `type="time"` inputs
- “Open Now” badge appears automatically if the current time is within today's hours

---

## 🧠 Smart Logic

- `ocd_is_open_now()` logic is reusable and hooked into filters
- Archive filtering uses AJAX + `wp_ajax` to avoid full page reloads
- Cards are rendered via `card.php` and dynamically inserted into DOM
- ZIP + distance logic logs info to error log for easy debugging

---

## 🌐 Localization

- Text domains and translation support will be added in future releases

---

## 📅 Roadmap

- [x] FSE support via `block-template` files
- [x] Radius filtering with ZIP + geocoding
- [x] “Open Now” logic and badge display
- [x] Leaflet maps with dynamic markers
- [x] Responsive SCSS layout + dark mode
- [x] Distance display per card
- [ ] Saved Favorites (via localStorage)
- [ ] “Open Now Near Me” filter using Geolocation
- [ ] Ratings, reviews, or comments per coffee shop
- [ ] Submit to WordPress plugin repo
- [ ] Convert to freemium or Pro version

---

## 👨‍💻 Author

**Jon Imms**  
WordPress Developer from Newcastle upon Tyne, now living in Omaha, Nebraska.  
🌐 https://jonimms.com

---

## 📖 License

MIT License  
Free for personal or commercial use. Attribution appreciated.

---

Enjoy your brew! ☕️
