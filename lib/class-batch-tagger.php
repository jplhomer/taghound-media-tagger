<?php

namespace Taghound_Media_Tagger;

use Taghound_Media_Tagger\Clarifai\API\Client;

class Batch_Tagger {
	/**
	 * Clarifai API Client
	 * @var Client
	 */
	protected $client;

	public function __construct() {

	}

	/**
	 * Inject the Clarifai API client
	 * @param Client $client
	 */
	public function set_client( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Get the match batch size we should send Clarifai
	 * @return int
	 */
	public function max_batch_size() {
		return null;
	}
}
