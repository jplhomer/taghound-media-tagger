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
	 * @param  string $inputs 				 Inputs formatted as required for consumption
	 * @return array              		 Array ( tags => array )
	 */
	protected function get_tags_for_inputs( $inputs ) {
		$data = array(
			'inputs' => $inputs,
		);

		try {
			$results = $this->_make_request( $data );

			return $results;
		} catch ( \Exception $e ) {
			return $e;
		}
	}

	/**
	 * Get tags for multiple images
	 *
	 * @param  Array $images  Array of image objects
	 * @return Array 			  Tag responses
	 */
	public function get_tags_for_images( $images ) {
		$inputs = array();

		foreach ( $images as $image ) {
			$input = array();

			if ( tmt_is_upload_only() ) {
				$input['base64'] = \base64_encode( file_get_contents( get_attached_file( $image->ID ) ) );
			} else {
				$input['url'] = \wp_get_attachment_image_src( $image->ID, 'large' )[0];
			}

			$inputs[] = array(
				'data' => array(
					'image' => $input,
				),
				'id' => (string) $image->ID,
			);
		}

		return $this->get_tags_for_inputs( $inputs );
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
			'body' => json_encode( $data ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => "Key {$this->api_key}",
			),
		));

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
