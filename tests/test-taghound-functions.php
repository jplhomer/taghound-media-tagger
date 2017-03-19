<?php

class TaghoundFunctionsTest extends WP_UnitTestCase {
	/**
	 * Make sure our upload only utility function works
	 */
	function test_upload_only_function_works() {
		$option_name = TMT_SETTING_PREFIX . 'upload_only';

		update_option( $option_name, '' );
		$this->assertFalse( tmt_is_upload_only() );

		update_option( $option_name, 'on' );
		$this->assertTrue( tmt_is_upload_only() );
	}

	function test_image_path_or_url_function_works() {
		$option_name = TMT_SETTING_PREFIX . 'upload_only';
		$post_id = Attachment_Helper::create_image_attachment( dirname( __FILE__ ) . '/assets/test-image.jpeg' );

		update_option( $option_name, '' );
		$result = tmt_get_image_path_or_url($post_id);

		$this->assertTrue(!!stristr($result, 'http'), "{$result} should be a public URL");

		update_option( $option_name, 'on' );
		$result = tmt_get_image_path_or_url($post_id);

		$this->assertFalse(!!stristr($result, 'http'), "{$result} should not be a public URL");
	}
}
