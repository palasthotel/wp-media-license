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
        add_filter('render_block', [$this, 'mark_append_caption_optout'], 11, 2);
    }

    /**
     * Marks images whose block opted out of the appended license info, so the
     * frontend script can skip them. Only an explicit opt-out is written: a block
     * without the attribute - which is all existing content - keeps appending, so
     * updating the plugin changes nothing that is already published.
     */
    public function mark_append_caption_optout(string $block_content, array $block): string
    {
        $explicit = $block['attrs'][$this->plugin::BLOCK_ATTRIBUTE_APPEND] ?? null;

        if (is_null($explicit) || $explicit) {
            return $block_content;
        }

        if (empty($block['blockName']) || !class_exists('\WP_HTML_Tag_Processor')) {
            return $block_content;
        }

        $tags = new \WP_HTML_Tag_Processor($block_content);
        $changed = false;
        while ($tags->next_tag('img')) {
            $tags->set_attribute('data-media-license-skip', 'true');
            $changed = true;
        }

        return $changed ? $tags->get_updated_html() : $block_content;
    }

    public function enqueue_block_editor_assets()
    {
        // dist/ is built by the pipeline and is not in the repository. If it is missing
        // (a source checkout that was never built), there is simply no Gutenberg block
        // rather than a fatal.
        $asset = $this->plugin->path . "/dist/media-license.asset.php";
        if (!file_exists($asset)) {
            return;
        }
        $info = include $asset;
        wp_enqueue_script(
            Plugin::HANDLE_GUTENBERG_JS,
            $this->plugin->getUrl("/dist/media-license.js"),
            $info["dependencies"],
            $info["version"]
        );
        wp_localize_script(
            Plugin::HANDLE_GUTENBERG_JS,
            "MediaLicenseEditor",
            [
                "blocks" => $this->get_append_caption_block_types(),
                "i18n" => [
                    "panel_title" => __('Media license', 'media-license'),
                    "append_caption" => __('Append license info', 'media-license'),
                    "append_caption_help" => __('Shows the license and author below the image on the front end.', 'media-license'),
                ],
            ]
        );
    }

    /**
     * Block types that get the "append license info" toggle. Only blocks that
     * render an image of their own are worth offering it on.
     */
    public function get_append_caption_block_types(): array
    {
        return apply_filters(
            Plugin::FILTER_APPEND_CAPTION_BLOCK_TYPES,
            ['core/image', 'core/gallery', 'core/media-text', 'core/cover']
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
        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME);
        $central_block_setting = $settings[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN] ?? 'legacy';
        // settings: toogle sammeln theme / plugin
        //
        // im footer div erstellen
        $individual_block_settings = apply_filters(Plugin::FILTER_INDIVIDUAL_BLOCK_SETTINGS, [], $central_block_setting);

        $blockname = $block['blockName'];
        // gutenberg blocks can be nested. So we have to lock the specific block rules somehow after we apply them.
        // if we don't do that, other blocks can overwrite the rule again, since we loop through all img, contained in 
        // a block below
        $has_overwritten_setting = array_key_exists($blockname, $individual_block_settings);
        $block_setting = $has_overwritten_setting ? $individual_block_settings[$blockname] : $central_block_setting;

        // return early if data-attributes are not activated for blocks
        if ($block_setting === 'legacy' && !$has_overwritten_setting) {
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
        // blocks can contain multiple imgs
        while ($tags->next_tag('img')) {

            // return if a block already got set by its own overwrite rule
            // we need this so nested block don't overwrite unwanted
            if (!$has_overwritten_setting && $tags->get_attribute('data-media-license-block-lock-setting')) {
                return $block_content;
            }

            if ($block_setting === 'data-attribute') {
                $tags->set_attribute('data-media-license-block-use-data-attribute', 'true');
            }

            if ($block_setting === 'collect') {
                $tags->set_attribute('data-media-license-block-use-data-attribute', 'true');
                $tags->set_attribute('data-media-license-block-collect', 'true');
            }

            if ($block_setting === 'legacy') {
                $tags->remove_attribute('data-media-license-block-use-data-attribute');
                $tags->remove_attribute('data-media-license-block-collect');
            }

            if ($has_overwritten_setting) {
                $tags->set_attribute('data-media-license-block-lock-setting', 'true');
            }

            $changed = true;
        }

        return $changed ? $tags->get_updated_html() : $block_content;
    }
}
