<?php

class Attachment_Helper extends WP_UnitTestCase {

	// Create a demo image attachment
	public static function create_image_attachment() {
		$post_data = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image/jpeg',
		);
		$post_id = wp_insert_post( $post_data );

		return $post_id;
	}

	// Create a demo PDF attachment
	public static function create_non_image_attachment() {
		$post_data = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'application/pdf',
		);
		$post_id = wp_insert_post( $post_data );

		return $post_id;
	}
}
