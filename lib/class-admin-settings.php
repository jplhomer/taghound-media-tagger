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
					array(
						'name'     => 'upload_only',
						'title'    => 'My website is not publicly accessible',
						'help'     => 'If checked, images will be uploaded for analysis instead of downloaded.',
						'type'     => 'checkbox',
						'disabled' => !tmt_can_be_enabled(),
					),
				),
			),
			'actions' => array(
				'name' => 'actions',
				'title' => '',
				'fields' => array(),
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
	 * Prints a checkbox input
	 * @param  array $setting  Settings
	 * @return void
	 */
	public function print_checkbox_input( $setting ) {
		$checked = ( get_option( $this->prefix . $setting['name'] ) ) ? 'checked' : '';
		$disabled = $setting['disabled'] ? 'disabled' : '';

		echo '<input type="checkbox" name="' . $this->prefix . $setting['name'] . '" ' . $checked . ' ' . $disabled . ' />';

		if ( !empty($setting['help']) ) {
			echo '<small><em>' . $setting['help'] . '</em></small>';
		}
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

	public function print_usage_data() {
		$taghound = Taghound_Media_Tagger::instance();
		$cf = $taghound->get_cf_client();

		$usage = $cf->get_usage_data();

		echo "<h3>Taghound - Clarifai API Usage</h3>";

		if ( ! is_a( $usage, 'Taghound_Media_Tagger\Clarifai\API\Usage' ) ) {
			echo "We had trouble loading your Clarifai API usage. Please try again later.";
			return;
		}

		$hourly = $usage->hourly;
		$monthly = $usage->monthly;

		echo "<p><strong>Hourly</strong></p>";
		echo "<progress class='tmt-progress' value='{$hourly['consumed']}' max='{$hourly['limit']}'></progress>";
		echo "<p>{$hourly['consumed']} of {$hourly['limit']} units used";
		echo "<hr>";
		echo "<p><strong>Monthly</strong></p>";
		echo "<progress class='tmt-progress' value='{$monthly['consumed']}' max='{$monthly['limit']}'></progress>";
		echo "<p>{$monthly['consumed']} of {$monthly['limit']} units used";
	}

	/**
	 * Print instructions for getting an API key from Clarifai.
	 * @return void
	 */
	public function section_content_main() {
		echo '<p>Enter your Clarifai Client ID and Client Secret. Get them by <a href="http://developer.clarifai.com" target="_blank">creating a free Clarifai account here &raquo;</a>';
	}

	public function section_content_actions() {
		if ( tmt_is_enabled() ) {
			$this->print_usage_data();
		}
	}
}

Settings::instance();
