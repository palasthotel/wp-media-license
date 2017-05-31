<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 17:01
 */

/**
 * @return \MediaLicense\Plugin
 */
function media_license_get_plugin(){
	return MediaLicense\Plugin::instance();
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
		\MediaLicense\Plugin::FILTER_EDIT_CAPTION_NAME,
		$post->post_excerpt,
		$post->post_excerpt,
		media_license_get_plugin()->shortcode->get_caption_info($attachment_id)
	);
}