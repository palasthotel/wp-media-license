<?php

/**
 * Plugin Name:       Media License (DEV)
 * Plugin URI:        https://github.com/palasthotel/media-license
 * Description:       Development wrapper. Loads the plugin from public/, which is what ships to wordpress.org. Do not deploy this file.
 * Version:           0.0.0-dev
 * Requires at least: 6.6
 * Author:            Palasthotel <rezeption@palasthotel.de> (Edward Bock, Lucas Regalar)
 * Author URI:        https://palasthotel.de
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       media_license
 * Domain Path:       /public/languages
 */

// The version above is deliberately not a real one and nothing syncs it. This file never
// ships, so its version means nothing, and bin/version-checker.sh does not check it.

include dirname( __FILE__ ) . "/public/media-license.php";
