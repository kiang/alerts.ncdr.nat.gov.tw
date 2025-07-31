# Taiwan NCDR Alert Data Fetcher

This project fetches typhoon tracking data from Taiwan's National Center for Disaster Reduction (NCDR) alert platform.

## Overview

The system automatically downloads typhoon KMZ files containing track and forecast data, validates the content, and preserves valid data while preventing empty updates.

## Files

- `scripts/01_fetch.php` - Main fetcher script with validation
- `scripts/cron.php` - Cron job scheduler
- `docs/typhoon.kmz` - Latest typhoon tracking data
- `docs/event/` - Archived typhoon events

## Features

### KMZ Validation
The fetch script includes validation to ensure data quality:
- Downloads to temporary file first
- Extracts and validates KML content
- Checks for actual Placemark features (typhoon tracks)
- Only updates if valid features are found
- Preserves existing data when no features are available

### Data Format
The KMZ files contain:
- KML files with typhoon tracking data
- Placemark features for track points
- Associated images and styling

## Usage

### Manual Fetch
```bash
php scripts/01_fetch.php
```

### Automated Updates
The cron script handles scheduled updates:
```bash
php scripts/cron.php
```

## Data Source

Fetches from: `https://alerts.ncdr.nat.gov.tw/DownLoadNewAssistData.ashx/1`

## Validation Logic

1. Downloads KMZ to temporary file
2. Opens as ZIP archive
3. Locates KML file inside
4. Searches for `<Placemark>` elements
5. Counts and logs features found
6. Updates only if features exist
7. Skips update if no features (preserves existing data)

## Exit Codes

- `0` - Success or no update needed
- `1` - Error (network, file, or validation failure)

## Requirements

- PHP with cURL and ZipArchive extensions
- Network access to NCDR servers

## Author

Finjon Kiang

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.