# Coffee Shop Data Import Instructions

This directory contains **27 verified Omaha-area coffee shops** with complete data ready for import.

## Files Generated

1. **omaha-coffee-data.json** - Source data with hours, addresses, and amenities
2. **coffee-shops-import.json** - WP-CLI formatted import file (RECOMMENDED)
3. **generate-import.php** - Script used to geocode and generate import files
4. **import-via-code.php** - Alternative PHP script for programmatic import

## Data Collected

Each coffee shop includes:
- ✓ **Name** and **Address**
- ✓ **Latitude/Longitude** (geocoded via OpenStreetMap Nominatim API)
- ✓ **Neighborhood** (inferred from address patterns)
- ✓ **Website URL**
- ✓ **WiFi availability** (true/false)
- ✓ **Drive-thru availability** (true/false)
- ✓ **Opening hours** for Monday-Sunday

## Coffee Shops Included

### By Neighborhood

**Old Market (4 shops)**
- Archetype Coffee - Little Bohemia
- Hardy Coffee Co. - Old Market
- 13th Street Coffee & Tea
- Urban Abbey
- Howlin' Hounds Coffee

**Blackstone (2 shops)**
- Archetype Coffee - Blackstone
- Zen Coffee Company - Downtown

**Benson (2 shops)**
- Hardy Coffee Co. - Benson
- Edge of the Universe

**Dundee (2 shops)**
- Blue Line Coffee
- Myrtle & Cypress - Omaha Conservatory

**Midtown (4 shops)**
- The Mill on Leavenworth
- Amateur Coffee
- Astute Coffee - Downtown
- Astute Coffee - Atlas

**West Omaha (3 shops)**
- Stories Coffee - West Dodge
- Zen Coffee Company - Tiffany Plaza
- Zen Coffee Company - Central

**NoDo (1 shop)**
- Archetype Coffee - Millwork Commons

**And more in:** Gifford Park, Highlander, Chalco, Millard, Ralston, North Omaha

## Import Methods

### Method 1: WP-CLI (RECOMMENDED)

The fastest and most reliable method for bulk importing.

```bash
# Navigate to your WordPress root
cd /Users/jon.imms/Local\ Sites/omahacoffeeshops/app/public

# Import all coffee shops
wp post generate --format=json --from-file=wp-content/plugins/coffee-shop-directory/coffee-shops-import.json
```

**Advantages:**
- Single command imports all 27 shops
- Fast and efficient
- Easy to rollback if needed
- Preserves all meta fields and taxonomy terms

### Method 2: Programmatic PHP Import

Use the included PHP script for more control.

```bash
# From the plugin directory
php import-via-code.php
```

This will insert posts one-by-one using `wp_insert_post()` and show progress.

### Method 3: WordPress REST API

Use curl to import via the REST API (requires authentication):

```bash
# Example for one shop
curl -X POST http://your-site.local/wp-json/wp/v2/coffee_shop \
  -H "Content-Type: application/json" \
  -u username:password \
  -d @single-shop.json
```

## Verification After Import

After importing, verify the data:

```bash
# Count imported coffee shops
wp post list --post_type=coffee_shop --format=count

# List all imported shops
wp post list --post_type=coffee_shop --format=table

# Check a specific shop's meta
wp post meta list <POST_ID>

# View all neighborhoods
wp term list neighborhood --format=table
```

## Updating a Single Shop

If you need to update data for one shop:

```bash
# Update meta field
wp post meta update <POST_ID> _ocd_wifi 1

# Update opening hours
wp post meta update <POST_ID> _ocd_hours_monday_open "07:00"
wp post meta update <POST_ID> _ocd_hours_monday_close "17:00"
```

## Deleting All Imported Shops (Rollback)

If you need to start over:

```bash
# Delete all coffee_shop posts
wp post delete $(wp post list --post_type=coffee_shop --format=ids) --force

# Delete all neighborhood terms
wp term delete neighborhood $(wp term list neighborhood --format=ids --field=term_id)
```

## Neighborhoods Created

The import will create these neighborhood taxonomy terms:

- old-market
- blackstone
- benson
- dundee
- midtown
- nodo
- gifford-park
- highlander
- chalco
- millard
- ralston
- west-omaha
- north-omaha
- central-omaha
- south-omaha
- aksarben
- downtown

## Troubleshooting

### Issue: Geocoding Failed for Some Addresses

**Solution:** Only 1 out of 27 shops failed geocoding (Hardy Coffee Co. - Chalco). You can manually add coordinates:

```bash
wp post meta update <POST_ID> _ocd_latitude "41.1850"
wp post meta update <POST_ID> _ocd_longitude "-96.1440"
```

### Issue: Neighborhoods Not Showing

**Solution:** Make sure the neighborhood taxonomy is registered before import:

```bash
# Flush rewrite rules
wp rewrite flush
```

### Issue: Opening Hours Not Displaying

**Solution:** Verify meta fields are saved correctly:

```bash
wp post meta list <POST_ID> | grep hours
```

## Data Sources

All data collected from publicly available sources (as of November 2025):
- Official business websites
- Yelp listings
- Google Maps/Business listings
- Visit Omaha tourism site

## Next Steps

After successful import:

1. **Add Featured Images** - Upload coffee shop photos
2. **Add Descriptions** - Enhance `post_content` with shop descriptions
3. **Test the Map** - Visit the archive page and test filtering
4. **Customize Neighborhoods** - Adjust neighborhood assignments if needed
5. **Add More Shops** - Use the same format to add additional coffee shops

## Support

For issues with the import:
1. Check WordPress error logs
2. Verify plugin is activated
3. Ensure custom post type is registered (`coffee_shop`)
4. Verify user has proper permissions

## Regenerating Import File

If you need to regenerate the import file with updated data:

```bash
# Edit omaha-coffee-data.json with your changes
# Then run:
php generate-import.php
```

This will re-geocode addresses and regenerate `coffee-shops-import.json`.
