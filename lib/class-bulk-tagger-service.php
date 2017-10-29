<?php

namespace Taghound_Media_Tagger;

use Taghound_Media_Tagger\Clarifai\API\Client;
use Taghound_Media_Tagger\Tagger_Service;

/**
 * Bulk Tagger Service class
 */
class Bulk_Tagger_Service {

	/**
	 * The Clarifai API
	 *
	 * @var Client
	 */
	protected $api = null;

	/**
	 * Errors encountered with images
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * This is hardcoded as of Clarifai V2
	 *
	 * @var int
	 */
	protected $max_batch_size = 128;

	/**
	 * Construct the bulk service class
	 *
	 * @param Client $api Clarifai api client
	 */
	public function __construct( Client $api ) {
		$this->api = $api;
	}

	/**
	 * Start a new bulk tagging session
	 *
	 * @param  array $args Arguments
	 *
	 * @return array       Results
	 */
	public function init( $args = array() ) {
		$result = wp_parse_args($args, array(
			'error' => false,
			'continue' => false,
			'tagged' => 0,
			'failed' => array(),
			'skip' => array(),
		));

		// Get that many images from repository.
		$images = $this->untagged_images( array( 'posts_per_page' => $this->max_batch_size, 'post__not_in' => $result['skip'] ) );
		$tagger = new Tagger_Service( $this->api );
		$results = $tagger->tag_images( $images );

		if ( 'Ok' === $results->status->description ) {
			$tags = $this->process_tag_results( $results->outputs );
			$result['tagged'] += count( $tags );
			$result['failed'] = $this->errors;

			$result['continue'] = ( count( $result['failed'] ) != $this->untagged_images_count() );
		} else {
			// Something bad happened.
			$result['error'] = true;
			$result['error_message'] = $results->status->description;
			$result['results'] = $results;
		}

		return $result;
	}

	/**
	 * Process the tags along with the images
	 *
	 * @param  Array $results  API results
	 * @return Array 		   Resulting messages
	 */
	public function process_tag_results( $results ) {
		$tags = array();

		foreach ( $results as $result ) {
			if ( 'Ok' != $result->status->description ) {
				$this->errors[] = array(
					'filename' => $result->input->url,
					'post_id' => $result->input->id,
					'status_code' => $result->status->code,
					'status_msg' => $result->status->description,
				);
			} else {
				$tags[] = $result;
			}
		}

		return $tags;
	}

	/**
	 * Get all untagged images
	 *
	 * @param  array $args Optional arguments
	 * @return array        WP Attachment objects
	 */
	public static function untagged_images( $args = array() ) {
		$args = wp_parse_args($args, array(
			'posts_per_page' => -1,
			'post_type' => 'attachment',
			'post_status' => 'any',
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
	 *
	 * @return int
	 */
	public static function untagged_images_count() {
		return count( self::untagged_images() );
	}
}
