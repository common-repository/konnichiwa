=== Konnichiwa! Membership  ===
Contributors: prasunsen
Tags: membership, content management, subscriptions, learning, community
Requires at least: 4.0
Tested up to: 5.8
Stable tag: trunk
License: GPL2

Konnichiwa! Membership is a super quick-start membership plugin integrated with WooCommerce. Protects individual content, categories, or post types.

/***

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
***/    

== Description ==

Create Powerful Membership Site In Minutes! 
(From the creators of [Namaste! LMS](https://namaste-lms.org "Namaste! LMS"))

See what Konnichiwa! does:

###Features###

- Manage unlimited number of subscription plans
- Protect content by type or by category, all from one page
- Protect each individual piece of content individually from the add/edit page in your administration
- Protect parts of post / page (or any other content) by placing it inside Konnichiwa shortcode
- Supports custom content types
- Supports Paypal and Stripe payments
- WooCommerce support. Each subscription plan can be linked to a WooCommerce product (virtual and downloadable products only). When the product is purchased, the user gets subscribed to the plan,
- Manage subscriptions and manually subscribe users
- Auto-publish a sales page with all membership plans, or design the plans yourself and use the shortcodes only for the subscribe buttons
- Protect a piece of content within a post or page (or custom post type)
- Integrates with [Namaste! LMS](https://namaste-lms.org "Namaste! LMS"),[Daskal](https://wordpress.org/plugins/daskal/ "Daskal"), [MoolaMojo](https://moolamojo.com "MoolaMojo") and other popular plugins

**After activating the plugin please check the Help page for a quick getting started guide.**

== Installation ==

1. Unzip the contents and upload the entire `konnichiwa` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Konnichiwa" in your menu and manage the plugin
4. Go to Konnichiwa -&gt; Help for a super quick "getting started" guide

== Frequently Asked Questions ==

None yet, please ask in the forum

== Screenshots ==

1. Manage your payment settings. Currently Paypal and Stripe are supported for automated activation.
2. Add/edit subscription plan. Define price (can be free as well) and how long it lasts.
3. Manage content restrictions by content type and content category.
4. Manage subscriptions made by members.
5. Each post, page or other content type can have its individual access restrictions.
6. Add/edit a subscription plan. If you are using WooCommerce the plan can be sold as a WooCommerce product.

== Changelog ==

= Version 0.8.3 = 
- Fixed the return URL in Paypal button to go back to the same post.
- Added Paypal Sandbox and PDT mode in payment settings.
- Added built-in WooCommerce support. Enable it from the settings page. Then each subscription plan can be linked to a WooCommerce product.


= Version 0.8 = 
1. Made the protect shortcode execute shortcodes from other plugins. This way you can protect any dynamic conent like quizzes etc.
2. "Protected files" menu lets you upload files protected by your subscription plans.
3. Fixed problem with replacing variables for "other payment methods"
4. Added custom currency option
5. Added shortcode [konnichiwa-mysubs] to display a page with My Subscriptions, along with options to renew or cancel an active subscription.
6. Added sortable columns on the Manage Subscrions page
7. Integrated MoolaMojo plugin as payment processor
8. Added option to automatically subscribe newly registered users to subscription plans. In such case the first subscription will be free regardless what is the plan price.

= Version 0.7 =
- A new shortcode allows you to protect a piece of content within a post or page (or custom post type)

= Version 0.6.4 =

First public release