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
			$map[$id] = $this->can_read_caption($id) ? media_license_get_caption($id) : "";
		}

		return [
			"error" => false,
			"captions" => $map,
		];
	}

	/**
	 * The route is public and the ids come from the request, so it answers for
	 * attachments only. media_license_get_caption() reads the excerpt of whatever
	 * post it is given - for a private or draft post, or a password protected one,
	 * that is content nobody without access to it should see.
	 *
	 * An attachment's own status is "inherit"; one that is private or in the trash
	 * needs the right to read it.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function can_read_caption(int $id): bool {
		$post = get_post($id);
		if ( ! ( $post instanceof \WP_Post ) || 'attachment' !== $post->post_type ) {
			return false;
		}
		return 'inherit' === $post->post_status || current_user_can( 'read_post', $post->ID );
	}
}