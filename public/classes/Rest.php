<?php


namespace Palasthotel\MediaLicense;


/**
 * @property Plugin plugin
 */
class Rest {

	/**
	 * API constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'rest_api_init', [$this, 'init']);
	}
	public function init(){
		register_rest_route( Plugin::DOMAIN.'/v1', '/captions', array(
			'methods' => 'GET',
			'callback' => [$this, 'captions'],
			'args' => [
				'ids' => [
					'validate_callback' => function($param, $request, $key){
						return is_array($param);
					}
				],
			],
			'permission_callback' => '__return_true',
		) );
	}

	public function getCaptionsUrl(){
		return rest_url(Plugin::DOMAIN."/v1/captions");
	}

	public function captions(\WP_REST_Request $request){
		$ids = $request->get_param("ids");

		$map = array();

		for($i = 0; $i < count($ids); $i++){
			$id = intval($ids[$i]);
			$map[$id] = media_license_get_caption($id);
		}

		return [
			"error" => false,
			"captions" => $map,
		];
	}
}