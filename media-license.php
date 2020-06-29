<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.4.0
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 5.4.2
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
 * @property string dir
 * @property string url
 * @property MetaFields meta_fields
 * @property Shortcode shortcode
 * @property API api
 * @property Render render
 */
class Plugin {

	const DOMAIN = 'media_license';

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

	/**
	 * meta field key names
	 */
	const META_LICENSE = "media_license_info";
	const META_AUTHOR = "media_license_author";
	const META_URL = "media_license_url";

	/**
	 * handle of javascript asset
	 */
	const API_JS_HANDLE = "media-license-js";

	/**
	 * MediaLicenses constructor.
	 */
	private function __construct() {

		/**
		 * plugin directory
		 */
		$this->dir = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);

		/**
		 * load translations
		 */
		load_plugin_textdomain(
			self::DOMAIN,
			false,
			plugin_basename( dirname( __FILE__ ) ) . '/languages'
		);

		/**
		 * creative common object
		 */
		require_once dirname(__FILE__)."/classes/creative-common.php";

		require_once dirname(__FILE__)."/classes/Render.php";
		$this->render = new Render($this);

		require_once dirname(__FILE__)."/classes/meta-fields.php";
		$this->meta_fields = new MetaFields($this);

		require_once dirname(__FILE__)."/classes/shortcode.php";
		$this->shortcode = new Shortcode($this);

		require_once dirname(__FILE__)."/classes/api.php";
		$this->api = new API($this);

	}

	/**
	 * @var null|Plugin $instance
	 */
	private static $instance = null;
	static function instance(){
		if(self::$instance == null) self::$instance = new Plugin();
		return self::$instance;
	}

	// ------------------------------------------------------------
	// deprecations
	// ------------------------------------------------------------
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

require_once dirname(__FILE__)."/public-functions.php";

