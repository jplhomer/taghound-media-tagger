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
	 *
	 * @return array                     Results from Clarifai
	 */
	public function tag_images( $images ) {
		$results = $this->api->get_tags_for_images( $images );

		return $this->store_tag_info( $results );
	}

	/**
	 * Persist Clarifai tag data
	 *
	 * @param  array $results 	Clarifai Tag result set
	 * @return array      			Tags
	 */
	public function store_tag_info( $results ) {
		foreach ($results->outputs as $output) {
			$post_id = (int) $output->input->id;

			if ($output->status->description != 'Ok') {
				continue;
			}

			$tags = array_map(function($concept) {
				return $concept->name;
			}, $output->data->concepts);

			wp_set_object_terms( $post_id, $tags, tmt_get_tag_taxonomy() );
			update_post_meta( $post_id, TMT_POST_META_KEY, $output );
		}

		return $results;
	}
}
