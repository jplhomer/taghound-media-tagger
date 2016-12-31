<?php
/**
 * Class TagTest
 *
 * @package
 */

/**
 * Test Tags
 */
class TagTest extends WP_UnitTestCase {
	private $api = null;
	private $TMT = null;
	private $attachment_image = null;
	private $tag_data = array();

	function setUp() {
		$this->TMT = Taghound_Media_Tagger\Taghound_Media_Tagger::instance();

		// NOTE: Attachment factories with uploads weren't introduced until 4.5
		// Create a demo image attachment
		if ( is_object( $this->factory ) ) {
			$post_id = $this->factory->attachment->create_upload_object( dirname( __FILE__ ) . '/assets/test-image.jpeg' );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image/jpeg',
			);
			$post_id = wp_insert_post( $post_data );
		}
		$this->attachment_image = get_post( $post_id );

		// Create a mock of the API
		$this->api = $this->getMockBuilder( '\Taghound_Media_Tagger\Clarifai\API\Client' )
						  ->setConstructorArgs( array( array( 'client_id' => 'nota', 'client_secret' => 'secret' ) ) )
						  ->setMethods( array( 'get_tags_for_image' ) )
						  ->getMock();

		// Mock tag data
	    $this->tag_data = array(
			'doc_id' => 1234,
			'tags' => array( 'apple', 'banana', 'pear' ),
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
	    $this->TMT->handle_add_attachment( $this->attachment_image->ID, $this->api );

		// Get the names of the terms associated with this attachment
		$terms = wp_get_object_terms( $this->attachment_image->ID, TMT_TAG_SLUG );

		$this->assertInternalType( 'array', $terms );

		$term_names = array_map( function( $term ) {
			return $term->name;
		}, $terms);

		$this->assertEquals( $this->tag_data['tags'], $term_names );
	}

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
}
