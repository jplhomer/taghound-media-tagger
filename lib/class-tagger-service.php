<?php

namespace Taghound_Media_Tagger;

use \Taghound_Media_Tagger\Clarifai\API\Client;

class Tagger_Service {
	/**
	 * Get Clarifai API client
	 * @return Client
	 */
	public static function get_cf_client() {
		return new Client( array(
			'client_id' => get_option( TMT_SETTING_PREFIX . 'client_id' ),
			'client_secret' => get_option( TMT_SETTING_PREFIX . 'client_secret' ),
		));
	}
}

new Tagger_Service();
