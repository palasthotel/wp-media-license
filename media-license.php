<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.2.3
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 4.8
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\MediaLicense
 */

namespace MediaLicense;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class MediaLicense
 */
class Plugin {
	
	const DOMAIN = 'media_license';
	
	/**
	 * theme template parts
	 */
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_FILE_CAPTION = "media-license-caption.tpl.php";
	
	/**
	 * edit caption filter
	 */
	const FILTER_EDIT_CAPTION_NAME = "media_license_edit_caption";
	const FILTER_EDIT_CAPTION_NUM_ARGS = 3;
	
	/**
	 * add fields filter
	 */
	const FILTER_ADD_FIELDS_NAME = "media_license_add_fields";
	const FILTER_ADD_FIELDS_NUM_ARGS = 1;

	const FILTER_AUTOLOAD_ASYNC_IMAGE_LICENSE = "media_license_autoload_async_image_license";
	
	/**
	 * meta field key names
	 */
	const META_LICENSE = "media_license_info";
	const META_AUTHOR = "media_license_author";
	const META_URL = "media_license_url";

	const API_JS_HANDLE = "media-license-js";


	private static $instance = null;
	static function instance(){
		if(self::$instance == null) self::$instance = new Plugin();
		return self::$instance;
	}

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
		load_plugin_textdomain( self::DOMAIN, false, $this->dir . '/languages' );
		
		/**
		 * creative common object
		 */
		require_once dirname(__FILE__)."/inc/creative-common.inc";

		require_once dirname(__FILE__)."/inc/meta-fields.inc";
		$this->meta_fields = new MetaFields($this);

		require_once dirname(__FILE__)."/inc/shortcode.inc";
		$this->shortcode = new Shortcode($this);

		require_once dirname(__FILE__)."/inc/api.inc";
		$this->api = new API($this);
		
	}
	
}
Plugin::instance();

require_once dirname(__FILE__)."/public-functions.php";

