=== WooCommerce Carousel/Grid Marketplace ===
Contributors: jerelryoshida-dot
Tags: woocommerce, marketplace, carousel, grid, elementor, services, tiered pricing
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern service marketplace carousel/grid for WooCommerce with Elementor support and optional tiered pricing.

== Description ==

= WooCommerce Carousel/Grid Marketplace =

Create a professional B2B service marketplace with this powerful WooCommerce extension. Features a modern card-based grid/carousel layout with optional tiered pricing, category filtering, and full Elementor compatibility.

**Key Features:**

* **Modern Card Design** - Beautiful service cards with pricing panels, headcount selectors, and popular badges
* **Grid or Carousel Layout** - Choose between static grid, sliding carousel, or hybrid (grid on desktop, carousel on mobile)
* **Category Sidebar** - Filter services by WooCommerce product categories
* **Optional Tiered Pricing** - Enable Entry/Mid/Expert pricing tiers with monthly and hourly rates
* **Elementor Native Widget** - Full visual controls for design customization
* **Shortcode Support** - Use `[wc_cgm_marketplace]` anywhere
* **Popular Badge** - Auto (based on sales) or manual per-product setting
* **AJAX Filtering** - Fast filtering without page reloads
* **Headcount Selector** - Quantity controls with automatic total calculation
* **WooCommerce Integration** - Seamless cart and checkout integration

= Elementor Compatibility =

* Native Elementor widget with full design controls
* Responsive column settings
* Style controls for cards, buttons, typography
* Works with Elementor's global colors
* Compatible with Elementor canvas mode

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/wc-carousel-grid-marketplace/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and active
4. Go to WooCommerce > Marketplace to configure settings
5. Enable products for the marketplace in the product edit screen

== Usage ==

= Shortcode =

Basic usage:
`[wc_cgm_marketplace]`

With parameters:
`[wc_cgm_marketplace columns="4" category="15" limit="8" show_sidebar="false"]`

= Elementor =

1. Edit a page with Elementor
2. Search for "WC Marketplace" widget
3. Drag and configure the widget settings

= Enabling Products =

1. Edit a WooCommerce product
2. Check "Enable for Marketplace" in the Marketplace Settings meta box
3. Optionally configure tier pricing (if enabled in settings)

== Frequently Asked Questions ==

= Does this work without WooCommerce? =

No, this plugin requires WooCommerce to be installed and active.

= Can I use this with any theme? =

Yes, the plugin is designed to work with any WordPress theme.

= Does it work with Elementor? =

Yes! There's a native Elementor widget with full design controls.

= Can I disable tiered pricing? =

Yes, tiered pricing is optional and can be disabled in the plugin settings.

== Changelog ==

== 1.0.3 ==
Released: 2026-02-18

* Fixed Elementor widget registration timing issue
* Fixed product meta box not loading without tier pricing enabled
* Added marketplace_only toggle to show all WC products by default
* Added admin notice when no products found
* Tier pricing tables now created on activation (regardless of setting)
* Enhanced Elementor editor preview with status indicators

== 1.0.2 ==
Released: 2026-02-18

* Development update
== 1.0.1 ==
Released: 2026-02-18

* Development update
= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of WooCommerce Carousel/Grid Marketplace.
