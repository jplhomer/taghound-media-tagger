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

	/**
	 * Delete all attachments. Use for tearing down.
	 *
	 * @return void
	 */
	public static function delete_all_attachments() {
		$post_ids = get_posts( array(
			'post_type' => 'attachment',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
		));

		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id );
		}
	}
}
