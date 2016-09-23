<?php

namespace Taghound_Media_Tagger;

use \Taghound_Media_Tagger\Clarifai\API\Client;
use \Taghound_Media_Tagger\Tagger_Service;

class Bulk_Tagger_Service {

	/**
	 * The Clarifai API
	 * @var Client
	 */
	protected $api = null;

	public function __construct(Client $api) {
		$this->api = $api;
	}

	/**
	 * Start a new bulk tagging session
	 * @return array    Results
	 */
	public function init() {
		// See what our max batch size is
		$info = $this->api->get_info();
		$max_batch_size = $info['max_batch_size'];

		// Get that many images from repository
		$images = $this->untagged_images( array('posts_per_page' => $max_batch_size) );
		$image_urls = array_map(function($image) {
			return $image->guid;
		}, $images);

		$results = $this->api->get_tags_for_images( $image_urls );

		// Request tags
		return $results;
		// Process tags
		// Send back paginated response
	}

	/**
	 * Can bulk tagging happen?
	 * @return boolean
	 */
	public static function enabled() {
		return tmt_is_enabled() && !tmt_is_upload_only();
	}

	/**
	 * Get all untagged images
	 * @param  array  $args Optional arguments
	 * @return array        WP Attachment objects
	 */
	public static function untagged_images( $args = array() ) {
		$args = wp_parse_args($args, array(
			'post_type' => 'attachment',
			'meta_query' => array(
				array(
					'key' => TMT_POST_META_KEY,
					'value' => '',
					'compare' => 'NOT EXISTS',
				),
			),
		));

		$untagged_images = get_posts( $args );

		return $untagged_images;
	}

	/**
	 * Get the number of untagged images in the library
	 * @return int
	 */
	public static function untagged_images_count() {
		return count( self::untagged_images() );
	}
}
