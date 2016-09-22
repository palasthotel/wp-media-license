<?php
/**
 * Plugin Name: Asset Licenses
 * Plugin URI: https://github.com/palasthotel/asset-licenses
 * Description: Advanced caption with license for assets
 * Version: 1.0
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 4.6
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\AssetLicenses
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AssetLicenses {
	
	/**
	 * meta field key names
	 */
	const META_LICENSE = "asset_license_info";
	const META_AUTHOR = "asset_license_author";
	
	/**
	 * AssetLicenses constructor.
	 */
	public function __construct() {
		/**
		 * add fields to attachments
		 */
		add_filter( 'attachment_fields_to_edit', array($this,'attachment_fields_to_edit'), 10, 2 );
		
		/**
		 * save custom meta field values
		 */
		add_action( 'save_post_attachment', array($this, 'edit_attachment'));
		
		/**
		 * filter is called by shortcode_atts
		 */
		add_filter('shortcode_atts_caption', array($this, 'shortcode_atts_caption'), 10, 4);
		
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
		if( isset($_POST[self::META_LICENSE]) ){
			update_post_meta($attachment_id, self::META_LICENSE, sanitize_text_field($_POST[self::META_LICENSE]) );
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
		// TODO: only handle if there is license information
		// TODO: use an template thats overrideable in theme
		$media_id = get_the_ID();
		$license = get_post_meta($media_id,self::META_LICENSE, true);
		
		if( !empty($license) ){
			$out['caption'] = $out['caption'].$license;
		}
		
		/**
		 * return output object which is modified atts
		 */
		return $out;
	}
	
}
new AssetLicenses();