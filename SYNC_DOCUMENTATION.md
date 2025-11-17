# StarGazers API Sync Documentation

## Overview

The StarGazers plugin now includes a comprehensive API synchronization system that automatically fetches and stores data from multiple space and weather APIs. The sync system runs on WordPress cron schedules and can also be triggered manually.

## Features

- **Automated Scheduling**: All APIs sync automatically on configured schedules
- **Manual Sync**: Trigger syncs manually via admin interface or WP-CLI
- **Transient Caching**: Reduces API calls with intelligent caching
- **Error Logging**: Comprehensive logging system for debugging
- **Admin Dashboard**: Visual status dashboard for monitoring syncs
- **WP-CLI Support**: Command-line tools for automation

## Configured APIs

### 1. CME (Coronal Mass Ejection) Alerts
- **Schedule**: Hourly
- **Endpoint**: NASA DONKI API
- **Post Type**: `sgu_cme_alerts`
- **Cache Duration**: 1 hour
- **Settings**: Admin → SGU Weather → CME Alerts

### 2. Solar Flare Alerts
- **Schedule**: Hourly
- **Endpoint**: NASA DONKI API
- **Post Type**: `sgu_sf_alerts`
- **Cache Duration**: 1 hour
- **Settings**: Admin → SGU Weather → Solar Flare Alerts
- **Note**: Can share API keys with CME

### 3. Geomagnetic Alerts
- **Schedule**: Every 30 minutes
- **Endpoint**: NOAA Space Weather Prediction Center
- **Post Type**: `sgu_geo_alerts`
- **Cache Duration**: 30 minutes
- **Settings**: Admin → SGU Weather → Geomagnetic Alerts
- **Note**: No API key required

### 4. Space Weather Alerts
- **Schedule**: Every 30 minutes
- **Endpoint**: NOAA Space Weather Products
- **Post Type**: `sgu_sw_alerts`
- **Cache Duration**: 30 minutes
- **Settings**: Admin → SGU Weather → Space Weather Alerts
- **Note**: No API key required

### 5. Near Earth Objects (NEO)
- **Schedule**: Twice daily
- **Endpoint**: NASA NEO API
- **Post Type**: `sgu_neo`
- **Cache Duration**: 2 hours
- **Settings**: Admin → SGU Weather → Near Earth Objects
- **Date Range**: Fetches 7 days ahead
- **Note**: Can share API keys with CME

### 6. NASA Photo Journal
- **Schedule**: Daily
- **Endpoint**: NASA Photo Journal RSS feeds
- **Post Type**: `sgu_journal`
- **Settings**: Admin → SGU Weather → Photo Journal
- **Features**:
  - Downloads and attaches images locally
  - Supports multiple RSS feeds
  - Categorizes by feed type

### 7. Astronomy Photo of the Day (APOD)
- **Schedule**: Daily
- **Endpoint**: NASA APOD API
- **Post Type**: `sgu_apod`
- **Cache Duration**: 12 hours
- **Settings**: Admin → SGU Weather → APOD
- **Features**:
  - Downloads images locally
  - Supports both images and videos
  - Stores HD URL if available
  - Can share API keys with CME

## Admin Interface

### Accessing the Sync Dashboard

Navigate to: **WordPress Admin → SGU Weather → Sync Status**

The dashboard provides:
- Real-time status of each API sync
- Next scheduled run times
- Total posts stored for each API
- Configuration status indicators
- Manual sync buttons for each API
- Recent sync logs (last 50 entries)
- Bulk sync action (sync all APIs at once)

### Manual Sync via Admin

1. Go to **SGU Weather → Sync Status**
2. Click **"Sync Now"** on individual API cards, or
3. Click **"Sync All APIs Now"** for bulk sync

### Configuration Status Indicators

- **Green "Active"**: API is properly configured and scheduled
- **Red "Inactive"**: API credentials or endpoints are missing

## WP-CLI Commands

### Sync All APIs
```bash
wp sgu sync all
```

### Sync Individual APIs
```bash
wp sgu sync cme           # CME Alerts
wp sgu sync flare         # Solar Flare Alerts
wp sgu sync geomag        # Geomagnetic Alerts
wp sgu sync space-weather # Space Weather Alerts
wp sgu sync neo           # Near Earth Objects
wp sgu sync journal       # NASA Photo Journal
wp sgu sync apod          # APOD
```

### Check Sync Status
```bash
wp sgu status
```

Displays a table with:
- API name
- Schedule status
- Next run time
- Total posts stored

