<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.0
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 4.6
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\MediaLicense
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class MediaLicense {
	
	/**
	 * meta field key names
	 */
	const META_LICENSE = "media_license_info";
	const META_AUTHOR = "media_license_author";
	
	/**
	 * modify caption filter
	 */
	const FILTER_EDIT_CAPTION_NAME = "media_license_edit_caption";
	const FILTER_EDIT_CAPTION_NUM_ARGS = 3;
	
	/**
	 * MediaLicenses constructor.
	 */
	public function __construct() {
		/**
		 * add fields to attachments
		 */
		add_filter( 'attachment_fields_to_edit', array($this,'attachment_fields_to_edit'), 10, 2 );
		
		/**
		 * save custom meta field values
		 */
//		add_action( 'save_post_attachment', array($this, 'edit_attachment'));
		add_action( 'edit_attachment', array($this, 'edit_attachment'));
		
		/**
		 * filter is called by shortcode_atts
		 */
		add_filter('shortcode_atts_caption', array($this, 'shortcode_atts_caption'), 10, 4);
		
		/**
		 * edit caption filter
		 */
		add_filter(self::FILTER_EDIT_CAPTION_NAME, array($this, 'edit_caption'), 10, self::FILTER_EDIT_CAPTION_NUM_ARGS);
		
	}
	
	/**
	 *
	 * @param $form_fields array fields for attachment
	 * @param $post WP_Post the post object of the attachment
	 *
	 * @return array modified form fields
	 */
	public function attachment_fields_to_edit($form_fields, $post){
		
		$license_text = get_post_meta( $post->ID, self::META_LICENSE, true );
		$form_fields[self::META_LICENSE] = array(
			'label' => 'Lizense text',
			'input' => 'text',
			'value' => (empty($license_text))? '': $license_text,
			'helps' => 'If provided, add license to caption.',
		);
		
		$author = get_post_meta( $post->ID, self::META_AUTHOR, true );
		$form_fields[self::META_AUTHOR] = array(
			'label' => 'Author',
			'input' => 'text',
			'value' => (empty($author))? '': $author,
			'helps' => 'If provided, add author to caption.',
		);
		
		return $form_fields;
	}
	
	/**
	 * @param $attachment_id integer
	 */
	function edit_attachment($attachment_id) {
		if(
			isset($_POST["attachments"]) &&
			isset($_POST["attachments"][$attachment_id])
		){
			$attachment_meta = $_POST["attachments"][$attachment_id];
			
			if( isset($attachment_meta[self::META_LICENSE]) ){
				update_post_meta($attachment_id, self::META_LICENSE, sanitize_text_field($attachment_meta[self::META_LICENSE]) );
			}
			
			if( isset($attachment_meta[self::META_AUTHOR]) ){
				update_post_meta($attachment_id, self::META_AUTHOR, sanitize_text_field($attachment_meta[self::META_AUTHOR]) );
			}
		}
		
		
	}
	
	/**
	 * modify caption
	 *
	 * @param $out array
	 * @param $pairs array
	 * @param $atts array
	 * @param $shortcode string
	 *
	 * @return array
	 */
	public function shortcode_atts_caption($out, $pairs, $atts, $shortcode ){
		
		$attachment_id = (int)str_replace("attachment_","",$out["id"]);
		
		if($attachment_id > 0){
			$license = get_post_meta($attachment_id,self::META_LICENSE, true);
			$author = get_post_meta($attachment_id,self::META_AUTHOR, true);
			
			$original = $out['caption'];
			
			$info = (object)array(
				'license' => (empty($license))? null: $license,
				'author' => (empty($author))? null: $author,
			);
			
			$out['caption'] = apply_filters( self::FILTER_EDIT_CAPTION_NAME, $original, $original, $info);
		}
		
		/**
		 * return output object which is modified atts
		 */
		return $out;
	}
	
	/**
	 * @param $caption string modified caption
	 * @param $original string unmodified caption
	 * @param $info object media license info
	 *
	 * @return string modified caption
	 */
	public function edit_caption($caption, $original, $info){
		
		if( $info->author != null){
			$caption.= " by ".$info->author;
		}
		
		if( $info->license != null){
			$caption.= " ".$info->license;
		}
		
		return $caption;
	}
	
}
new MediaLicense();