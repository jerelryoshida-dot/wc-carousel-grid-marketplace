=== WooCommerce Carousel/Grid Marketplace ===
Contributors: Jerel-R-Yoshida
Tags: woocommerce, marketplace, carousel, grid, elementor, services, tiered pricing
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.34
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


== 1.0.34 ==
Released: 2026-02-21

* Development update
== 1.0.33 ==
Released: 2026-02-20

* **Added**: Enhanced debug logging for add-to-cart failures
* **Added**: Captures WooCommerce error notices when add_to_cart fails
* **Added**: Logs product state (purchasable, stock, price, status) before add_to_cart
* **Added**: Logs WooCommerce session and customer state
* **Improved**: Error messages now include actual WooCommerce error notices

== 1.0.32 ==
Released: 2026-02-20

* **Fixed**: Add-to-cart now works correctly with WELP (WooCommerce Experience Level Pricing)
* **Fixed**: JavaScript sends WELP-expected field names (welp_selected_tier, welp_tier_name, etc.)
* **Fixed**: PHP sets $_POST fields before add_to_cart() so WELP can detect tier selection
* **Changed**: Removed redundant add_welp_compatible_meta filter (WELP handles everything via $_POST)
* **Changed**: Cart/checkout display now reads from welp_tier instead of wc_cgm_tier
* **Improved**: Cleaner integration with WELP's native cart handling

== 1.0.31 ==
Released: 2026-02-20

* **Added**: Comprehensive debug logging for add-to-cart troubleshooting
* **Added**: WELP-compatible meta keys for cart item data
* **Fixed**: jQuery .data() vs .attr() inconsistency for tier-level attribute
* **Fixed**: Tier level now correctly passed to add_to_cart AJAX handler
* **Changed**: Debug mode enabled in marketplace.js for troubleshooting
== 1.0.30 ==
Released: 2026-02-20

* **Changed**: Replaced Monthly/Hourly buttons with modern toggle switch
* **Improved**: Toggle switch is right-aligned for cleaner UI
* **Improved**: Smoother transitions when switching between price types

== 1.0.29 ==
Released: 2026-02-19

* **Added**: Loading animation overlay - shows spinner until prices are synced and correct
* **Added**: Staggered fade-in animation for product cards
* **Added**: Loading states for category filter, search, and load more
* **Improved**: No more visual glitch of wrong prices on initial load

== 1.0.28 ==
Released: 2026-02-19

* **Fixed**: Service category click now syncs prices with active tier filter
* **Fixed**: Load More button now syncs prices with active tier
* **Fixed**: Search results now sync prices with active tier

== 1.0.27 ==
Released: 2026-02-19

* **Fixed**: Marketplace first load now displays correct tier prices (syncs with active filter)
* **Fixed**: Product badges and prices now match the selected experience level on load

== 1.0.26 ==
Released: 2026-02-19

* **Fixed**: Price display now uses actual database values instead of calculated conversions
* **Fixed**: Monthly/Hourly prices no longer incorrectly calculated (e.g., 1200 showing as 1280)

== 1.0.25 ==
Released: 2026-02-19

* **Fixed**: pricing-panel-simple.php now shows hourly/monthly conversion instead of "/item"
* **Added**: Monthly/Hourly toggle buttons to simple pricing panel
* **Added**: Data attributes for JavaScript price switching

== 1.0.24 ==
Released: 2026-02-19

* **Fixed**: Hourly/Monthly price type now correctly sent to cart (was always using monthly)
* **Removed**: Orphan pricing-panel.php template file

== 1.0.23 ==
Released: 2026-02-19

* **Fixed**: Fatal error "Call to undefined function wc_cgm_tier_pricing_enabled()" in marketplace template
* **Fixed**: Admin settings now use consistent `wc_cgm_` prefix throughout
* **Changed**: Now requires WooCommerce Experience Level Pricing plugin as dependency
* **Improved**: Cleaner architecture - tier pricing managed by WELP plugin

== 1.0.22 ==
Released: 2026-02-19

* Fixed: Card description now prioritizes full description over short description
* Improved: Word limit increased from 20 to 30 words for better content display

