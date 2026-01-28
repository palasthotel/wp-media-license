<?php


namespace Palasthotel\MediaLicense;


use Palasthotel\MediaLicense\BlockX\ListOfLicenses;

/**
 * @property Plugin plugin
 */
class Gutenberg
{
    public Plugin $plugin;
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        add_action('enqueue_block_editor_assets', [$this, "enqueue_block_editor_assets"]);
        add_action('blockx_collect', [$this, 'collect']);
        add_filter('blockx_add_templates_paths', [$this, 'add_templates_paths']);
        add_filter('render_block', [$this, 'add_data_attribute_to_blockmarkup'], 10, 2);
    }

    public function enqueue_block_editor_assets()
    {
        $info = include $this->plugin->path . "/js/gutenberg/media-license.asset.php";
        wp_enqueue_script(
            Plugin::HANDLE_GUTENBERG_JS,
            $this->plugin->getUrl("/js/gutenberg/media-license.js"),
            $info["dependencies"],
            $info["version"]
        );
    }

    public function collect(\Palasthotel\WordPress\BlockX\Gutenberg $gutenberg)
    {
        $gutenberg->addBlockType(new ListOfLicenses());
    }

    public function add_templates_paths($paths)
    {
        $paths[] = $this->plugin->path . "/templates/";
        return $paths;
    }

    /** 
     * This functions adds a data-attribute to the block markup of the gutenberg blocks
     * This is used to distinguish imgs that are part of gutenberg blocks from all other imgs
     * We do this because the block img usually don't want the license information rendered below
     * the img.
     * */
    public function add_data_attribute_to_blockmarkup(string $block_content, array $block): string
    {

        $blockname = $block['blockName'];

        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME);
        $enable = (bool) ($settings[$this->plugin::SETTINGS_FIELD_ENABLE_BLOCK_DATA_ATTRIBUTES] ?? false);
        $exclude_key = $this->plugin::SETTINGS_FIELD_EXCLUDE_BLOCK_DATA_ATTRIBUTES_PREFIX . $blockname;
        $exlude_block = (bool) ($settings[$exclude_key] ?? false);

        // return early if data-attributes are not activated for blocks
        if (!$enable) {
            return $block_content;
        }

        if (empty($blockname) || $blockname === '') {
            return $block_content;
        }

        if (!class_exists('\WP_HTML_Tag_Processor')) {
            return $block_content;
        }

        $tags = new \WP_HTML_Tag_Processor($block_content);

        $changed = false;
        while ($tags->next_tag('img')) {
            $tags->set_attribute('data-media-license-block-flag', 'is-block');
            // exclude specific blocks set in admin-settings-page 
            if ($exlude_block) {
                $tags->set_attribute('data-media-license-block-exclude', 'true');
            }
            $changed = true;
        }

        return $changed ? $tags->get_updated_html() : $block_content;
    }
}
