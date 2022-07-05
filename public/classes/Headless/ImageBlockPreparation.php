<?php

namespace Palasthotel\MediaLicense\Headless;

use Palasthotel\WordPress\Headless\Interfaces\IBlockPreparation;
use Palasthotel\WordPress\Headless\Model\BlockName;

class ImageBlockPreparation implements IBlockPreparation {

	function blockName(): BlockName {
		return new BlockName("core", "image");
	}

	function prepare( array $block ): array {

		if(isset($block["attrs"]) && isset($block["attrs"]["id"])){
			$imageId = $block["attrs"]["id"];

			$info = media_license_get_plugin()->shortcode->get_caption_info($imageId);
			$block["attrs"]["media_license"] = $info;

		}

		return $block;
	}
}