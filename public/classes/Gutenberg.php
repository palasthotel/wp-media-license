<?php


namespace Palasthotel\MediaLicense;


use Palasthotel\MediaLicense\BlockX\ListOfLicenses;

/**
 * @property Plugin plugin
 */
class Gutenberg {
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'enqueue_block_editor_assets', [$this, "enqueue_block_editor_assets"]);
		add_action('blockx_collect', [$this, 'collect']);
		add_filter('blockx_add_templates_paths', [$this, 'add_templates_paths']);
	}

	public function enqueue_block_editor_assets(){
		$info = include $this->plugin->path . "/js/gutenberg/media-license.asset.php";
		wp_enqueue_script(
			Plugin::HANDLE_GUTENBERG_JS,
			$this->plugin->getUrl("/js/gutenberg/media-license.js"),
			$info["dependencies"],
			$info["version"]
		);
	}

	public function collect(\Palasthotel\WordPress\BlockX\Gutenberg $gutenberg){
		$gutenberg->addBlockType(new ListOfLicenses());
	}

	public function add_templates_paths($paths){
		$paths[] = $this->plugin->path . "/templates/";
		return $paths;
	}
}