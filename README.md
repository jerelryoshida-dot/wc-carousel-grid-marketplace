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

## Tier Selection UI

The marketplace features a global experience level selector that affects all product cards:

```
+-------------------------------------------------------------+
|  Experience Level:  [Entry] [Mid] [Expert]                     |  <- Global filter bar (Entry default)
+-------------------------------------------------------------+
|  +------------------+  +------------------+                  |
|  | [Entry] Senior   |  | [Entry] Full-Stack|                 |  <- Badge before title
|  |     Developer    |  |     Developer     |                 |
|  |                  |  |                   |                 |
|  |   $1,500/mo      |  |   $2,000/mo       |                 |  <- Price for selected tier
|  |                  |  |                   |                 |
|  |  Headcount: [-1+]|  |  Headcount: [-1+]|                 |
|  |  Total: $1,500   |  |  Total: $2,000    |                 |
|  |  [Add to Cart]   |  |  [Add to Cart]    |                 |
|  +------------------+  +------------------+                  |
+-------------------------------------------------------------+
```

### How It Works

1. **Select Experience Level** - Click Entry, Mid, or Expert in the filter bar
2. **Prices Update** - All product cards show prices for the selected tier
3. **Badge Updates** - Tier badge appears before each product title
4. **Products Filter** - Products without the selected tier are hidden

### Experience Level Colors

| Level | Color | Description |
|-------|-------|-------------|
| Entry | Green | Budget-friendly option |
| Mid | Blue | Standard option |
| Expert | Purple | Premium option |

## Screenshots

_Screenshots to be added_

## Changelog

### 1.0.38 - 2026-02-22
* Fixed: TypeError "removeClass is not a function" when adding to cart
* Fixed: added_to_cart event now passes correct WooCommerce parameters (fragments, cart_hash, $button)
* Added: Mini-cart refresh via cartQuoteRefreshMiniCart() after successful add-to-cart

### 1.0.37 - 2026-02-21
* Fixed: Cart now correctly saves the selected tier level (was defaulting to tier 1)
* Fixed: Set $_POST['selected_tier'] before add_to_cart() for WELP compatibility

### 1.0.36 - 2026-02-21
* Fixed: Global experience level filter now correctly updates button's data-tier-level attribute
* Fixed: Add-to-cart now sends the correct tier level selected via global filter (Entry/Mid/Expert)
* Improved: updateAllPricingPanels() now syncs button attribute with current tier selection

### 1.0.35 - 2026-02-21
* Merge release for v1.0.35

### 1.0.34 - 2026-02-21
* Development update
* Added: Enhanced debug logging for add-to-cart failures
* Added: Captures WooCommerce error notices when add_to_cart fails
* Added: Logs product state (purchasable, stock, price, status) before add_to_cart
* Improved: Error messages now include actual WooCommerce error notices

### 1.0.32 - 2026-02-20
* Fixed: Add-to-cart now works correctly with WELP (WooCommerce Experience Level Pricing)
* Fixed: JavaScript sends WELP-expected field names (welp_selected_tier, welp_tier_name, etc.)
* Fixed: PHP sets $_POST fields before add_to_cart() so WELP can detect tier selection
* Changed: Removed redundant add_welp_compatible_meta filter
* Changed: Cart/checkout display now reads from welp_tier instead of wc_cgm_tier

### 1.0.31 - 2026-02-20
* Added: Comprehensive debug logging for add-to-cart troubleshooting
* Added: WELP-compatible meta keys for cart item data
* Fixed: jQuery .data() vs .attr() inconsistency for tier-level attribute
* Fixed: Tier level now correctly passed to add_to_cart AJAX handler
* Changed: Debug mode enabled in marketplace.js for troubleshooting

### 1.0.18 - 2026-02-19
* Fixed: Total price now always calculates based on monthly rate regardless of price type selection
* Improved: Total display consistency - switching between Monthly/Hourly no longer affects total calculation

### 1.0.17 - 2026-02-19
* Fixed: Fatal error "Cannot redeclare wc_cgm_autoloader()" when multiple plugin versions installed
* Added: function_exists() check to prevent autoloader redeclaration conflicts

### 1.0.16 - 2026-02-19
* Fixed: Total price now always displays with monthly (/mo) suffix regardless of price type toggle
* Improved: Cleaner total display - quantity still updates total but suffix stays consistent

### 1.0.15 - 2026-02-19
* Fixed: Total price now always displays with monthly (/mo) suffix regardless of price type toggle
* Improved: Cleaner total display - quantity still updates total but suffix stays consistent

### 1.0.14 - 2026-02-19
* Fixed price type button toggle: Active button now shows green with !important CSS
* Fixed total display to show /mo or /hr based on selected price type
* Enhanced price type switching with proper AJAX updates

### 1.0.13 - 2026-02-19
* Price type buttons: Active=Green, Inactive=Red
* Total always shows monthly cost regardless of button selection
* Removed price-changing AJAX when clicking Monthly/Hourly buttons (visual toggle only)

### 1.0.12 - 2026-02-19
* Fixed price type toggle buttons: Monthly defaults to green, Hourly inactive is gray
* Clicking toggles green highlight between Monthly/Hourly buttons

### 1.0.11 - 2026-02-19
* Development update

### 1.0.10 - 2026-02-19
* Removed "All" option from experience level filter - Entry is now default
* Price display shows opposite rate (Monthly→hourly equiv, Hourly→monthly equiv)
* Price type buttons always green (#22c55e)
* Removed hover transition from card description
* Cart always uses monthly pricing
* Enhanced add-to-cart validation

### 1.0.9 - 2026-02-18
* Fixed fatal error "Class Exception not found" in Single_Product.php
* Replaced exception throwing with graceful error handling (WooCommerce best practice)
* Added validation to block native add-to-cart for marketplace products
* Added redirect URL fix to prevent double slash in URLs
* Marketplace products now require using the marketplace interface

### 1.0.8 - 2026-02-18
* Fixed tier selection validation for Entry and Expert levels
* Added server-side and client-side validation in add-to-cart flow
* Enhanced error messages for invalid tier/price type selections

### 1.0.7 - 2026-02-18
* Development update

### 1.0.6 - 2026-02-18
* Fixed monthly price display when switching price types
* Fixed WooCommerce cart integration with proper error handling
* Removed pricing header section from product cards
* Enhanced AJAX add to cart with cart totals calculation

### 1.0.5 - 2026-02-18
* Development update

### 1.0.4 - 2026-02-18
* Fixed PHP syntax errors in Admin_Manager, Repository, Marketplace_Widget, and Marketplace
* Enhanced admin settings and product meta box functionality
* Improved marketplace display with better pricing panel handling

## Credits

- **Author**: [Jerel Yoshida](https://github.com/Jerel-R-Yoshida)
- **License**: GPL v2 or later

## Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/Jerel-R-Yoshida/wc-carousel-grid-marketplace/issues) page.
