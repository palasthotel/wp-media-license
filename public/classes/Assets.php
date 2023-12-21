<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 16:59
 */

namespace Palasthotel\MediaLicense;


/**
 * @property Plugin plugin
 */
class Assets {

	/**
	 * API constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('init', array($this, 'register'), 1);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
	}

	function register(){
		wp_register_script(
			Plugin::HANDLE_API_JS,
			$this->plugin->getUrl("/js/api.js"),
			[],
			filemtime( $this->plugin->getPath( "/js/api.js")),
			true
		);
		$obj =  array(
			"resturl" => $this->plugin->rest->getCaptionsUrl(),
			"autoload" => apply_filters(Plugin::FILTER_AUTOLOAD_ASYNC_IMAGE_LICENSE, true),
		);
		wp_localize_script(Plugin::HANDLE_API_JS, "MediaLicense_API", $obj);
	}

	function enqueue_script(){
		wp_enqueue_script(Plugin::HANDLE_API_JS);
	}
}
