<?php
/**
 * Class BulkTaggerServiceTest
 *
 * @package
 */

use \Taghound_Media_Tagger\Bulk_Tagger_Service;

class BulkTaggerServiceTest extends WP_UnitTestCase {
	function setUp() {
		// NOTE: Attachment factories with uploads weren't introduced until 4.5

		// Create a demo image attachment
		if ( is_object( $this->factory ) ) {
			$post_id = $this->factory->attachment->create_upload_object( dirname( __FILE__ ) .  '/assets/test-image.jpeg' );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'post_status' => 'publish',
			);
			$post_id = wp_insert_post( $post_data );
		}
		$this->attachment_image = get_post( $post_id );

		parent::setUp();
	}

	function test_untagged_images_count() {
		$this->assertEquals(1, Bulk_Tagger_Service::untagged_images_count());
	}
}
