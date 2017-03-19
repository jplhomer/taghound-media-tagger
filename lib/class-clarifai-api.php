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
	protected $api_version = 'v1';

	/**
	 * Base API URL
	 *
	 * @var string
	 */
	protected $api_base_url = 'https://api.clarifai.com';

	/**
	 * Client ID
	 *
	 * @var string
	 */
	protected $client_id = '';

	/**
	 * Client Secret
	 *
	 * @var string
	 */
	protected $client_secret = '';

	/**
	 * Construct the API
	 *
	 * @param array $options Options
	 * @throws \Exception 	If missing options.
	 */
	public function __construct( $options ) {
		if ( empty( $options['client_id'] ) || empty( $options['client_secret'] ) ) {
			throw new \Exception( 'Please provide a client_id and client_secret' );
		}

		$this->client_id = $options['client_id'];
		$this->client_secret = $options['client_secret'];
	}

	/**
	 * Determines if a token object is expired using a custom value we calculate
	 *
	 * @param  array $token  Token with 'expiration_date'
	 * @return boolean
	 */
	protected function is_token_expired( $token ) {
		return time() > $token['expiration_date'];
	}

	/**
	 * Gets the API keys set on the object
	 *
	 * @return array
	 */
	protected function get_api_keys() {
		return array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
		);
	}

	/**
	 * Gets the auth token. Generates ones if it's invalid or expired.
	 *
	 * @return array Token object
	 */
	protected function get_auth_token() {
		$token = get_option( TMT_TOKEN_SETTING );

		if ( ! $token || empty( $token['access_token'] ) || $this->is_token_expired( $token ) ) {
			return $this->renew_auth_token();
		}

		return $token;
	}

	/**
	 * Get a new auth token
	 *
	 * @return array Auth token array
	 */
	protected function renew_auth_token() {
		$keys = $this->get_api_keys();

		$args = array(
			'endpoint' => 'token',
			'post' => array(
				'client_id' => $keys['client_id'],
			    'client_secret' => $keys['client_secret'],
			    'grant_type' => 'client_credentials',
			),
		);

		$results = $this->_make_request( $args, true );

		// Calculate the expiration date of this token.
		$results['expiration_date'] = time() + $results['expires_in'];

		update_option( TMT_TOKEN_SETTING, $results );

		return $results;
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
	 * Get usage data for a user's account
	 *
	 * @return Usage object or Exception
	 */
	public function get_usage_data() {
		$args = array(
			'endpoint' => 'usage',
		);

		try {
			$results = $this->_make_request( $args );

			return new Usage( $results );
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
			$token = $this->get_auth_token();
			$args = wp_parse_args( $args, array(
				'headers' => array(
					"Authorization: Bearer {$token['access_token']}",
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
