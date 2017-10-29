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
	 * Use Clarifai's General model for predictions.
	 *
	 * @var string
	 */
	protected $general_model = 'aaa03c23b3724a16a56b629203edc62c';

	/**
	 * Construct the API
	 *
	 * @param string $api_key API Key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get tags for an image
	 *
	 * @param  string $image_path_or_url File path or URL to image
	 * @param  int	  $post_id			 WP Post ID
	 * @return array              		 Array ( tags => array, doc_id => int )
	 */
	public function get_tags_for_image( $image_path_or_url, $post_id ) {
		$image = array();

		if ( tmt_is_upload_only() ) {
			$image['base64'] = \base64_encode(file_get_contents($image_path_or_url));
		} else {
			$image['url'] = $image_path_or_url;
		}

		$data = array(
			'inputs' => array(
				array(
					'data' => array(
						'image' => $image,
					),
					'id' => (string) $post_id,
				),
			),
		);

		try {
			$results = $this->_make_request( $data );

			return $results->outputs[0];
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
			'endpoint' => 'models',
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
	 * @param array $data              Data
	 *
	 * @throws \Exception 			   If there was an error.
	 * @return object                  JSON-decoded response
	 */
	protected function _make_request( $data ) {
		$url = implode('/', [
			$this->api_base_url,
			$this->api_version,
			'models',
			$this->general_model,
			'outputs',
		]);

		$response = wp_remote_post( $url, array(
			'body' => json_encode($data),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => "Key {$this->api_key}",
			),
		));

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
