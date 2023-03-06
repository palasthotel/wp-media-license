<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 17:06
 */

namespace Palasthotel\MediaLicense;


class MetaFields {

	/**
	 * meta fields array for iteration
	 * @var null|array $meta_fields
	 */
	private $meta_fields = null;

	/**
	 * MetaFields constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct($plugin) {

		/**
		 * plugin default fields
		 */
		add_filter(Plugin::FILTER_ADD_FIELDS, array($this, "add_fields" ));

		/**
		 * add fields to attachments
		 */
		add_filter( 'attachment_fields_to_edit', array($this,'attachment_fields_to_edit'), 10, 2 );

		/**
		 * save custom meta field values
		 */
		add_action( 'edit_attachment', array($this, 'edit_attachment'));

		/**
		 * copy license info if image was edited
		 */
		add_filter('wp_edited_image_metadata', [$this, 'wp_edited_image_metadata'], 10, 3);
	}

	/**
	 * init plugin when wordpress is ready
	 */
	public function init(){
		$this->meta_fields = apply_filters(Plugin::FILTER_ADD_FIELDS, $this->meta_fields);
	}

	/**
	 * access registered meta fields
	 * @return array
	 */
	public function getMetaFields(){
		if(null == $this->meta_fields){
			$this->meta_fields = apply_filters(Plugin::FILTER_ADD_FIELDS, array());
		}
		return $this->meta_fields;
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
		 * licenses selection
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
			'label' => __('License','media_license'),
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
		foreach($this->getMetaFields() as $meta_key => $form_definition){
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

			foreach ($this->getMetaFields() as $meta_key => $field_definition){
				if( isset($attachment_meta[$meta_key]) ){
					update_post_meta($attachment_id, $meta_key, sanitize_text_field($attachment_meta[$meta_key]) );
				}
			}
		}

	}

	public function wp_edited_image_metadata( $new_image_meta, $new_attachment_id, $attachment_id){

		foreach($this->getMetaFields() as $meta_key => $field_definition){
			$value = get_post_meta( $attachment_id, $meta_key, true );
			update_post_meta($new_attachment_id, $meta_key, $value);
			$new_image_meta["image_meta"][$meta_key] = $value;
		}

		return $new_image_meta;
	}

}
