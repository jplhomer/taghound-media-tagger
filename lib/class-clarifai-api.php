<?php
namespace Image_Auto_Tagger;

class Clarifai_API {
	/**
	 * Current API version
	 * @var string
	 */
	protected $api_version = 'v1';

	/**
	 * Base API URL
	 * @var string
	 */
	protected $api_base_url = 'https://api.clarifai.com';

	/**
	 * Client ID
	 * @var string
	 */
	protected $client_id = '';

	/**
	 * Client Secret
	 * @var string
	 */
	protected $client_secret = '';

	public function __construct( $options ) {
		if ( empty($options['client_id']) || empty($options['client_secret']) ) {
			throw new \Exception("Please provide a client_id and client_secret");
		}

		$this->client_id = $options['client_id'];
		$this->client_secret = $options['client_secret'];
	}

	protected function get_api_keys() {
		return array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
		);
	}

	protected function get_auth_token() {
		return get_option(IAT_TOKEN_SETTING);
	}

	protected function renew_auth_token() {
		$keys = $this->get_api_keys();

		$args = array(
			'endpoint' => 'token',
			'post' => array(
				'client_id' => $keys['client_id'],
			    'client_secret' => $keys['client_secret'],
			    'grant_type' => 'client_credentials',
			)
		);

		$results = $this->_make_request( $args, true );

		update_option(IAT_TOKEN_SETTING, $results['access_token']);
	}

	/**
	 * Get tags for an image
	 * @param  string $image_path File path to image
	 * @return array              Array of tags
	 */
	public static function get_tags_for_image( $image_path ) {
		$args = array(
			'endpoint' => 'tag',
			'post' => array(
				'encoded_data' => new CURLFile( $image_path ),
			),
		);

		$results = $this->_make_request( $args );

		$tags = $results['results'][0]['result']['tag']['classes'];

		return $tags;
	}

	/**
	 * Performs the general API request
	 */
	protected function _make_request( $args, $authenticating = false ) {
		if ( ! $authenticating ) {
			$args = wp_parse_args( $args, array(
				'headers' => array(
					"Authorization: Bearer {$this->get_auth_token()}",
				),
			));
		}

		$url = $this->api_base_url . '/' . $this->api_version . '/' . $args['endpoint'] . '/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $args['post']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ( ! empty($args['headers']) ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $args['headers']);
		}

		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode( $result, true );

		return $result;
	}
}
