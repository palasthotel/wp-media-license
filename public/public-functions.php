<?php

use Palasthotel\MediaLicense\Plugin;

/**
 * @return Plugin
 */
function media_license_get_plugin(){
	return Plugin::instance();
}

/**
 * get caption with info template
 * @param $attachment_id
 *
 * @return mixed
 */
function media_license_get_caption($attachment_id){
	$post = get_post($attachment_id);
	return apply_filters(
		Plugin::FILTER_EDIT_CAPTION,
		$post->post_excerpt,
		$post->post_excerpt,
		media_license_get_plugin()->shortcode->get_caption_info($attachment_id)
	);
}
