<?php


namespace Palasthotel\MediaLicense\BlockX;


use Palasthotel\MediaLicense\Plugin;
use Palasthotel\WordPress\BlockX\Blocks\_BlockType;
use Palasthotel\WordPress\BlockX\Model\BlockId;
use Palasthotel\WordPress\BlockX\Model\ContentStructure;
use Palasthotel\WordPress\BlockX\Widgets\Hidden;
use stdClass;

class ListOfLicenses extends _BlockType {

	// Block namespaces are validated against /^[a-z0-9-]+\/[a-z0-9-]+$/ - no underscores -
	// so Plugin::DOMAIN ("media_license") can't be used here directly, it fails that
	// check silently (WP_Block_Type_Registry::register() logs a doing_it_wrong and
	// returns false, so the block is simply never registered). This also matches the
	// key src/gutenberg.js already registers its editor component under.
	const BLOCK_NAMESPACE = "media-license";

	public function id(): BlockId {
		return BlockId::build(self::BLOCK_NAMESPACE, "list-of-licenses");
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