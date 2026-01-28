<?php

namespace Palasthotel\MediaLicense;

class AdminPage
{

    public Plugin $plugin;
    // this is a list of all the settings fields for all the gutenberg blocks
    private array $individual_block_settings_field_list;

    // todo: better names for better UX (how the checkboxes look)

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
            $this->plugin::SETTINGS_FIELD_ENABLE_BLOCK_DATA_ATTRIBUTES,
            'Enable data attributes for gutenberg blocks',
            [$this, 'render_checkbox'],
            $this->plugin::SETTINGS_PAGE_SLUG,
            $this->plugin::SETTINGS_SECTION_MAIN,
            ['key' => $this->plugin::SETTINGS_FIELD_ENABLE_BLOCK_DATA_ATTRIBUTES]
        );

        $block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        // array keys are names like: core/paragraph, core/image, myplugin/foo
        $block_names = array_keys( $block_types );

        foreach ($block_names as $block) {

            // $block looks like: core/accordion-panel
            // todo: are / and - problems here since we use this in the db as in id?
            // todo: write helper function to generate the fieldname so I can reuse it
            $field_name = $this->plugin::SETTINGS_FIELD_EXCLUDE_BLOCK_DATA_ATTRIBUTES_PREFIX . $block;
            $this->individual_block_settings_field_list[] = $field_name;
            
            add_settings_field(
                $field_name,
                $block,
                [$this, 'render_checkbox'],
                $this->plugin::SETTINGS_PAGE_SLUG,
                $this->plugin::SETTINGS_SECTION_MAIN,
                ['key' => $field_name],
            );
        }

    }

    public function sanitize_settings($input): array
    {
        $out = [];

        $out[$this->plugin::SETTINGS_FIELD_ENABLE_BLOCK_DATA_ATTRIBUTES] =
            ! empty($input[$this->plugin::SETTINGS_FIELD_ENABLE_BLOCK_DATA_ATTRIBUTES]) ? 1 : 0;

        foreach ($this->individual_block_settings_field_list as $field) {
            $out[$field] =
                ! empty($input[$field]) ? 1 : 0;
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

    // todo: add other label here
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
                <?php checked(true, $enabled); ?>
            />
<?php
    }
}
