<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 17:06
 */

namespace MediaLicense;


class MetaFields {
	/**
	 * MetaFields constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		/**
		 * meta fields array for iteration
		 */
		$this->meta_fields = array();

		/**
		 * after initialize wordpress system
		 */
		add_action('init', array($this, 'init'));

		add_filter(Plugin::FILTER_ADD_FIELDS_NAME, array($this, "add_fields" ), 10, Plugin::FILTER_ADD_FIELDS_NUM_ARGS);

		/**
		 * add fields to attachments
		 */
		add_filter( 'attachment_fields_to_edit', array($this,'attachment_fields_to_edit'), 10, 2 );

		/**
		 * save custom meta field values
		 */
		add_action( 'edit_attachment', array($this, 'edit_attachment'));
	}

	/**
	 * init plugin when wordpress is ready
	 */
	public function init(){
		$this->meta_fields = apply_filters(Plugin::FILTER_ADD_FIELDS_NAME, $this->meta_fields);
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
		$fields[Plugin::META_LICENSE] = array(
			'label' => __('Lizense','media_license'),
			'input' => 'select',
			'value' => '',
			'helps' => __('Add license to caption if provided','media_license'),
			'selections' => $selections,
		);

		/**
		 * author field
		 */
		$fields[Plugin::META_AUTHOR] = array(
			'label' => __('Author','media_license'),
			'input' => 'text',
			'value' => '',
			'helps' => __('Add author to caption if provided','media_license'),
		);

		/**
		 * url field
		 */
		$fields[Plugin::META_URL] = array(
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
	 * @param $post \WP_Post the post object of the attachment
	 *
	 * @return array modified
	 *
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

}