== 1.0.21 ==
Released: 2026-02-19

* Fixed: Card description now always shows WooCommerce product description (short/full), never tier description
* Fixed: All prices formatted with 2 decimal places (e.g., $500.00 instead of $500)
* Improved: Tier description only shown in pricing panel, not in card
* Added: Pricing panel CSS styling with border and background

== 1.0.20 ==
Released: 2026-02-19

* Changed: Monthly pricing button now appears first and is selected by default
* Added: Tier description displayed above Monthly/Hourly buttons in pricing panel
* Added: Card description shows selected tier's description (updates when tier changes)
* Improved: Dynamic tier description updates via JavaScript when selecting different tiers

== 1.0.19 ==
Released: 2026-02-19

* Fixed: Fatal error on plugin activation - spl_autoload_register() callback now defined before registration
== 1.0.18 ==
Released: 2026-02-19

* Fixed: Total price now always calculates based on monthly rate regardless of price type selection
* Improved: Total display consistency - switching between Monthly/Hourly no longer affects total calculation

== 1.0.17 ==
Released: 2026-02-19

* Fixed: Fatal error "Cannot redeclare wc_cgm_autoloader()" when multiple plugin versions installed
* Added: function_exists() check to prevent autoloader redeclaration conflicts

== 1.0.16 ==
Released: 2026-02-19

* Development update
== 1.0.15 ==
Released: 2026-02-19

* Fixed: Total price now always displays with monthly (/mo) suffix regardless of price type toggle
* Improved: Cleaner total display - quantity still updates total but suffix stays consistent

== 1.0.14 ==
Released: 2026-02-19

* Fixed price type button toggle: Active button now shows green with !important CSS
* Fixed total display to show /mo or /hr based on selected price type
* Enhanced price type switching with proper AJAX updates

== 1.0.13 ==
Released: 2026-02-19

* Price type buttons: Active=Green, Inactive=Red
* Total always shows monthly cost regardless of button selection
* Removed price-changing AJAX when clicking Monthly/Hourly buttons (visual toggle only)
== 1.0.12 ==
Released: 2026-02-19

* Fixed price type toggle buttons: Monthly defaults to green, Hourly inactive is gray
* Clicking toggles green highlight between Monthly/Hourly buttons
== 1.0.11 ==
Released: 2026-02-19

* Development update
== 1.0.10 ==
Released: 2026-02-19

* Removed "All" option from experience level filter - Entry is now default
* Price display now shows opposite rate: Monthly shows hourly equivalent, Hourly shows monthly equivalent
* Price type buttons (Monthly/Hourly) now always display green (#22c55e)
* Removed hover transition effect from card description
* Fixed font-size override for card description to prevent theme conflicts
* Cart always uses monthly pricing regardless of selected price type
* Enhanced add-to-cart validation with clear error messages

== 1.0.9 ==
Released: 2026-02-18

* Fixed fatal error "Class Exception not found" in Single_Product.php
* Replaced exception throwing with graceful error handling (WooCommerce best practice)
* Added woocommerce_add_to_cart_validation filter to block native add-to-cart for marketplace products
* Added redirect URL fix to prevent double slash in URLs (/pricing//?add-to-cart=)
* Marketplace products now require using the marketplace interface for add-to-cart

== 1.0.8 ==
Released: 2026-02-18

* Fixed tier selection validation for Entry and Expert levels
* Added server-side validation in AJAX add-to-cart handler
* Added client-side validation in JavaScript before AJAX calls
* Fixed data attributes for all tier levels in pricing panel
* Added user-friendly error messages for invalid tier selections
* Enhanced single product page tier validation
* Added new localization strings for error messages

== 1.0.7 ==
Released: 2026-02-18

* Development update
== 1.0.6 ==
Released: 2026-02-18

* Fixed monthly price display when switching price types
* Fixed WooCommerce cart integration with proper error handling
* Removed pricing header section from product cards
* Enhanced AJAX add to cart with cart totals calculation

== 1.0.5 ==
Released: 2026-02-18

* Development update
== 1.0.4 ==
Released: 2026-02-18

* Development update
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
