<?php

namespace Taghound_Media_Tagger;

use Taghound_Media_Tagger\Clarifai\API\Client;

/**
 * Tagger service for getting and storing tag information for a media item.
 */
class Tagger_Service {
	/**
	 * API
	 *
	 * @var Client
	 */
	protected $api = null;

	/**
	 * Construct the tagger service
	 *
	 * @param Client $api [description]
	 */
	public function __construct( Client $api ) {
		$this->api = $api;
	}

	/**
	 * Persist Clarifai tag data
	 *
	 * @param  array $resultset 	Clarifai Tag result set
	 * @return array      			Tags
	 */
	public function store_tag_info( $resultset ) {
		$post_id = (int) $resultset['local_id'];
		$tags = $resultset['result']['tag']['classes'];

		// Store the terms as tags
		// TODO: Delegate to a TagStorage interface.
		wp_set_object_terms( $post_id, $tags, TMT_TAG_SLUG );

		$this->preserve_tag_resultset( $post_id, $resultset );

		return $tags;
	}

	/**
	 * Preserve a tag resultset to database
	 *
	 * @param int   $post_id   Post ID
	 * @param array $resultset Resultset
	 */
	public function preserve_tag_resultset( $post_id, $resultset ) {
		// Store tag data along with the image.
		update_post_meta( $post_id, TMT_POST_META_KEY, $resultset );
	}
}
