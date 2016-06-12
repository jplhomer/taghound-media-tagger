<?php
/**
 * Class AttachmentTest
 *
 * @package
 */

/**
 * Test Attachments
 */
class AttachmentTest extends WP_UnitTestCase {
	private $api = null;
	private $TMT = null;
	private $attachment_image = null;
	private $attachment_pdf = null;
	private $tag_data = array();

	function setUp() {
		$this->TMT = Taghound_Media_Tagger\Taghound_Media_Tagger::instance();

		// NOTE: Attachment factories with uploads weren't introduced until 4.5

		// Create a demo image attachment
		if ( is_object( $this->factory ) ) {
			$post_id = $this->factory->attachment->create_upload_object( dirname( __FILE__ ) .  '/assets/test-image.jpeg' );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image/jpeg',
			);
			$post_id = wp_insert_post( $post_data );
		}
		$this->attachment_image = get_post( $post_id );

		// Create a demo PDF attachment
		if ( is_object( $this->factory ) ) {
			$post_id = $this->factory->attachment->create_upload_object( dirname( __FILE__ ) . '/assets/test-pdf.pdf' );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'application/pdf',
			);
			$post_id = wp_insert_post( $post_data );
		}
		$this->attachment_pdf = get_post( $post_id );

		// Create a mock of the API
		$this->api = $this->getMockBuilder( '\Taghound_Media_Tagger\Clarifai_API' )
						  ->setConstructorArgs( array( array( 'client_id' => 'nota', 'client_secret' => 'secret' ) ) )
						  ->setMethods( array('get_tags_for_image') )
						  ->getMock();

        // Mock tag data
	    $this->tag_data = array(
			'doc_id' => 1234,
			'tags' => array( 'apple', 'banana', 'pear' ),
		);
	}

	/**
	 * Make sure Image attachments are marked as valid
	 */
	function test_image_attachments_are_valid() {
		// Test that a JPEG is valid
		$this->assertTrue( $this->TMT->validate_attachment_for_tagging( $this->attachment_image->ID ) );

		// Test that a PDF is invalid
		$this->assertFalse( $this->TMT->validate_attachment_for_tagging( $this->attachment_pdf->ID ) );
	}

	/**
	 * Make sure meta data is being stored with each image
	 */
	function test_tag_data_stored_with_image() {
		$this->api->expects( $this->any() )
				  ->method( 'get_tags_for_image' )
				  ->will( $this->returnValue( $this->tag_data ) );

		// Get mock tags for the image
	    $this->TMT->handle_add_attachment( $this->attachment_image->ID, $this->api );

		$this->assertEquals( $this->tag_data, get_post_meta( $this->attachment_image->ID, TMT_POST_META_KEY, true ) );
	}
}
