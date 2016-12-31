<?php

use Taghound_Media_Tagger\Valid_Image_Specification;

class ValidImageSpecificationTest extends WP_UnitTestCase {
	public function test_image_scope_is_set() {
		$args = array(
			'post_type' => 'attachment',
		);

		$valid_images = new Valid_Image_Specification;
		$args = $valid_images->as_scope( $args );

		$this->assertArrayHasKey( 'post_mime_type', $args );
		$this->assertInternalType( 'array', $args['post_mime_type'] );
	}

	public function test_image_scope_works()
	{
		$post_id = Attachment_Helper::create_image_attachment();
		Attachment_Helper::create_non_image_attachment();

		$args = array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'orderby' => 'ID',
			'order' => 'DESC',
			'posts_per_page' => -1,
		);

		$args = (new Valid_Image_Specification)->as_scope( $args );
		$results = get_posts( $args );

		$first = array_shift( $results );
		$this->assertEquals( $post_id, $first->ID );
	}

	function tearDown() {
		Attachment_Helper::delete_all_attachments();
	}
}
