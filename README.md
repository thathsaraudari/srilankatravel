# Sri Lanka Travel

Custom WordPress travel-package website for a France-facing Sri Lanka travel business.

## Current MVP

The project is self-contained and does **not** require ACF Pro.

### Admin
- Tours
- Hotels
- Destinations and travel styles
- Day-by-day itinerary editor
- WordPress media picker for itinerary photos
- Highlights, inclusions and exclusions
- Price / price-on-request configuration
- Enquiries stored in WordPress
- Enquiry workflow: New → Contacted → Quoted → Booked / Closed
- Quote amount, payment-link field and internal notes
- Global site settings for contact details, currency, deposit percentage and payment mode

### Frontend
- French-first homepage
- Tour listing
- Tour detail pages
- Responsive itinerary
- Quote/enquiry form
- Responsive custom theme

## Repository layout

- `wp-content/plugins/slt-core/` — travel CMS and enquiry workflow
- `wp-content/themes/slt-travel/` — public-facing theme
- `wp-content/mu-plugins/slt-bootstrap.php` — one-time activation and demo-data bootstrap
- `.github/workflows/ci.yml` — PHP validation and ZIP build
- `.github/workflows/deploy-ftp.yml` — automatic InfinityFree deployment
- `scripts/build.sh` — builds installable ZIP files

## Requirements

- WordPress 6.4+
- PHP 8.1+
- No paid plugin required for the MVP
- WooCommerce / Stripe will be added when direct payment functionality is enabled

## Deployment

Pushes to `main` that change the plugin, theme or bootstrap are automatically deployed to the configured InfinityFree WordPress installation.

The InfinityFree host, FTP username, port and WordPress path are configured in the workflow. GitHub only needs this repository secret:

- `FTP_PASSWORD`

Never commit passwords, API keys, Stripe secrets or WordPress credentials.

## Build locally

```bash
bash scripts/build.sh
```

Generated packages:

- `dist/slt-core-plugin.zip`
- `dist/slt-travel-theme.zip`

## Demo content

The bootstrap creates the initial 7-day Sri Lanka itinerary supplied for the project, along with the referenced hotels, destinations, menu and contact page. It is idempotent and will not create duplicates on every request.
