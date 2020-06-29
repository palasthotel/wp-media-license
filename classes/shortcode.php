<?php

namespace Palasthotel\MediaLicense;


/**
 * @property Plugin plugin
 */
class Shortcode {

	/**
	 * Shortcode constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {

		$this->plugin = $plugin;

		/**
		 * filter is called by shortcode_atts
		 */
		add_filter('shortcode_atts_caption', array($this, 'shortcode_atts_caption'), 10, 4);

		/**
		 * edit caption filter
		 */
		add_filter(Plugin::FILTER_EDIT_CAPTION, array($this, 'edit_caption'), 10, 3);

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
			$out['caption'] = apply_filters( Plugin::FILTER_EDIT_CAPTION, $out['caption'], $out['caption'], $this->get_caption_info($attachment_id));
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
		foreach ($this->plugin->meta_fields->getMetaFields() as $meta_key => $field_definition){
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
		include $this->plugin->render->get_template_path(Plugin::TEMPLATE_FILE_CAPTION );
		$caption = ob_get_contents();
		ob_end_clean();

		return $caption;
	}
}
