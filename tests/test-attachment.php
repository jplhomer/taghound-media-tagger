<?php
/**
 * Class AttachmentTest
 *
 * @package
 */

use Taghound_Media_Tagger\Tagger_Service;

/**
 * Test Attachments
 */
class AttachmentTest extends WP_UnitTestCase {
	private $api = null;
	private $tagger = null;
	private $attachment_image = null;
	private $attachment_pdf = null;
	private $tag_data = array();

	function setUp() {
		// NOTE: Attachment factories with uploads weren't introduced until 4.5
		// Create a demo image attachment
		if ( is_object( $this->factory ) ) {
			$post_id = $this->factory->attachment->create_upload_object( dirname( __FILE__ ) . '/assets/test-image.jpeg' );
		} else {
			$post_id = Attachment_Helper::create_image_attachment( dirname( __FILE__ ) . '/assets/test-image.jpeg' );
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
		$this->api = $this->getMockBuilder( '\Taghound_Media_Tagger\Clarifai\API\Client' )
						  ->setConstructorArgs( array( array( 'client_id' => 'nota', 'client_secret' => 'secret' ) ) )
						  ->setMethods( array( 'get_tags_for_image' ) )
						  ->getMock();

		$this->tagger = new Tagger_Service( $this->api );

		// Mock tag data
		$this->tag_data = array(
			'docid' => 1234,
			'local_id' => $this->attachment_image->ID,
			'result' => array(
				'tag' => array(
					'classes' => array('apple', 'banana', 'pear'),
				),
			),
		);
	}

	/**
	 * Make sure meta data is being stored with each image
	 */
	function test_tag_data_stored_with_image() {
		$this->api->expects( $this->any() )
				  ->method( 'get_tags_for_image' )
				  ->will( $this->returnValue( $this->tag_data ) );

		// Get mock tags for the image
	    $this->tagger->tag_single_image( tmt_get_image_path_or_url($this->attachment_image->ID), $this->attachment_image->ID );

		$this->assertEquals( $this->tag_data, get_post_meta( $this->attachment_image->ID, TMT_POST_META_KEY, true ) );
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
