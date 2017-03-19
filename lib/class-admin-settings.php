<?php

namespace Taghound_Media_Tagger;

use \Taghound_Media_Tagger\Bulk_Tagger_Service;

/**
 * Handles admin settings page
 */
class Settings {
	/**
	 * Singleton container
	 *
	 * @var self
	 */
	protected static $_instance;

	/**
	 * The page where our options are being printed
	 *
	 * @var string
	 */
	protected $page = 'taghound-settings';

	/**
	 * The prefix to use when creating setting names
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Holds our default settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Instantiate Singleton
	 *
	 * @return instance of self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Construct singlton
	 */
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
						'disabled' => ! tmt_can_be_enabled(),
					),
					array(
						'name'     => 'upload_only',
						'title'    => 'My website is not publicly accessible',
						'help'     => 'If checked, images will be uploaded for analysis instead of downloaded.',
						'type'     => 'checkbox',
						'disabled' => ! tmt_can_be_enabled(),
					),
				),
			),
			'actions' => array(
				'name' => 'actions',
				'title' => '',
				'fields' => array(),
			),
		);

		add_action( 'admin_menu', array( $this, 'init_settings_sections' ) );
	}

	/**
	 * Initialize settings sections
	 *
	 * @return void
	 */
	public function init_settings_sections() {
		add_options_page(
			'Taghound Media Tagger',
			'Taghound',
			'manage_options',
			$this->page,
			array( $this, 'print_options_page' )
		);

		foreach ( $this->settings as $key => $section ) {
			if ( method_exists( 'Taghound_Media_Tagger\Settings', "section_content_$key" ) ) {
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

			foreach ( $section['fields'] as $setting ) {
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
	 *
	 * @param  array $setting  Settings.
	 * @return void
	 */
	public function print_checkbox_input( $setting ) {
		$checked = ( get_option( $this->prefix . $setting['name'] ) ) ? 'checked' : '';
		$disabled = $setting['disabled'] ? 'disabled' : '';

		echo '<input type="checkbox" name="' . $this->prefix . $setting['name'] . '" ' . $checked . ' ' . $disabled . ' />';

		if ( ! empty( $setting['help'] ) ) {
			echo '<small><em>' . $setting['help'] . '</em></small>';
		}
	}

	/**
	 * Print a basic select input
	 *
	 * @param  array $setting  Settings
	 * @return void
	 */
	public function print_select_input( $setting ) {
		$value = esc_attr( get_option( $this->prefix . $setting['name'], '' ) );
		$options = call_user_func( $setting['options'] );

		echo '<select name="' . $this->prefix . $setting['name'] . '" id="' . $this->prefix . $setting['name'] . '">';
		foreach ( $options as $val => $name ) {
			echo '<option value="' . $val . '" ' . selected( $value, $val, false ) . '>' . $name . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Print out a text field input
	 *
	 * @param  array $setting  Original settings
	 * @return void
	 */
	public function print_text_input( $setting ) {
		$value = esc_attr( get_option( $this->prefix . $setting['name'], '' ) );

		echo '<input name="' . $this->prefix . $setting['name'] . '" id="' . $this->prefix . $setting['name'] . '" value="' . $value . '" />';
	}

	/**
	 * Prints the Options page content
	 *
	 * @return void
	 */
	public function print_options_page() {
		echo '<form method="POST" action="options.php"><div class="wrap">';
		settings_fields( $this->page );
		do_settings_sections( $this->page );
		submit_button();
		echo '</div></form>';
	}

	/**
	 * Print usage data on admin page
	 *
	 * @return void
	 */
	public function print_usage_data() {
		$cf = tmt_get_cf_client();

		$usage = $cf->get_usage_data();

		echo '<h3>Taghound - Clarifai API Usage</h3>';

		if ( ! is_a( $usage, 'Taghound_Media_Tagger\Clarifai\API\Usage' ) ) {
			echo 'We had trouble loading your Clarifai API usage. Please try again later.';
			return;
		}

		$hourly = $usage->hourly;
		$monthly = $usage->monthly;

		echo '<p><strong>Hourly</strong></p>';
		echo "<progress class='tmt-progress' value='{$hourly['consumed']}' max='{$hourly['limit']}'></progress>";
		echo "<p>{$hourly['consumed']} of {$hourly['limit']} units used";
		echo '<hr>';
		echo '<p><strong>Monthly</strong></p>';
		echo "<progress class='tmt-progress' value='{$monthly['consumed']}' max='{$monthly['limit']}'></progress>";
		echo "<p>{$monthly['consumed']} of {$monthly['limit']} units used";
	}

	/**
	 * Print bulk tagger UI
	 *
	 * @return void
	 */
	public function print_bulk_tagger() {
		$untagged_images_count = Bulk_Tagger_Service::untagged_images_count();
		$disabled = ! Bulk_Tagger_Service::enabled();
		$disabled_attr = $disabled ? 'disabled' : '';

		echo '<h3>Taghound - Bulk Tagger</h3>';

		if ( $untagged_images_count > 0 ) {
			echo '<p>Use the bulk tagger to analyze any untagged images in your library. Note that this function may be limited by your Clarifai usage quota.<br>After starting the bulk tagger, <strong>do not navigate away from the page or close the browser window</strong> until it has completed.</p>';
			echo "<p>You have <strong data-starting-number>${untagged_images_count}</strong> untagged images.";
			echo "<p><button class='button' data-bulk-tag-init ${disabled_attr}>Tag Them Now</button>";

			if ( $disabled ) {
				echo ' <em>Bulk tagging is not available for websites that are not publicly accessible.</em>';
			}

			echo '</p>';
			echo '<div class="tmt-errors"></div><div class="tmt-status"></div>';
		} else {
			echo '<p>All of your images have tags!</p>';
		}
	}

	/**
	 * Print alternate taxonomy warning
	 *
	 * @return void
	 */
	public function print_alternate_taxonomy_notice() {
		if ( tmt_using_alternate_taxonomy() ) {
			echo '<p><strong style="color: red;">Note: Your theme is using an alternate taxonomy: <code>' . tmt_get_tag_taxonomy() . '</code></strong>.';
			echo "<br>Taghound's default taxonomy user interface and search has been disabled for media items.</p>";
		}
	}

	/**
	 * Print instructions for getting an API key from Clarifai.
	 *
	 * @return void
	 */
	public function section_content_main() {
		echo '<p>Enter your Clarifai Client ID and Client Secret. Get them by <a href="http://developer.clarifai.com" target="_blank">creating a free Clarifai account here &raquo;</a>';
	}

	/**
	 * Conditionally prints admin sectinos
	 *
	 * @return void
	 */
	public function section_content_actions() {
		$this->print_alternate_taxonomy_notice();
		if ( tmt_can_be_enabled() ) {
			$this->print_usage_data();
			$this->print_bulk_tagger();
		}
	}
}

Settings::instance();
