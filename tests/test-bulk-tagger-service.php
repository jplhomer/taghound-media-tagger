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
						  ->setConstructorArgs( array( array( 'client_id' => 'nota', 'client_secret' => 'secret' ) ) )
						  ->setMethods( array( 'get_info', 'get_tags_for_images' ) )
						  ->getMock();

		// Stub Clarifai response info
	    $this->response_info = array(
			'max_image_size' => 100000,
			'default_language' => 'en',
			'max_video_size' => 100000,
			'max_image_bytes' => 10485760,
			'min_image_size' => 1,
			'default_model' => 'general-v1.3',
			'max_video_bytes' => 104857600,
			'max_video_duration' => 1800,
			'max_batch_size' => 25,
			'max_video_batch_size' => 1,
			'min_video_size' => 1,
			'api_version' => 0.1,
		);

		$resultset = array(
			'docid' => 15512461224882631443,
			'url' => 'https://samples.clarifai.com/metro-north.jpg',
			'status_code' => 'OK',
			'status_msg' => 'OK',
			'local_id' => '',
			'result' => array(
				'tag' => array(
					'concept_ids' => array( 'ai_HLmqFqBf', 'ai_fvlBqXZR', 'ai_Xxjc3MhT', 'ai_6kTjGfF6', 'ai_RRXLczch', 'ai_VRmbGVWh', 'ai_SHNDcmJ3', 'ai_jlb9q33b', 'ai_46lGZ4Gm', 'ai_tr0MBp64', 'ai_l4WckcJN', 'ai_2gkfMDsM', 'ai_CpFBRWzD', 'ai_786Zr311', 'ai_6lhccv44', 'ai_971KsJkn', 'ai_WBQfVV0p', 'ai_dSCKh8xv', 'ai_TZ3C79C6', 'ai_VSVscs9k' ),
					'classes' => array( 'train', 'railway', 'transportation system', 'station', 'train', 'travel', 'tube', 'commuter', 'railway', 'traffic', 'blur', 'platform', 'urban', 'no person', 'business', 'track', 'city', 'fast', 'road', 'terminal' ),
					'probs' => array( 0.9989112019538879, 0.9975532293319702, 0.9959157705307007, 0.9925730228424072, 0.9925559759140015, 0.9878921508789063, 0.9816359281539917, 0.9712483286857605, 0.9690325260162354, 0.9687051773071289, 0.9667078256607056, 0.9624242782592773, 0.960752010345459, 0.9586490392684937, 0.9572030305862427, 0.9494642019271851, 0.940894365310669, 0.9399334192276001, 0.9312160611152649, 0.9230834245681763 ),
				),
			),
			'docid_str' => '31fdb2316ff87fb5d747554ba5267313',
		);

		// Stub Clarifai response tags
		$this->response_tags = array(
			'status_code' => 'OK',
			'status_msg' => 'All images in request have completed successfully. ',
			'meta' => array(
				'tag' => array(
					'timestamp' => 1451945197.398036,
					'model' => 'general-v1.3',
					'config' => '34fb1111b4d5f67cf1b8665ebc603704',
				),
			),
			'results' => array(),
		);

		for ( $i = 0; $i < $this->response_info['max_batch_size']; $i++ ) {
			$set = $resultset;
			$set['local_id'] = "${post_ids[$i]}";

			if ( $i < $this->num_bad_images ) {
				$set['status_code'] = 'ERROR';
				$set['status_msg'] = 'This image was weird or something.';
			}

			$this->response_tags['results'][] = $set;
		}
	}

	function test_untagged_images() {
		$images = Bulk_Tagger_Service::untagged_images( array( 'posts_per_page' => $this->response_info['max_batch_size'] ) );

		$this->assertCount( $this->response_info['max_batch_size'], $images, 'Posts per page argument should be respected' );
	}

	function test_untagged_images_count() {
		$this->assertEquals( $this->num_images, Bulk_Tagger_Service::untagged_images_count() );
	}

	function test_bulk_tagging() {
		$this->api->expects( $this->any() )
				  ->method( 'get_info' )
				  ->will( $this->returnValue( $this->response_info ) );

	    $this->api->expects( $this->any() )
				  ->method( 'get_tags_for_images' )
				  ->will( $this->returnValue( $this->response_tags ) );

		$bulk_tagger = new Bulk_Tagger_Service( $this->api );

		$result = $bulk_tagger->init();

		$this->assertEquals( $this->response_info['max_batch_size'] - $this->num_bad_images, $result['tagged'] );
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

		$first_tagged_result = $this->response_tags['results'][ 0 + $this->num_bad_images ];
		$this->assertEquals(
			$first_tagged_result,
			get_post_meta( $first_tagged_result['local_id'], TMT_POST_META_KEY, true ),
			'Tag data should be persisted on the Post object'
		);

		// Make sure count is updated
		$this->assertEquals(
			$this->num_images - $this->response_info['max_batch_size'] + $this->num_bad_images,
			Bulk_Tagger_Service::untagged_images_count(),
		 	'Remaining untagged images should be less the max batch size'
		);
	}

	function test_rate_limit() {
		$response_info = $this->response_info;

		$response_info['max_batch_size'] = 0;

		$this->api->expects( $this->any() )
				  ->method( 'get_info' )
				  ->will( $this->returnValue( $response_info ) );

		$bulk_tagger = new Bulk_Tagger_Service( $this->api );
		$this->assertFalse( $bulk_tagger->init() );
	}

	function test_bulk_tagging_continuation() {
		// @TODO: Test images that failed are not re-tested in next batch
		// @TODO: Test continue param with more images than batch size
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
