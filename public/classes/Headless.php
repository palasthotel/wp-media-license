<?php

namespace Palasthotel\MediaLicense;

use Palasthotel\MediaLicense\Headless\GalleryBlockPreparation;
use Palasthotel\MediaLicense\Headless\ImageBlockPreparation;
use Palasthotel\WordPress\Headless\Model\BlockPreparations;

class Headless {

	/**
	 * Render constructor
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		add_action('plugins_loaded', [$this, 'plugins_loaded']);
	}

	function plugins_loaded(){
		if(!class_exists("\Palasthotel\WordPress\Headless\Plugin")){
			return;
		}
		add_action(\Palasthotel\WordPress\Headless\Plugin::ACTION_REGISTER_BLOCK_PREPARATION_EXTENSIONS, [$this,'extensions'], 20);
	}

	public function extensions(BlockPreparations $extensions){
		$extensions->add(new ImageBlockPreparation());
	}


}