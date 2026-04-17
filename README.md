# Preset Quantity Buttons for WooCommerce

**Contributors:** senff
**Donate link:** http://donate.senff.com
**Tags:** preset, quantity, buttons, cart, bundles
**Plugin URI:** https://wordpress.org/plugins/preset-quantity-buttons-for-woocommerce
**Requires at least:** 6.0
**Tested up to:** 6.9
**Requires PHP:** 7.4
**Stable tag:** 1.0
**License:** GPLv3
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html

Adds preset quantity buttons to WooCommerce product pages, letting customers instantly add a predefined quantity of a product to their cart.


## Description

With Preset Quantity Buttons for WooCommerce, your product pages can have additional buttons that will let your customers add predefined quantities of that product to the cart. This way, they can add 5 of the same, or 10, or 23 (or whatever numbers you add) to their cart with just one click.

### Features

* Quantity buttons can replace the default quantity field and Add to Cart button, or be added below them.
* Customizable prefix and suffix (e.g. "Add 3 to Cart", "Get 3", "Buy 3 now!", etc.)
* Every Simple and Variable product can use the settings from the Global Settings page, or have its own settings.


## Installation

### Installation from within WordPress

1. Visit **Plugins > Add New**.
2. Search for **Preset Quantity Buttons for WooCommerce**.
3. Install and activate the Preset Quantity Buttons for WooCommerce plugin.
4. Go to WooCommerce > Settings > Preset Qty Buttons to set global settings.
5. Go to a product editing screen and select the Preset Quantity tab.

### Manual installation

1. Upload the entire `preset-quantity-buttons-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Preset Quantity Buttons for WooCommerce plugin.
4. Go to WooCommerce > Settings > Preset Qty Buttons to set global settings.
5. Go to a product editing screen and select the Preset Quantity tab.


## Frequently Asked Questions

### What does it do exactly?

With this plugin, you can add buttons to product pages that add multiple quantities of a product to the cart with one click. You can show as many buttons as you want, so that your customer can add 5 at the same time, or 10, or 23, or any other quantity.

### I only want to show the preset quantity buttons, and not the default quantity field and Add to Cart button. Can I do that too?

Yes! It's an option in the settings.

### How can I set this for every product in my store?

Go to WooCommerce > Settings > Preset Qty Buttons and enter your settings. Save the settings, and then use the APPLY TO ALL PRODUCTS NOW button to ensure that all your products will have those settings.

### I now want every product to have its own settings.

Go to WooCommerce > Settings > Preset Qty Buttons and use the REMOVE FROM ALL PRODUCTS NOW button. Now, every product will use the settings that are set in its own individual settings.

### I've added a bunch of buttons but some of them are disabled.

The plugin checks the amount of items that are still in stock and what the customer already has in their cart, and will disable any buttons that try to add more. So if you have a stock of 100 items, and the customer has 20 in the cart already, any buttons that would add more than 80 or more to the cart, would be disabled.

### Buttons look all garbled or misplaced. Huh?

The plugin was made for (and tested with) classic themes and Block-based themes that do not override standard WooCommerce functionality. It's possible that the theme you're using has some specific styles or template overrides that cause issues, or that you have other plugins that cause a conflict. Feel free to post a message on the community support forum at https://wordpress.org/support/plugin/preset-quantity-buttons-for-woocommerce with a link to a page where the issue shows, and I can take a look. I will try my best to see if I can spot what's the issue, though I can't give any guarantees that I will be able to fix it -- you may need to talk to the support team of your theme or plugin that might be causing the issue.

### I don't see the Preset Quantities tab on my subscription product.

At this time, the plugin only supports Simple and Variable Products. While it can be made to work on subscription-based products, we've opted not to implement that yet since it could cause confusion among customers with billing.

### I need more help please!

If you're not sure how to use this, or you're running into any issues with it, post a message on the plugin's [WordPress.org support forum](https://wordpress.org/support/plugin/preset-quantity-buttons-for-woocommerce).

### I've noticed that something doesn't work right, or I have an idea for improvement. How can I report this?

The plugin's community support forum at https://wordpress.org/support/plugin/preset-quantity-buttons-for-woocommerce would be a good place, though if you want to add all sorts of technical details, it's best to report it on the plugin's [GitHub page](https://github.com/senff/WooCommerce-Preset-Quantity-Buttons/issues). This is also where I consider code contributions.

### My question isn't listed here?

Please go to the plugin's community support forum at https://wordpress.org/support/plugin/preset-quantity-buttons-for-woocommerce and post a message. Note that support is provided on a voluntary basis and that it can be difficult to troubleshoot, and may require access to your admin area. Needless to say, NEVER include any passwords of your site on a public forum!


## Screenshots

1. Global settings screen
2. Settings screen for individual products
3. Example on product page


## Changelog

### 1.0
* Initial release.


## Upgrade Notice

### 1.0
Initial release of the plugin.
