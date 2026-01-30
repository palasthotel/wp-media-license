<?php


namespace Palasthotel\MediaLicense;

/**
 * @property Plugin plugin
 */
class Footer
{
    public Plugin $plugin;
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        add_action('wp_footer', [$this, 'append_data_attributes_container'], 20);
    }

    // container to display collected license information of 
    // gutenberg blocks that don't display their license under
    // the image
    public function append_data_attributes_container(): void
    {
        if (is_admin()) return;

        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME);
        if (!is_array($settings)) $settings = [];
        $collect_is_active = $settings[$this->plugin::SETTINGS_FIELD_COLLECT] ?? false;

        if ($collect_is_active) {
?>
            <div id="media-license-footer-container">
            </div>
<?php
        }
    }
}
