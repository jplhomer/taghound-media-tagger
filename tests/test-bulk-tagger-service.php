<?php
/**
 * Class BulkTaggerServiceTest
 *
 * @package
 */

use \Taghound_Media_Tagger\Bulk_Tagger_Service;

class BulkTaggerServiceTest extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();

		// Create a demo image and PDF attachment
		// NOTE: Attachment factories with uploads weren't introduced until 4.5
		if ( is_object( $this->factory ) ) {
			$this->factory->attachment->create_upload_object( dirname( __FILE__ ) .  '/assets/test-image.jpeg' );
			$this->factory->attachment->create_upload_object( dirname( __FILE__ ) . '/assets/test-pdf.pdf' );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'post_status' => 'publish',
			);
			wp_insert_post( $post_data );

			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'application/pdf',
				'post_status' => 'publish',
			);
			wp_insert_post( $post_data );
		}
	}

	function test_untagged_images_count() {
		$this->assertEquals(1, Bulk_Tagger_Service::untagged_images_count());
	}
}
