<?php
/**
 * Plugin Name: Media License
 * Plugin URI: https://github.com/palasthotel/media-license
 * Description: Advanced caption with license for media files
 * Version: 1.1.0
 * Author: Palasthotel <rezeption@palasthotel.de> (in person: Edward Bock)
 * Author URI: http://www.palasthotel.de
 * Requires at least: 4.0
 * Tested up to: 4.6.1
 * License: http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @copyright Copyright (c) 2014, Palasthotel
 * @package Palasthotel\MediaLicense
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use MediaLicense\CreativeCommon;

/**
 * Class MediaLicense
 */
class MediaLicense {
	
	/**
	 * dir path to plugin
	 * @var string
	 */
	public $dir;
	
	/**
	 * theme template parts
	 */
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_FILE_CAPTION = "media-license-caption.tpl.php";
	
	/**
	 * edit caption filter
	 */
	const FILTER_EDIT_CAPTION_NAME = "media_license_edit_caption";
	const FILTER_EDIT_CAPTION_NUM_ARGS = 3;
	
	/**
	 * add fields filter
	 */
	const FILTER_ADD_FIELDS_NAME = "media_license_add_fields";
	const FILTER_ADD_FIELDS_NUM_ARGS = 1;
	
	/**
	 * meta field key names
	 */
	const META_LICENSE = "media_license_info";
	const META_AUTHOR = "media_license_author";
	const META_URL = "media_license_url";
	
	
	
	/**
	 * MediaLicenses constructor.
	 */
	public function __construct() {
		
		/**
		 * plugin directory
		 */
		$this->dir = plugin_dir_path(__FILE__);
		
		/**
		 * creative common object
		 */
		require_once $this->dir."/creative-common.inc";
		
		/**
		 * load translations
		 */
		load_plugin_textdomain( 'media_license', false, $this->dir . '/languages' );
		
		/**
		 * meta fields array for iteration
		 */
		$this->meta_fields = array();
		add_filter(self::FILTER_ADD_FIELDS_NAME, array($this, "add_fields" ), 10, self::FILTER_ADD_FIELDS_NUM_ARGS);
		
		/**
		 * after initialize wordpress system
		 */
		add_action('init', array($this, 'init'));
		
		/**
		 * add fields to attachments
		 */
		add_filter( 'attachment_fields_to_edit', array($this,'attachment_fields_to_edit'), 10, 2 );
		
		/**
		 * save custom meta field values
		 */
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
	 * init plugin when wordpress is ready
	 */
	public function init(){
		$this->meta_fields = apply_filters(self::FILTER_ADD_FIELDS_NAME, $this->meta_fields);
	}
	
	/**
	 * add fields fields
	 *
	 * @param $fields array
	 *
	 * @return array
	 */
	public function add_fields($fields){
		
		/**
		 * lizenses selection
		 */
		$list = CreativeCommon::getList();
		$selections = array();
		foreach($list as $slug => $item){
			$selections[] = array(
				"value" => $slug,
				"label" => $item['label'],
			);
		}
		$fields[self::META_LICENSE] = array(
			'label' => __('Lizense','media_license'),
			'input' => 'select',
			'value' => '',
			'helps' => __('Add license to caption if provided','media_license'),
			'selections' => $selections,
		);
		
		/**
		 * author field
		 */
		$fields[self::META_AUTHOR] = array(
			'label' => __('Author','media_license'),
			'input' => 'text',
			'value' => '',
			'helps' => __('Add author to caption if provided','media_license'),
		);
		
		/**
		 * url field
		 */
		$fields[self::META_URL] = array(
			'label' => __('Author URL','media_license'),
			'input' => 'text',
			'value' => '',
			'helps' => __('Link author if url is provided','media_license'),
		);
		
		return $fields;
	}
	
	/**
	 *
	 * @param $form_fields array fields for attachment
	 * @param $post WP_Post the post object of the attachment
	 *
	 * @return array modified form fields
	 */
	public function attachment_fields_to_edit($form_fields, $post){
		
		/**
		 * get values and append to form fields
		 */
		foreach($this->meta_fields as $meta_key => $form_definition){
			$fd = $form_definition;
			$value = get_post_meta( $post->ID, $meta_key, true );
			switch ($fd['input']){
				case "select":
					$fd['type'] = "html";
					$fd['html'] = "<select name='attachments[{$post->ID}][{$meta_key}]' id='attachments[{$post->ID}][{$meta_key}]'>";
					foreach($fd['selections'] as $selection){
						$_value = $selection["value"];
						$label = $selection['label'];
						$fd['input'] = 'html';
						$fd['html'].= "<option value='{$_value}' ".(($_value == $value)? "selected='selected'": "").">{$label}</option>";
					}
					break;
				default:
					$fd['value'] = (empty($value))? '': $value;
					break;
			}
			
			
			$form_fields[$meta_key] = $fd;
		}
		
		return $form_fields;
	}
	
	/**
	 * @param $attachment_id integer
	 */
	public function edit_attachment($attachment_id) {
		if(
			isset($_POST["attachments"]) &&
			isset($_POST["attachments"][$attachment_id])
		){
			$attachment_meta = $_POST["attachments"][$attachment_id];
			
			foreach ($this->meta_fields as $meta_key => $field_definition){
				if( isset($attachment_meta[$meta_key]) ){
					update_post_meta($attachment_id, $meta_key, sanitize_text_field($attachment_meta[$meta_key]) );
				}
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
			
			/**
			 * edit caption by filters
			 */
			$out['caption'] = apply_filters( self::FILTER_EDIT_CAPTION_NAME, $out['caption'], $out['caption'], $this->get_caption_info($attachment_id));
		}
		
		/**
		 * return output object which is modified atts
		 */
		return $out;
	}
	
	/**
	 * get all meta info for attachment
	 *
	 * @param $attachment_id
	 *
	 * @return array
	 */
	public function get_caption_info($attachment_id){
		$info = array();
		foreach ($this->meta_fields as $meta_key => $field_definition){
			$value = get_post_meta( $attachment_id, $meta_key, true );
			$info[$meta_key] = (empty($value))? '': $value;
		}
		return $info;
	}
	
	/**
	 * @param $caption string modified caption
	 * @param $original_caption string unmodified caption
	 * @param $info array media license info
	 *
	 * @return string modified caption
	 */
	public function edit_caption($caption, $original_caption, $info){
		/**
		 * dynamic varaibles
		 */
		extract($info, EXTR_PREFIX_SAME, "ml");
		
		$license = new CreativeCommon($info["media_license_info"]);
		
		/**
		 * get template contents
		 */
		ob_start();
		if ( $overridden_template = locate_template( self::THEME_FOLDER."/".self::TEMPLATE_FILE_CAPTION ) ) {
			include $overridden_template;
		} else {
			include $this->dir . '/templates/'.self::TEMPLATE_FILE_CAPTION;
		}
		$caption = ob_get_contents();
		ob_end_clean();
		
		return $caption;
	}
	
}

/**
 * save to global
 * @var $media_license MediaLicense
 */
global $media_license;
$media_license = new MediaLicense();

/**
 * get caption with info template
 * @param $attachment_id
 *
 * @return mixed|void
 */
function media_license_get_caption($attachment_id){
	global $media_license;
	$post = get_post($attachment_id);
	return apply_filters(
		MediaLicense::FILTER_EDIT_CAPTION_NAME,
		$post->post_excerpt,
		$post->post_excerpt,
		$media_license->get_caption_info($attachment_id)
	);
}