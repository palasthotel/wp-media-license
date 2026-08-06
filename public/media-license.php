<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.6.9
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock, Lucas Regalar)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 6.6
 * Tested up to: 7.0.2
 * Requires PHP: 8.0
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: media-license
 * Domain Path: /languages
 * @copyright Copyright (c) 2023, Palasthotel
 * @package Palasthotel\MediaLicense
 */

namespace Palasthotel\MediaLicense;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// composer package name is defined in plugins composer.json
const COMPOSER_PACKAGE = 'palasthotel/media-license';

$centralAutoloader = (defined('PALASTHOTEL_COMPOSER_CENTRAL') && constant('PALASTHOTEL_COMPOSER_CENTRAL'))
    || did_action('palasthotel/central_autoloader_loaded') > 0;

$managedByCentralAutoloader = false;
if ($centralAutoloader && class_exists('\Composer\InstalledVersions', true)) { //checks if autoloader exists
    try {
        if (\Composer\InstalledVersions::isInstalled(COMPOSER_PACKAGE)) { // this only checks for some version not the directory 
            $installPath = \Composer\InstalledVersions::getInstallPath(COMPOSER_PACKAGE);
            $managedByCentralAutoloader = $installPath && realpath($installPath) && realpath($installPath) === realpath(__DIR__); // check if the it is acutally THIS version and dir installed
        }
    } catch (\Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[' . COMPOSER_PACKAGE . '] InstalledVersions exception: ' . $e->getMessage());
        }
    }
}

if (!$centralAutoloader || !$managedByCentralAutoloader) {
    $local = __DIR__ . '/vendor/autoload.php';
    if (is_readable($local)) {
        require_once $local;
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Bitte "composer install" im ' . COMPOSER_PACKAGE .  ' Plugin-Ordner ausführen.</p></div>';
        });
        return;
    }
}
if (defined('PH_CENTRAL_AUTOLOADER_DEBUG') && PH_CENTRAL_AUTOLOADER_DEBUG) {
    error_log('[MediaLicense] centralAutoloader=' . ($centralAutoloader ? '1' : '0')
        . ' classExists=' . (class_exists('\Composer\InstalledVersions', false) ? '1' : '0')
        . ' installPath=' . ($installPath ?? '(none)')
        . ' managed=' . ($managedByCentralAutoloader ? '1' : '0'));
}

class Plugin extends \Palasthotel\MediaLicense\Components\Plugin {

	// The REST namespace and the Tools submenu slug, kept as-is so published
	// endpoints and bookmarked URLs stay valid. This is NOT the text domain -
	// that has to match the plugin slug, see TEXT_DOMAIN below.
	const DOMAIN = 'media_license';

	// WordPress looks for languages/<text domain>-<locale>.mo, and language packs
	// from wordpress.org are built against the plugin slug. With 'media_license'
	// here nothing ever matched the shipped media-license-*.mo files, so no
	// translation loaded at all. Every __() call passes the literal, because
	// make-pot and GlotPress only read literal domains.
	const TEXT_DOMAIN = 'media-license';

    const DISPLAY_NAME = 'Media License';

    /**
     * Settings page
     */
    const SETTINGS_PAGE_SLUG = 'media-license-settings';
    const SETTINGS_OPTIONS_GROUP = 'media_license_settings_group';
    const SETTINGS_OPTION_NAME = 'media_license_settings';
    const SETTINGS_SECTION_MAIN = 'media_license_main';
    const SETTINGS_SECTION_OVERWRITE = 'media_license_overwrite';
    const SETTINGS_FIELD_BLOCKS_MAIN = 'main_block_setting';
    const SETTINGS_FIELD_COLLECT = 'collect_data_attributes';

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
    const FILTER_ENABLE_FRONTEND_STYLES = 'media_license_enable_frontend_styles';
    const FILTER_INDIVIDUAL_BLOCK_SETTINGS =  'media_license_individual_block_settings';

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
	const HANDLE_ADMIN_TEXTAREA_JS = "media-license-admin-textarea";

    /**
     * hanlde of CSS asset
     */
    const HANDLE_FRONTEND_CSS = 'media-license-frontend';

	/**
	 * MediaLicenses constructor.
	 */

    public Render $render;
    public MetaFields $meta_fields;
    public Shortcode $shortcode;
    public Assets $assets;
    public Rest $rest;
    public Gutenberg $gutenberg;
    public Headless $headless;
    public AdminPage $admin_page;
    public Footer $footer;

	public function onCreate(): void {

		$this->loadTextdomain(
			self::TEXT_DOMAIN,
			"languages"
		);

		$this->render      = new Render( $this );
		$this->meta_fields = new MetaFields( $this );
		$this->shortcode   = new Shortcode( $this );
		$this->assets      = new Assets( $this );
		$this->rest        = new Rest($this);
		$this->gutenberg   = new Gutenberg( $this );
        $this->admin_page  = new AdminPage( $this );
        $this->headless    = new Headless( $this );
        $this->footer      = new Footer( $this );

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
