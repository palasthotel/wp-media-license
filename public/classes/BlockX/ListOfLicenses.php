<?php


namespace Palasthotel\MediaLicense\BlockX;


use Palasthotel\MediaLicense\Plugin;
use Palasthotel\WordPress\BlockX\Blocks\_BlockType;
use Palasthotel\WordPress\BlockX\Model\BlockId;
use Palasthotel\WordPress\BlockX\Model\ContentStructure;
use Palasthotel\WordPress\BlockX\Widgets\Hidden;
use stdClass;

class ListOfLicenses extends _BlockType {

	public function id(): BlockId {
		return BlockId::build(Plugin::DOMAIN, "list-of-licenses");
	}

	public function category(): string {
		return "widgets";
	}

	public function title(): string {
		return __("List of media licenses", Plugin::DOMAIN);
	}

	public function contentStructure(): ContentStructure {
		return new ContentStructure([
			Hidden::build("imageIds", []),
		]);
	}

	public function registerBlockTypeArgs(): array {
		$args = parent::registerBlockTypeArgs();
		$args["icon"] = "images-alt2";
		$args["description"] = __("Collects information about all media licenses used in this post.", Plugin::DOMAIN);
		return $args;
	}

	public function script(): string {
		return Plugin::HANDLE_GUTENBERG_JS;
	}

	public function prepare( stdClass $content ): stdClass {
		$content = parent::prepare( $content );
		if(!isset($content->imageIds) || !is_array($content->imageIds)){
			$content->imageIds = [];
		}
		$content->imageIds = apply_filters(Plugin::FILTER_BLOCK_LIST_OF_LICENSES_IMAGE_IDS, $content->imageIds);
		$content->captions = [];
		foreach ($content->imageIds as $imageId){
			$content->captions[$imageId] = media_license_get_caption($imageId);
		}
		return $content;
	}
}