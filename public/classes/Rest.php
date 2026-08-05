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
    public Plugin $plugin;

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

		// The frontend requests captions for the images on one page, never more than a
		// handful - cap it so a crafted ids array cannot force hundreds of
		// media_license_get_caption() lookups per request.
		$ids = array_slice($ids, 0, 100);

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