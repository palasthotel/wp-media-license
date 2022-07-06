<?php

namespace Palasthotel\MediaLicense\Headless;

use Palasthotel\WordPress\Headless\Extensions\AbsPostExtensionPost;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class FeaturedMedia extends AbsPostExtensionPost {

	function response( WP_REST_Response $response, WP_Post $post, WP_REST_Request $request ): WP_REST_Response {
		$data = $response->get_data();

		$id = get_post_thumbnail_id($post);
		$info = media_license_get_plugin()->shortcode->get_caption_info($id);
		$data["featured_media_license"] = $info;

		$response->set_data( $data );
		return $response;
	}
}