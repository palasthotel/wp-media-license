<?php

use Palasthotel\MediaLicense\Plugin;

/**
 * @return Plugin
 */
function media_license_get_plugin(){
	return Plugin::instance();
}

/**
 * The id the list of licenses links to, and that Gutenberg::add_image_anchors()
 * puts on the matching image in the post content.
 *
 * @param int $attachment_id
 *
 * @return string
 */
function media_license_get_image_anchor($attachment_id){
	return Plugin::ANCHOR_PREFIX . intval($attachment_id);
}

/**
 * get caption with info template
 * @param $attachment_id
 *
 * @return mixed
 */
function media_license_get_caption($attachment_id){
	$post = get_post($attachment_id);
	$excerpt = "";
	if(is_object($post) && isset($post->post_excerpt)){
		$excerpt = $post->post_excerpt;
	}
	return apply_filters(
		Plugin::FILTER_EDIT_CAPTION,
		$excerpt,
		$excerpt,
		media_license_get_plugin()->shortcode->get_caption_info($attachment_id)
	);
}
