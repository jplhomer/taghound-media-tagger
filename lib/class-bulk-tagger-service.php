<?php

namespace Taghound_Media_Tagger;

use \Taghound_Media_Tagger\Client;

class Bulk_Tagger_Service {
	protected static $_instance = null;

	/**
	 * The Clarifai API
	 * @var Client
	 */
	protected $api = null;

	public static function instance() {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Injects the Clarifai API service
	 * @param Client $api
	 */
	public function set_api(Client $api) {
		$this->api = $api;
	}

	public static function init() {
		// See what our max batch size is
		// Get that many images from repository
		// Request tags
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
	 * Get the number of untagged images in the library
	 * @return int
	 */
	public static function untagged_images_count() {
		$args = array(
			'post_type' => 'attachment',
			'meta_query' => array(
				array(
					'key' => TMT_POST_META_KEY,
					'value' => '',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		$untagged_images = get_posts( $args );

		return count( $untagged_images );
	}
}

Bulk_Tagger_Service::instance();
