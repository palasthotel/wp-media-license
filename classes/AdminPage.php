<?php

namespace Palasthotel\MediaLicense;

class AdminPage
{

    public Plugin $plugin;
    // this is a list of all the settings fields for all the gutenberg blocks
    private array $individual_block_settings_field_list;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->individual_block_settings_field_list = [];
        add_action('admin_menu', [$this, 'menu_pages']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function menu_pages(): void
    {
        add_submenu_page(
            'tools.php',
            $this->plugin::DISPLAY_NAME,
            $this->plugin::DISPLAY_NAME,
            'manage_options',
            $this->plugin::DOMAIN,
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void
    {
        register_setting(
            $this->plugin::SETTINGS_OPTIONS_GROUP,
            $this->plugin::SETTINGS_OPTION_NAME,
            [$this, 'sanitize_settings']
        );

        add_settings_section(
            $this->plugin::SETTINGS_SECTION_MAIN,
            'Allgemein', // __('General', $this->plugin::DOMAIN)
            [$this, 'render_settings_section'],
            $this->plugin::SETTINGS_PAGE_SLUG,
        );

        add_settings_field(
            $this->plugin::SETTINGS_FIELD_BLOCKS_MAIN,
            'Choose main block option',
            [$this, 'render_main_block_options'],
            $this->plugin::SETTINGS_PAGE_SLUG,
            $this->plugin::SETTINGS_SECTION_MAIN,
        );

        add_settings_field(
            $this->plugin::SETTINGS_FIELD_COLLECT,
            'Collect data-attribute data in footer',
            [$this, 'render_checkbox'],
            $this->plugin::SETTINGS_PAGE_SLUG,
            $this->plugin::SETTINGS_SECTION_MAIN,
            ['key' => $this->plugin::SETTINGS_FIELD_COLLECT]
        );

        add_settings_section(
            $this->plugin::SETTINGS_SECTION_OVERWRITE,
            'Overwrites via "media_license_individual_block_settings" hook', // __('General', $this->plugin::DOMAIN)
            [$this, 'render_overwrite_section'],
            $this->plugin::SETTINGS_PAGE_SLUG,
        );
    }

    public function sanitize_settings($input): array
    {
        $out = [];

        $out[$this->plugin::SETTINGS_FIELD_COLLECT] =
            ! empty($input[$this->plugin::SETTINGS_FIELD_COLLECT]) ? 1 : 0;

        $allowed_main_block_options = [
            'legacy',
            'data-attribute',
            'collect'
        ];

        if (
            isset($input[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN]) &&
            in_array($input[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN], $allowed_main_block_options, true)
        ) {
            $out[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN] = sanitize_text_field($input[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN]);
        } else {
            $out[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN] = 'legacy';
        }

        return $out;
    }


    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html($this->plugin::DISPLAY_NAME); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields($this->plugin::SETTINGS_OPTIONS_GROUP); // renders hidden input fields and handles the secrurity aspects (nonce etc.)
                do_settings_sections($this->plugin::SETTINGS_PAGE_SLUG); // renders all the defined inputs fields
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function render_settings_section(): void
    {
        // echo '<p>Configure Media License</p>';
    }

    public function render_overwrite_section()
    {
    ?>
        <p>
            The central block option can be overwritten for individual blocks.
        </p>

        <p>
            Use the <code>media_license_individual_block_settings</code> filter to do this.
        </p>

        <p><strong>Example:</strong></p>

        <pre><code>
        add_filter(
            'media_license_individual_block_settings',
            fn( $blocks ) => $blocks + [ 'core/cover' => 'collect' ],
            10,
            2
        );
        </code></pre>

        <p><strong>Currently active settings:</strong></p>
        <?php
        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME);
        $central_block_setting = $settings[$this->plugin::SETTINGS_FIELD_BLOCKS_MAIN] ?? 'legacy';
        $individual_block_settings = apply_filters('media_license_individual_block_settings', [], $central_block_setting);

        if (empty($individual_block_settings)) :
        ?>
            <p><em>No individual block overrides are active.</em></p>
        <?php
        else :
        ?>
            <ul>
                <?php foreach ($individual_block_settings as $block_name => $setting) : ?>
                    <li>
                        <code><?php echo esc_html($block_name); ?></code>
                        → <strong><?php echo esc_html($setting); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php
        endif;
    }

    public function render_checkbox($args): void
    {
        $key = $args['key'];

        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME, []);
        $enabled  = ! empty($settings[$key]);
        ?>
        <input
            type="checkbox"
            name="<?php echo esc_attr($this->plugin::SETTINGS_OPTION_NAME); ?>[<?php echo esc_attr($key); ?>]"
            value="1"
            <?php checked(true, $enabled); ?> />
    <?php
    }

    public function render_main_block_options(): void
    {
        $key = $this->plugin::SETTINGS_FIELD_BLOCKS_MAIN;
        $settings = get_option($this->plugin::SETTINGS_OPTION_NAME);
        $current_value = $settings[$key] ?? 'legacy';
    ?>
        <select
            name="<?php echo esc_attr($this->plugin::SETTINGS_OPTION_NAME); ?>[<?php echo esc_attr($key); ?>]">
            <option value='legacy' <?php selected('legacy', $current_value) ?>>Legacy</option>
            <option value='data-attribute' <?php selected('data-attribute', $current_value) ?>>Data-attribute</option>
            <option value='collect' <?php selected('collect', $current_value) ?>>Data-attribute + Collect</option>
        </select>
<?php
    }
}
