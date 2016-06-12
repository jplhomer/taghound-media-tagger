<?php

namespace Taghound_Media_Tagger;

class Settings {
	protected static $_instance;

	/**
	 * The page where our options are being printed
	 * @var string
	 */
	protected $page = 'media';

	/**
	 * The prefix to use when creating setting names
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Holds our default settings
	 * @var array
	 */
	protected $settings = array();

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->prefix = TMT_SETTING_PREFIX;
		$this->settings = array(
			'main' => array(
				'name' => 'iat',
				'title' => 'Taghound Media Tagger Settings',
				'fields' => array(
					array(
						'name'  => 'client_id',
						'title' => 'Clarifai Client ID',
						'type'  => 'text',
					),
					array(
						'name'  => 'client_secret',
						'title' => 'Clarifai Client Secret',
						'type'  => 'text',
					),
					array(
						'name'    => 'enabled',
						'title'   => 'Enable for all new Images',
						'type'    => 'checkbox',
						'disabled' => !tmt_can_be_enabled(),
					),
				),
			),
		);

		add_action( 'admin_init', array( $this, 'init_settings_sections' ) );
	}

	/**
	 * Initialize settings sections
	 * @return void
	 */
	public function init_settings_sections() {
		foreach ($this->settings as $key => $section) {
            if( method_exists( 'Taghound_Media_Tagger\Settings', "section_content_$key" ) ) {
                $section_callback = array( $this, "section_content_$key" );
            } else {
                $section_callback = array();
            }

			add_settings_section(
				"{$this->prefix}_{$section['name']}",
				$section['title'],
				$section_callback,
				$this->page
			);

			foreach ($section['fields'] as $setting) {
				add_settings_field(
					"{$this->prefix}_{$setting['name']}",
					$setting['title'],
					array( $this, "print_{$setting['type']}_input" ),
					$this->page,
					"{$this->prefix}_{$section['name']}",
					$setting
				);

				register_setting(
					$this->page,
					$this->prefix . $setting['name']
				);
			}
		}
	}

	/**
	 * Get content for the page
	 * @return void
	 */
	public function get_options_page() {
		?>
		<div class="wrap">
			<h2>Taghound Media Tagger Settings</h2>
			<form method="post" action="options.php">
				<?php settings_fields( $this->page ); ?>
				<?php do_settings_sections( $this->page ); ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Prints a checkbox input
	 * @param  array $setting  Settings
	 * @return void
	 */
	public function print_checkbox_input( $setting ) {
		$checked = ( get_option( $this->prefix . $setting['name'] ) ) ? 'checked' : '';
		$disabled = $setting['disabled'] ? 'disabled' : '';

		echo '<input type="checkbox" value="' . $this->prefix . $setting['name'] . '" name="' . $this->prefix . $setting['name'] . '" ' . $checked . ' ' . $disabled . ' />';
	}

	/**
	 * Print a basic select input
	 * @param  array $setting  Settings
	 * @return void
	 */
	public function print_select_input( $setting ) {
		$value = esc_attr( get_option( $this->prefix . $setting['name'], '' ) );
		$options = call_user_func( $setting['options'] );

		echo '<select name="' . $this->prefix . $setting['name'] . '" id="' . $this->prefix . $setting['name'] . '">';
		foreach ($options as $val => $name) {
			echo '<option value="' . $val . '" ' . selected( $value, $val, false ) . '>' . $name . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Print out a text field input
	 * @param  array $setting  Original settings
	 * @return void
	 */
	public function print_text_input( $setting ) {
		$value = esc_attr( get_option( $this->prefix . $setting['name'], '' ) );

		echo '<input name="' . $this->prefix . $setting['name'] . '" id="' . $this->prefix . $setting['name'] . '" value="' . $value . '" />';
	}

	/**
	 * Print instructions for getting an API key from Clarifai.
	 * @return void
	 */
	public function section_content_main() {
		echo '<p>Enter your Clarifai Client ID and Client Secret. Get them by <a href="http://developer.clarifai.com" target="_blank">creating a free Clarifai account here &raquo;</a>';
	}
}

Settings::instance();
