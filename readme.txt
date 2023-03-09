=== PZZ API Client ===
Contributors: mjavadhpour, thisismzm 
Tags: RESTful API, API, Mobile, Shopping, WooCommerce
Requires at least: 4.2
Tested up to: 6.1
Requires PHP: 7
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Stable tag: 1.2.8

Provides a set of RESTful APIs, developed specifically for Mobile clients that want to connect to your WordPress/WooCommerce website.

== Description ==
PZZ API Client provides a set of RESTful APIs, developed specifically for Mobile clients to connect to your WordPress website. 

This plugin support some specific APIs such as posts, taxonomies, comments, and WooCommerce. We plan to support more APIs.

It is possible to authenticate a user using any of the following WP REST API authentication methods such as:
-  JWT Authentication for WP REST API

== Installation ==
First of all you need to enable [pretty permalinks](https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#overview) at Settings > Permalink > Permalink structure > Select Post name. after that you can review the APIs at <BASE_URL>/wp-json/pzz/v1.
Install latest version from [wordpress](https://wordpress.org/plugins/pzz-api-client/) or Download the latest stable version from [GitHub](https://github.com/mjavadhpour/pzz-api-client/releases) and upload it into your WordPress site.

== Frequently Asked Questions ==
Feel free to open an Issue at [This Link](https://github.com/mjavadhpour/pzz-api-client/issues), Also you can track the developing process from [GitHub](https://github.com/mjavadhpour/pzz-api-client/milestones).

== Changelog ==
= 1.2.8 =
* Refactor source code.
* Test up to wordpress 6.
* Update github repo.
* Fix branching strategy in github.
* Update documentation.

= 1.2.7 =
* Bug fix

= 1.2.6 =
* Bug fix

= 1.2.5 =
* Fix post API (GET).

= 1.2.4 =
* Update plugin description.
* Bug Fixing.

= 1.2.3 =
* Use hook instead of filter.

= 1.2.2 =
* Bug Fixing.

= 1.2.1 =
* Bug Fixing.

= 1.2.0 =
* Add Support for WooCommerce.

= 1.1.6 =
* Use isolated filters, these filters run just when the endpoint was called. we provide these filters because the plugin adds target="_blank" to all links even when the website serves a page.

= 1.1.5 =
* Resolve conflicts with reactor-core.

= 1.1.4 =
* Fix WordPress trademarked name with plugin name.

= 1.1.3 =
* Update readme and plugin description.

= 1.1.2 =
* Use WordPress filters to replace links and fix the functionality of replaced links.

= 1.1.1 =
* Fix get all posts API error.

= 1.1.0 =
* Replace all links int the post description by  in post API.

= 1.0.0 =
* Basic functionality.
* Post API.
* Taxonomies.
* Post comments.

== Upgrade Notice ==
If you install plugin manually, please remove the previous version and install the new one.