### View Sync Logs
```bash
wp sgu logs              # Show last 20 lines
wp sgu logs --lines=50   # Show last 50 lines
```

### Clear API Cache
```bash
wp sgu clear-cache
```

Removes all transient caches for API responses, forcing fresh data on next sync.

## Cron Schedules

The plugin automatically schedules the following cron events on activation:

| Cron Hook | Recurrence | Interval |
|-----------|------------|----------|
| `sgu_sync_cme_alerts` | Hourly | 1 hour |
| `sgu_sync_solar_flare` | Hourly | 1 hour |
| `sgu_sync_geomagnetic` | Custom: sgu_30min | 30 minutes |
| `sgu_sync_space_weather` | Custom: sgu_30min | 30 minutes |
| `sgu_sync_neo` | Twice Daily | 12 hours |
| `sgu_sync_photo_journal` | Daily | 24 hours |
| `sgu_sync_apod` | Daily | 24 hours |

### Custom Cron Interval

The plugin adds a custom `sgu_30min` interval for 30-minute syncs.

### Viewing WordPress Cron

```bash
wp cron event list
```

## Configuration

### API Keys

Most NASA APIs require an API key. You can obtain one at:
https://api.nasa.gov/

**Key Sharing**: The plugin supports sharing API keys between related APIs:
- Solar Flare can use CME keys
- NEO can use CME keys
- APOD can use CME keys

Enable key sharing via the checkbox in each API's settings page.

### Required Configuration

For each API, configure:

1. **Endpoint URL**: The API endpoint (pre-filled for most APIs)
2. **API Keys**: Your NASA/NOAA API keys (if required)
3. **Additional Settings**: Feed URLs for RSS-based APIs

### Settings Locations

All API settings are accessible under **SGU Weather** menu:
- Main Weather APIs: SGU Weather → SGU Weather
- Individual APIs: SGU Weather → [API Name]

## Data Storage

### Custom Post Types

All synced data is stored as WordPress posts using custom post types:

| Post Type | Slug | Purpose |
|-----------|------|---------|
| `sgu_cme_alerts` | `cme-alerts` | CME alert notifications |
| `sgu_sf_alerts` | `solar-flare-alerts` | Solar flare alerts |
| `sgu_geo_alerts` | `geo-magnetic-alerts` | Geomagnetic alerts |
| `sgu_sw_alerts` | `space-weather-alerts` | Space weather alerts |
| `sgu_neo` | `near-earth-objects` | NEO tracking data |
| `sgu_journal` | `astronomy-information/nasa-photo-journal` | NASA photo journal |
| `sgu_apod` | `astronomy-information/nasas-astronomy-photo-of-the-day` | Daily astronomy photos |

### Post Meta

Additional data is stored in post meta:
- `sgu_activity_id`: Unique identifier from API
- `sgu_alert_data`: Full API response (serialized)
- `sgu_neo_hazardous`: Yes/No for potentially hazardous asteroids
- `sgu_apod_local_media`: WordPress attachment ID for downloaded images
- `sgu_journal_local_image`: WordPress attachment ID for journal images
- And more...

### Media Handling

The plugin automatically downloads and stores images from:
- NASA Photo Journal RSS feeds
- Astronomy Photo of the Day

Downloaded images are:
- Stored in WordPress media library
- Attached to their respective posts
- Set as featured images
- Tracked via post meta

## Logging

### Log File Location

Logs are stored in: `wp-content/uploads/sgu-sync.log`

### Log Levels

- **INFO**: Normal operation messages
- **WARNING**: Non-critical issues (missing configuration, unchanged data)
- **ERROR**: Critical failures (API errors, post creation failures)

### Log Format

```
[2025-11-17 10:30:45] [INFO] Starting CME alerts sync
[2025-11-17 10:30:47] [INFO] CME alerts synced successfully. Processed 5 alerts
[2025-11-17 10:30:50] [ERROR] API request failed: Connection timeout
```

### Viewing Logs

**Via Admin Interface**: SGU Weather → Sync Status (bottom of page)

**Via WP-CLI**:
```bash
wp sgu logs --lines=50
```

**Via SSH**:
```bash
tail -f wp-content/uploads/sgu-sync.log
```

## Caching

### Transient Cache

API responses are cached using WordPress transients to reduce API calls:

