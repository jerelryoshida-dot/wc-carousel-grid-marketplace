# WooCommerce Carousel/Grid Marketplace

[![License](https://img.shields.io/badge/license-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0%2B-purple.svg)](https://woocommerce.com/)

A modern service marketplace carousel/grid for WooCommerce with Elementor support and optional tiered pricing.

## Features

- **Modern Card Design** - Beautiful service cards with pricing panels, headcount selectors, and popular badges
- **Grid or Carousel Layout** - Choose between static grid, sliding carousel, or hybrid (grid on desktop, carousel on mobile)
- **Category Sidebar** - Filter services by WooCommerce product categories
- **Optional Tiered Pricing** - Enable Entry/Mid/Expert pricing tiers with monthly and hourly rates
- **Elementor Native Widget** - Full visual controls for design customization
- **Shortcode Support** - Use `[wc_cgm_marketplace]` anywhere
- **Popular Badge** - Auto (based on sales) or manual per-product setting
- **AJAX Filtering** - Fast filtering without page reloads
- **Headcount Selector** - Quantity controls with automatic total calculation
- **WooCommerce Integration** - Seamless cart and checkout integration

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 6.0 or higher

## Installation

1. Download the latest release
2. Upload to `/wp-content/plugins/wc-carousel-grid-marketplace/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Ensure WooCommerce is installed and active
5. Go to **WooCommerce > Marketplace** to configure settings

## Usage

### Shortcode

Basic usage:
```
[wc_cgm_marketplace]
```

With parameters:
```
[wc_cgm_marketplace columns="4" category="15" limit="8" show_sidebar="false"]
```

### Shortcode Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `columns` | `3` | Number of columns (1-6) |
| `category` | - | Product category ID to filter |
| `limit` | `-1` | Maximum products to display (-1 for all) |
| `show_sidebar` | `true` | Show category sidebar |
| `layout` | `grid` | Layout type: `grid`, `carousel`, or `hybrid` |

### Elementor

1. Edit a page with Elementor
2. Search for "WC Marketplace" widget
3. Drag and configure the widget settings

### Enabling Products

1. Edit a WooCommerce product
2. Check "Enable for Marketplace" in the Marketplace Settings meta box
3. Optionally configure tier pricing (if enabled in settings)

## Configuration

### General Settings

Navigate to **WooCommerce > Marketplace** to access:

- **Layout Type**: Grid, Carousel, or Hybrid
- **Columns**: Number of products per row
- **Enable Tier Pricing**: Toggle tiered pricing feature
- **Popular Badge Method**: Auto (sales-based) or Manual

### Tier Pricing

When enabled, products can have three pricing tiers:

- **Entry Level**: Budget-friendly option
- **Mid Level**: Standard option
- **Expert Level**: Premium option

Each tier supports both monthly and hourly rates.

## Screenshots

_Screenshots to be added_

## Changelog

### 1.0.0
- Initial release

## Credits

- **Author**: [Jerel Yoshida](https://github.com/jerelryoshida-dot)
- **License**: GPL v2 or later

## Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/jerelryoshida-dot/wc-carousel-grid-marketplace/issues) page.
