<?php
/**
 * Class TaggerServiceTest
 *
 * @package
 */

use Taghound_Media_Tagger\Tagger_Service;

/**
 * Test the Tagger Service
 */
class TaggerServiceTest extends WP_UnitTestCase {
	private $api = null;
	private $tagger = null;
	private $attachment_image = null;
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

		// Create a mock of the API
		$this->api = $this->getMockBuilder( '\Taghound_Media_Tagger\Clarifai\API\Client' )
						  ->setConstructorArgs( array( array( 'client_id' => 'nota', 'client_secret' => 'secret' ) ) )
						  ->setMethods( array( 'get_tags_for_image' ) )
						  ->getMock();

		$this->tagger = new Tagger_Service( $this->api );

		// Mock tag data
	    $this->tag_data = array(
			'docid' => 1234,
			'local_id' => $post_id,
			'result' => array(
				'tag' => array(
					'classes' => array('apple', 'banana', 'pear'),
				),
			),
		);
	}

	/**
	 * Make sure Tags are added to an attachment
	 */
	function test_tags_are_added_to_attachment() {
		$this->api->expects( $this->any() )
				  ->method( 'get_tags_for_image' )
				  ->will( $this->returnValue( $this->tag_data ) );

		// Get mock tags for the image
		$this->tagger->tag_single_image(
			tmt_get_image_path_or_url( $this->attachment_image->ID ),
			$this->attachment_image->ID
		);

		// Get the names of the terms associated with this attachment
		$terms = wp_get_object_terms( $this->attachment_image->ID, TMT_TAG_SLUG );

		$this->assertInternalType( 'array', $terms );

		$term_names = array_map( function( $term ) {
			return $term->name;
		}, $terms);

		$this->assertEquals( $this->tag_data['result']['tag']['classes'], $term_names );
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
