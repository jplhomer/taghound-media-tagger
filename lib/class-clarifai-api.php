<?php
namespace Taghound_Media_Tagger\Clarifai\API;

/**
 * Clarifai API Client
 */
class Client {
	/**
	 * Current API version
	 *
	 * @var string
	 */
	protected $api_version = 'v2';

	/**
	 * Base API URL
	 *
	 * @var string
	 */
	protected $api_base_url = 'https://api.clarifai.com';

	/**
	 * API Key
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Construct the API
	 *
	 * @param string $api_key API Key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get info about max batch size, etc from the info endpoint
	 *
	 * @return array  Info set from Clarifai
	 */
	public function get_info() {
		$args = array(
			'endpoint' => 'info',
		);

		try {
			$results = $this->_make_request( $args );

			return $results['results'];
		} catch ( \Exception $e ) {
			return $e;
		}
	}

	/**
	 * Get tags for an image
	 *
	 * @param  string $image_path_or_url File path or URL to image
	 * @param  int	  $post_id			 WP Post ID
	 * @return array              		 Array ( tags => array, doc_id => int )
	 */
	public function get_tags_for_image( $image_path_or_url, $post_id ) {
		$args = array(
			'endpoint' => 'tag',
		);

		if ( tmt_is_upload_only() ) {
			$args['post'] = array(
				'encoded_data' => new \CURLFile( $image_path_or_url ),
				'local_id' => $post_id,
			);
		} else {
			$args['post'] = array(
				'url' => $image_path_or_url,
				'local_id' => $post_id,
			);
		}

		try {
			$results = $this->_make_request( $args );

			return $results['results'][0];
		} catch ( \Exception $e ) {
			return $e;
		}
	}

	/**
	 * Get tags for multiple images
	 *
	 * @param  Array $image_urls  Array of image URLs
	 * @return Array 			  Tag responses
	 */
	public function get_tags_for_images( $image_urls ) {
		$args = array(
			'endpoint' => 'tag',
		);

		$image_url_string = '';
		foreach ( $image_urls as $id => $url ) {
			$image_url_string .= 'url=' . $url . '&local_id=' . $id . '&';
		}

		$args['post'] = $image_url_string;

		try {
			$results = $this->_make_request( $args );

			return $results;
		} catch ( \Exception $e ) {
			return $e;
		}
	}

	/**
	 * Performs the general API request
	 *
	 * @param  array   $args           Arguments
	 * @param  boolean $authenticating Whether this is the token exchange request
	 *
	 * @throws \Exception 			   If there was an error.
	 * @return object                  JSON-decoded response
	 */
	protected function _make_request( $args, $authenticating = false ) {
		$is_post = ! empty( $args['post'] );

		if ( ! $authenticating ) {
			$args = wp_parse_args( $args, array(
				'headers' => array(
					"Authorization: {$this->api_key}",
				),
			));
		}

		$url = $this->api_base_url . '/' . $this->api_version . '/' . $args['endpoint'] . '/';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		if ( $is_post ) {
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $args['post'] );
		}

		if ( ! empty( $args['headers'] ) ) {
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $args['headers'] );
		}

		$result = curl_exec( $ch );

		if ( ! $result ) {
			throw new \Exception( curl_error( $ch ) );
		}

		curl_close( $ch );

		return json_decode( $result, true );
	}
}
