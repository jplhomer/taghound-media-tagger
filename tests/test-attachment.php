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
						  ->setConstructorArgs( ['my_api_key'] )
						  ->setMethods( array( 'get_tags_for_images' ) )
						  ->getMock();

		$this->tagger = new Tagger_Service( $this->api );

		// Mock tag data
		$this->tag_data = (object) array(
			'outputs' => array(
				(object) array(
					'input' => (object) array(
						'id' => $this->attachment_image->ID
					),
					'status' => (object) array(
						'code' => 10000,
						'description' => 'Ok',
					),
					'data' => (object) array(
						'concepts' => array(
							(object) array( 'name' => 'apple' ),
							(object) array( 'name' => 'banana' ),
							(object) array( 'name' => 'pear' ),
						)
					)
				)
			)
		);
	}

	/**
	 * Make sure meta data is being stored with each image
	 */
	function test_tag_data_stored_with_image() {
		$this->api->expects( $this->any() )
				  ->method( 'get_tags_for_images' )
				  ->will( $this->returnValue( $this->tag_data ) );

		// Get mock tags for the image
	  $this->tagger->tag_images( array($this->attachment_image) );

		$this->assertEquals( $this->tag_data->outputs[0], get_post_meta( $this->attachment_image->ID, TMT_POST_META_KEY, true ) );
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
