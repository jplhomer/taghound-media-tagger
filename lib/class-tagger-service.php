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
	 * Tag a single uploaded image
	 *
	 * @param  string $image_path_or_url Image path or ID
	 * @param  int    $post_id              WP Post ID
	 *
	 * @return array                     Tags added to the image
	 */
	public function tag_single_image( $image_path_or_url, $post_id ) {
		$tags = $this->api->get_tags_for_image( $image_path_or_url, $post_id );

		if ( ! $tags ) {
			return $post_id;
		}

		return $this->store_tag_info( $tags );
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

		wp_set_object_terms( $post_id, $tags, tmt_get_tag_taxonomy() );

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
		update_post_meta( $post_id, TMT_POST_META_KEY, $resultset );
	}
}
