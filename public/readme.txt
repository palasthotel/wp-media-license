=== Media License ===
Contributors: palasthotel, edwardbock, kroppenstedt
Donate link: http://palasthotel.de/
Tags: media, extension, license
Requires at least: 5.0
Tested up to: 6.4.2
Stable tag: 1.6.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl

Extend your media files with license information.

== Description ==

Extend your media files with license information. Customizable and themable.

== Installation ==

1. Upload `media-license.zip` to the `/wp-content/plugins/` directory
1. Extract the Plugin to a `media-license` Folder
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Now you can add creative common license and much more to you media files

== Frequently Asked Questions ==

= Can I extend the licenses? =

Yes you can with the filter function "media_license_add_fields".

== Screenshots ==



== Changelog ==

= 1.6.2 =
* Fix: prevent double slashes in url path

= 1.6.1 =
* Bugfix: Copy license meta to edited image

= 1.6.0 =
* Feature: Headless plugin integration

= 1.5.2 =
* Bugfix: ListOfLicenses block work with 1.4 and later

= 1.5.1 =
* Optimization: New "has-duplicate-caption" class in frontend if local and image captions are the same

= 1.5.0
* Optimization: removed jQuery as dependency

= 1.4.5 =
* Bugfix: Missing alignment classes on figure wrapper in frontend fix

= 1.4.4 =
* Bugfix: Missing permission callback for rest route

= 1.4.3 =
* Optimization: moved wp-ajax to rest endpoint for better caching

= 1.4.2 =
* Optimization: list of licenses blockx update
* Bugfix: empty post excerpt fix

= 1.4.1 =
 * Bugfix: Enqueue script fix
 * Bugfix: Do not render same captions twice.

= 1.4.0 =
 * Breaking change: Namespace of plugin has changed due to unification
 * Feature: New filter to provide templates with plugins
 * Optimizations: General refactorings
 * Optimizations: deprecated some functions and constants

= 1.3.3 =
 * Translations: CH
 * Bugfix: translations path

= 1.3.2 =
 * Feature: Filter for extending list of licenses
 * Optimization: template translation
 * Bugfix: Gutenberg captions were not handled in every case

= 1.3.1 =
 * Bugfix: Do not do anything on empty caption
 * Bugfix: Gutenberg caption without wp-caption class fix

= 1.3.0 =
 * Bugfix: Gutenberg support
 * Optimization: Changed default value for filter media_license_autoload_async_image_license
 * Optimization: Automatically enqueue api.js script

= 1.2.3 =
 * Bugfix JavaScript
 * Same picture twice fix

= 1.2.2 =
 * Ajax NoPriv typo fix
 * API JS Get Requests with paging
 * API JS flexibility

= 1.2.1 =
 * No images on page fix

= 1.2 =
 * Ajax API
 * global $media_license not available anymore
 * singleton pattern plugin now
 * autoload async licenses filter added

= 1.1.1 =
 * Bugfix: Rendering of Public Domain and All rights reserved

= 1.1.0 =
 * Theme template location changed to plugin-parts subfolder.
 * Template part file ending is now .tpl.php

= 1.0.2 =
 * If no license selected no output in template

= 1.0.1 =
 * Linkable author

= 1.0.0 =
 * First release

== Upgrade Notice ==

= 1.4.0 =

Plugin PHP namespace has changed due to unification.

Deprecated some constants and functions. Please consider refactoring custom code in your project.

= 1.3.0 =
Default behaviors changed. See changelog.
