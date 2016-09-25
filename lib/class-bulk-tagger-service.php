<?php

namespace Taghound_Media_Tagger;

use Taghound_Media_Tagger\Clarifai\API\Client;
use Taghound_Media_Tagger\Tagger_Service;

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
		$result = array(
			'error' => false,
			'continue' => false,
			'tagged' => 0,
			'failed' => 0,
		);

		// See what our max batch size is
		$info = $this->api->get_info();
		$max_batch_size = $info['max_batch_size'];

		// Get that many images from repository
		$images = $this->untagged_images( array('posts_per_page' => $max_batch_size) );
		$image_urls = array();
		foreach ($images as $image) {
			$image_urls[ $image->ID ] = $image->guid;
		}

		$results = $this->api->get_tags_for_images( $image_urls );

		if ( $results['status_code'] === 'OK' ) {
			$tags = $this->process_tag_results( $results['results'] );
			$result['tagged'] += count($tags);
			$result['failed'] += count($results['results']) - count($tags);
			$result['continue'] = false; // TODO: Determine if we should continue
		} else {
			// Something bad happened.
			$result['error'] = true;
			$result['error_message'] = $results['status_msg'];
			$result['results'] = $results;
		}

		return $result;
	}

	/**
	 * Process the tags along with the images
	 * @param  Array $results  API results
	 * @return Array 		   Resulting messages
	 */
	public function process_tag_results( $results ) {
		$tags = array();

		foreach ( $results as $result ) {
			if ( $result['status_code'] == 'OK' ) {
				$tagger = new Tagger_Service( $this->api );
				$tags[] = $tagger->store_tag_info( $result );
			}
		}

		return $tags;
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
			'posts_per_page' => -1,
			'post_type' => 'attachment',
			'meta_query' => array(
				array(
					'key' => TMT_POST_META_KEY,
					'value' => 'bug #23268',
					'compare' => 'NOT EXISTS',
				),
			),
		));

		$valid_images = new Valid_Image_Specification;
		$args = $valid_images->as_scope( $args );

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