| API | Cache Duration |
|-----|----------------|
| CME Alerts | 1 hour |
| Solar Flare | 1 hour |
| Geomagnetic | 30 minutes |
| Space Weather | 30 minutes |
| NEO | 2 hours |
| APOD | 12 hours |
| Photo Journal | RSS cache (built-in) |

### Cache Keys

Cache keys are generated using MD5 hash of URL + parameters:
```
sgu_api_[md5_hash]
```

### Clearing Cache

**Via WP-CLI**:
```bash
wp sgu clear-cache
```

**Manual**:
```bash
wp transient delete --all
```

## Troubleshooting

### Issue: Syncs Not Running

**Check**:
1. Verify WordPress cron is enabled (not disabled in wp-config.php)
2. Check cron schedule: `wp cron event list`
3. Check if plugin is active
4. Review error logs

**Fix**:
```bash
# Reschedule events
wp eval "SGU_Sync_Manager::unschedule_events(); SGU_Sync_Manager::schedule_events();"
```

### Issue: API Key Errors

**Check**:
1. Verify API key is correctly entered in settings
2. Check if key sharing is properly configured
3. Test API key at: https://api.nasa.gov/

**Fix**: Update API keys in **SGU Weather → [API Name]**

### Issue: No Data Syncing

**Check**:
1. View sync logs: `wp sgu logs`
2. Check API configuration status in admin dashboard
3. Test manual sync for specific API

**Debug**:
```bash
# Test individual sync with logging
wp sgu sync cme
wp sgu logs --lines=20
```

### Issue: Rate Limiting

**Symptoms**: HTTP 429 errors in logs

**Solution**:
- Reduce sync frequency in cron schedules
- Ensure caching is working properly
- Contact NASA for increased rate limits

### Issue: Missing Images

**Check**:
1. Verify `wp-content/uploads` is writable
2. Check PHP `max_execution_time` and `memory_limit`
3. Review error logs for download failures

**Fix**:
```bash
# Check permissions
ls -la wp-content/uploads

# Fix permissions if needed
chmod 755 wp-content/uploads
```

## Performance Considerations

### Resource Usage

- **API Calls**: Limited by caching system
- **Memory**: Image downloads may require increased PHP memory
- **Disk Space**: Images stored in uploads directory

### Optimization Tips

1. **Adjust Sync Frequency**: Modify cron schedules if needed
2. **Cache Duration**: Increase cache times for less-critical data
3. **Limit Photo Journal Feeds**: Configure only necessary RSS feeds
4. **Clean Old Posts**: Periodically remove outdated posts

### Recommended Server Requirements

- PHP 8.1 or higher
- 256MB PHP memory limit (minimum)
- 1GB disk space for media storage
- WordPress 6.0.9 or higher

## Security

### API Key Storage

- API keys stored in WordPress options table
- Not exposed in frontend
- Accessible only to administrators

### Data Sanitization

- All API data is sanitized before storage
- HTML content filtered via `wp_kses_post()`
- URLs validated with `esc_url_raw()`
- Text sanitized with `sanitize_text_field()`

### File Downloads

- Images downloaded to WordPress uploads directory
- File types validated
- WordPress media security applied

## Development

### Hook System

The sync manager exposes several action hooks:

```php
// Triggered when sync starts
do_action( 'sgu_sync_cme_alerts' );
do_action( 'sgu_sync_solar_flare' );
do_action( 'sgu_sync_geomagnetic' );
do_action( 'sgu_sync_space_weather' );
do_action( 'sgu_sync_neo' );
do_action( 'sgu_sync_photo_journal' );
do_action( 'sgu_sync_apod' );
```

### Extending Sync Manager

```php
// Add custom processing after sync
add_action( 'sgu_sync_cme_alerts', function() {
    // Custom processing after CME sync
}, 20 );
```

### API Reference

```php
// Get sync manager instance
$sync_manager = SGU_Sync_Manager::get_instance();

// Trigger manual sync
$sync_manager->sync_cme_alerts();
$sync_manager->sync_solar_flare();
// ... etc
```

## Support

For issues and questions:
- Check logs: `wp sgu logs`
- Review configuration: **SGU Weather → Sync Status**
- Test individual APIs: `wp sgu sync [api-name]`

## Version History

### 0.0.1 (2025-11-17)
- Initial implementation of sync system
- Support for 7 APIs (CME, Solar Flare, Geomagnetic, Space Weather, NEO, Photo Journal, APOD)
- Admin dashboard interface
- WP-CLI commands
- Automated scheduling with WordPress cron
- Transient caching system
- Comprehensive logging
