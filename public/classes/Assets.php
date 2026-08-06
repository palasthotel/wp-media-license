<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 31.05.17
 * Time: 16:59
 */

namespace Palasthotel\MediaLicense;


/**
 * @property Plugin plugin
 */
class Assets {

	/**
	 * API constructor.
	 *
	 * @param Plugin $plugin
	 */
    public Plugin $plugin;

    function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('init', array($this, 'register'), 1);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_script'));
	}

	function register(){
		wp_register_script(
			Plugin::HANDLE_API_JS,
			$this->plugin->getUrl("/js/api.js"),
			['jquery'],
			filemtime( $this->plugin->getPath( "/js/api.js")),
			true
		);
		$obj =  array(
			"resturl" => $this->plugin->rest->getCaptionsUrl(),
			"autoload" => apply_filters(Plugin::FILTER_AUTOLOAD_ASYNC_IMAGE_LICENSE, true),
		);
		wp_localize_script(Plugin::HANDLE_API_JS, "MediaLicense_API", $obj);

		wp_register_script(
			Plugin::HANDLE_ADMIN_TEXTAREA_JS,
			$this->plugin->getUrl('/js/admin-textarea-toolbar.js'),
			[],
			filemtime($this->plugin->getPath('/js/admin-textarea-toolbar.js')),
			true
		);
	}

	function enqueue_script(){
		wp_enqueue_script(Plugin::HANDLE_API_JS);

        $enable_frontend_styling = apply_filters(Plugin::FILTER_ENABLE_FRONTEND_STYLES, true);

        if ($enable_frontend_styling) {
            wp_enqueue_style(
                Plugin::HANDLE_FRONTEND_CSS,
                $this->plugin->getUrl('/styles/frontend.css'),
                [],
                filemtime($this->plugin->getPath('/styles/frontend.css'))
			);
		}
	}

	function enqueue_admin_script($hook_suffix){
		if (!in_array($hook_suffix, ['upload.php', 'post.php', 'post-new.php', 'media-upload.php', 'media_page_media-license-settings'], true)) {
			return;
		}

		wp_enqueue_script(Plugin::HANDLE_ADMIN_TEXTAREA_JS);
	}
}
