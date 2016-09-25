<?php

use Taghound_Media_Tagger\Valid_Image_Specification;

class ValidImageSpecificationTest extends WP_UnitTestCase {
	public function test_image_scope_is_set() {
		$args = array(
			'post_type' => 'attachment',
		);

		$valid_images = new Valid_Image_Specification;
		$args = $valid_images->as_scope($args);

		$this->assertArrayHasKey('post_mime_type', $args);
		$this->assertInternalType('array', $args['post_mime_type']);
	}
}
