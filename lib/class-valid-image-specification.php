<?php

namespace Taghound_Media_Tagger;

use WP_Post;

/**
 * Specification for valid taggable images
 */
class Valid_Image_Specification {

	/**
	 * Valid MIME types for images
	 *
	 * @var array
	 */
	protected $valid_attachment_mime_types = array( 'image/jpeg', 'image/png', 'image/gif' );

	/**
	 * See if an attachment is an image
	 *
	 * @param  WP_Post $attachment The Attachment object
	 * @return boolean
	 */
	public function is_satisfied_by( WP_Post $attachment ) {
		return in_array( $attachment->post_mime_type, $this->valid_attachment_mime_types );
	}

	/**
	 * Scope the provided query args with valid image types
	 *
	 * @param  array $query_args  Post query args
	 * @return array              Query args with scope
	 */
	public function as_scope( $query_args = array() ) {
		$query_args['post_mime_type'] = $this->valid_attachment_mime_types;

		return $query_args;
	}
}
