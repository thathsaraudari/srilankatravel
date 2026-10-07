# Sri Lanka Travel Website

WordPress MVP for a France-facing Sri Lanka travel package website.

## Repository layout

- `wp-content/plugins/slt-core/` — travel domain plugin (Tours, Hotels, Destinations, Enquiries, settings)
- `wp-content/themes/slt-travel/` — custom public-facing theme
- `.github/workflows/ci.yml` — PHP syntax checks and build artifacts
- `.github/workflows/deploy-ftp.yml` — deployment of the plugin/theme to WordPress hosting over FTP
- `scripts/build.sh` — builds installable plugin/theme ZIP files

## Requirements

- WordPress 6.5+
- PHP 8.1+
- ACF Pro for the structured admin fields used by the MVP
- WooCommerce/Stripe can be added later for payments

## Development workflow

1. Change code under `wp-content/plugins/slt-core` or `wp-content/themes/slt-travel`.
2. Push to GitHub.
3. GitHub Actions validates PHP syntax and builds installable ZIPs.
4. Pushes to `main` automatically deploy the custom plugin and theme when FTP secrets are configured.

## Hosting deployment secrets

The FTP workflow expects these GitHub Actions secrets:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_REMOTE_PATH` — WordPress root path on the hosting account, e.g. `/htdocs/`

The workflow deploys only the custom plugin and theme. WordPress core, uploads, database and `wp-config.php` stay outside source control.

## Build locally

```bash
bash scripts/build.sh
```

Generated files:

- `dist/slt-core-plugin.zip`
- `dist/slt-travel-theme.zip`
