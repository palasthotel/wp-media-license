<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.5.1
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 5.8.2
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\MediaLicense
 */

namespace Palasthotel\MediaLicense;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MediaLicense
 * @property string $path
 * @property string url
 * @property MetaFields meta_fields
 * @property Shortcode shortcode
 * @property Assets $assets
 * @property Render render
 * @property Gutenberg gutenberg
 * @property Rest rest
 */
class Plugin {

	const DOMAIN = 'media-license';

	/**
	 * theme template parts
	 */
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_FILE_CAPTION = "media-license-caption.tpl.php";
	const FILTER_TEMPLATE_PATHS = "media_license_template_paths";

	/**
	 * FILTERS
	 */
	const FILTER_EDIT_CAPTION = "media_license_edit_caption";
	const FILTER_ADD_FIELDS = "media_license_add_fields";
	const FILTER_EDIT_LICENSE = "media_license_edit_licenses";
	const FILTER_AUTOLOAD_ASYNC_IMAGE_LICENSE = "media_license_autoload_async_image_license";
	const FILTER_BLOCK_LIST_OF_LICENSES_IMAGE_IDS = "media_license_block_list_of_licenses_image_ids";

	/**
	 * meta field key names
	 */
	const META_LICENSE = "media_license_info";
	const META_AUTHOR = "media_license_author";
	const META_URL = "media_license_url";

	/**
	 * handle of javascript asset
	 */
	const HANDLE_API_JS = "media-license-js";
	const HANDLE_GUTENBERG_JS = "media-license-gutenberg";

	/**
	 * MediaLicenses constructor.
	 */
	private function __construct() {

		/**
		 * plugin directory
		 */
		$this->path = plugin_dir_path( __FILE__ );
		$this->url  = plugin_dir_url( __FILE__ );

		/**
		 * load translations
		 */
		load_plugin_textdomain(
			self::DOMAIN,
			false,
			plugin_basename( dirname( __FILE__ ) ) . '/languages'
		);

		require_once dirname( __FILE__ ) . "/vendor/autoload.php";

		$this->render      = new Render( $this );
		$this->meta_fields = new MetaFields( $this );
		$this->shortcode   = new Shortcode( $this );
		$this->assets      = new Assets( $this );
		$this->rest        = new Rest($this);
		$this->gutenberg   = new Gutenberg( $this );

	}

	private static $instance = null;

	/**
	 * @return Plugin $instance
	 */
	static function instance(): Plugin {
		if ( self::$instance == null ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	// ------------------------------------------------------------
	// deprecations
	// ------------------------------------------------------------
	/**
	 * @deprecated use HANDLE_API_JS for consistent naming
	 */
	const API_JS_HANDLE = "media-license-js";
	/**
	 * @deprecated use FILTER_EDIT_CAPTION instead
	 */
	const FILTER_EDIT_CAPTION_NAME = self::FILTER_EDIT_CAPTION;
	/**
	 * @deprecated just add number of arguments you want to use
	 */
	const FILTER_EDIT_CAPTION_NUM_ARGS = 3;

	/**
	 * @deprecated use FILTER_ADD_FIELDS instead
	 */
	const FILTER_ADD_FIELDS_NAME = self::FILTER_ADD_FIELDS;
	/**
	 * @deprecated just add number of arguments you want to use
	 */
	const FILTER_ADD_FIELDS_NUM_ARGS = 1;

	/**
	 * @deprecated use FILTER_EDIT_LICENSE instead
	 */
	const FILTER_EDIT_LICENSE_NAME = "media_license_edit_licenses";
	/**
	 * @deprecated just add number of arguments you want to use
	 */
	const FILTER_EDIT_LICENSE_NUM_ARGS = 1;

}

Plugin::instance();

require_once dirname( __FILE__ ) . "/public-functions.php";

