<?php

class Attachment_Helper extends WP_UnitTestCase {

	// Create a demo image attachment
	public static function create_image_attachment( $file = null ) {
		if ( ! is_null($file) ) {
			$post_id = static::create_actual_image_upload( $file );
		} else {
			$post_data = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'guid' => 'this-is-a-test.jpeg',
			);
			$post_id = wp_insert_post( $post_data );
		}

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

	/**
	 * Stubs the WP_UnitTest_Factory_For_Attachment available in 4.5
	 *
	 * @param  string $file File path
	 *
	 * @return int          WP Post ID
	 */
	protected static function create_actual_image_upload( $file, $parent = 0 ) {
		$contents = file_get_contents($file);
		$upload = wp_upload_bits(basename($file), null, $contents);

		$type = '';
		if ( ! empty($upload['type']) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ($mime)
				$type = $mime['type'];
		}

		$attachment = array(
			'post_title' => basename( $upload['file'] ),
			'post_content' => '',
			'post_type' => 'attachment',
			'post_parent' => $parent,
			'post_mime_type' => $type,
			'guid' => $upload[ 'url' ],
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload[ 'file' ], $parent );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}
}
