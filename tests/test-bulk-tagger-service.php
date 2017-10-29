<?php
/**
 * Class BulkTaggerServiceTest
 *
 * @package
 */

use Taghound_Media_Tagger\Bulk_Tagger_Service;

class BulkTaggerServiceTest extends WP_UnitTestCase {
	protected $api = null;

	protected $response_info = array();

	protected $response_tags = array();

	protected $num_images = 50;

	protected $num_non_images = 2;

	protected $num_bad_images = 2;

	protected $failed_post_ids = array();

	function setUp() {
		parent::setUp();

		for ( $i = 0; $i < $this->num_images; $i++ ) {
			$post_ids[] = Attachment_Helper::create_image_attachment();
		}

		for ( $i = 0; $i < $this->num_non_images; $i++ ) {
			Attachment_Helper::create_non_image_attachment();
		}

		// Create a mock of the API
		$this->api = $this->getMockBuilder( '\Taghound_Media_Tagger\Clarifai\API\Client' )
						  ->setConstructorArgs( ['my_api_key'] )
						  ->setMethods( array( 'get_tags_for_images' ) )
							->getMock();

		$output = (object) array(
			'input' => (object) array(
				'id' => '',
				'url' => 'http://foo.bar/image.jpg',
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
		);

		$this->response = (object) array(
			'status' => (object) array(
				'code' => 10000,
				'description' => 'Ok',
			),
			'outputs' => array()
		);

		for ( $i = 0; $i < 25; $i++ ) {
			$set = clone $output;
			$set->input = (clone $set->input);
			$set->input->id = (string) $post_ids[$i];

			if ( $i < $this->num_bad_images ) {
				$set->status = (clone $set->status);
				$set->status->description = 'This image was weird or something.';
			}

			$this->response->outputs[$i] = $set;
		}
	}

	function test_untagged_images() {
		$images = Bulk_Tagger_Service::untagged_images( array( 'posts_per_page' => 25 ) );

		$this->assertCount( 25, $images, 'Posts per page argument should be respected' );
	}

	function test_untagged_images_count() {
		$this->assertEquals( $this->num_images, Bulk_Tagger_Service::untagged_images_count() );
	}

	function test_bulk_tagging() {
	    $this->api->expects( $this->any() )
				  ->method( 'get_tags_for_images' )
				  ->will( $this->returnValue( $this->response ) );

		$bulk_tagger = new Bulk_Tagger_Service( $this->api );

		$result = $bulk_tagger->init();

		$this->assertEquals( 25 - $this->num_bad_images, $result['tagged'] );
		$this->assertEquals( $this->num_bad_images, count( $result['failed'] ), 'Images with errors should be collected' );

		// Test tags with not OK statuses are marked as failed and reasons given
		$this->failed_post_ids = array_map(function( $failed ) {
			return $failed['post_id'];
		}, $result['failed']);
		$first_failed = array_shift( $result['failed'] );
		$this->assertInternalType( 'string', $first_failed['filename'] );
		$this->assertContains( 'jpg', $first_failed['filename'] );
		$this->assertEquals( 'This image was weird or something.', $first_failed['status_msg'] );
		$this->assertTrue( isset( $first_failed['post_id'] ) );

		$first_tagged_result = $this->response->outputs[ 0 + $this->num_bad_images ];
		$this->assertEquals(
			$first_tagged_result,
			get_post_meta( $first_tagged_result->input->id, TMT_POST_META_KEY, true ),
			'Tag data should be persisted on the Post object'
		);

		// Make sure count is updated
		$this->assertEquals(
			$this->num_images - 25 + $this->num_bad_images,
			Bulk_Tagger_Service::untagged_images_count(),
		 	'Remaining untagged images should be less the max batch size'
		);
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
