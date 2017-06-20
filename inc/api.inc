<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 16:59
 */

namespace MediaLicense;


class API {

	const AJAX_ACTION = "media-license";

	/**
	 * API constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('init', array($this, 'register'), 1);
		add_action('wp_ajax_'.self::AJAX_ACTION, array($this, "ajax"));
		add_action('wp_ajax_nopriv_'.self::AJAX_ACTION, array($this, "ajax"));
	}

	function register(){
		wp_register_script(
			Plugin::API_JS_HANDLE,
			$this->plugin->url."/js/api.js",
			array("jquery"),
			filemtime($this->plugin->dir."/js/api.js")
		);
		$obj =  array(
			"ajaxurl" => admin_url( 'admin-ajax.php' ),
			"params" => array(
				"action" => self::AJAX_ACTION,
			),
			"autoload" => apply_filters(Plugin::FILTER_AUTOLOAD_ASYNC_IMAGE_LICENSE, false),
		);
		wp_localize_script(Plugin::API_JS_HANDLE, "MediaLicense_API", $obj);
	}

	function ajax(){
		if(empty($_GET["ids"])) exit;

		if(!is_array($_GET["ids"])){
			wp_send_json(array(
				"error" => true,
				"msg" => "no valid request",
			),200);
			exit;
		}


		$map = array();

		for($i = 0; $i < count($_GET["ids"]); $i++){
			$id = intval($_GET["ids"][$i]);

			$map[$id] = media_license_get_caption($id);

		}

		wp_send_json(array(
			"error" => false,
			"captions" => $map,
		));
		exit;
	}
}