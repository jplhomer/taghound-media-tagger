<?php
namespace Taghound_Media_Tagger\Clarifai\API;

/**
 * Class to manage the Clarifai API usage for the given user
 */
class Usage {
	/**
	 * Hourly usage
	 *
	 * @var array
	 */
	public $hourly = array();

	/**
	 * Monthly usage
	 *
	 * @var array
	 */
	public $montly = array();

	/**
	 * The original API response
	 *
	 * @var array
	 */
	protected $response = array();

	/**
	 * Example API response:
	 *
	 * @param array $response Response from the API to interperet
	 *
	 * @see https://developer.clarifai.com/guide/usage#usage
	 *
	 * {
	 *  "status_code": "OK",
	 *  "status_msg": "All images in request have completed successfully. ",
	 *  "results": {
	 *    "user_throttles": [
	 * 	 {
	 * 	   "name": "hourly",
	 * 	   "consumed": 0,
	 * 	   "consumed_percentage": 0,
	 * 	   "limit": 1000,
	 * 	   "units": "per hour",
	 * 	   "wait": 3.396084081
	 * 	 },
	 * 	 {
	 * 	   "name": "monthly",
	 * 	   "consumed": 2,
	 * 	   "consumed_percentage": 0,
	 * 	   "limit": 5000,
	 * 	   "units": "per month",
	 * 	   "wait": 452.3001357252901
	 * 	 }
	 *    ],
	 *    "app_throttles": {}
	 *  }
	 * }
	 */
	public function __construct( array $response ) {
		$this->response = $response;

		$this->hourly = $this->get_throttle_set( 'hourly' );
		$this->monthly = $this->get_throttle_set( 'monthly' );
	}

	/**
	 * Get a throttle set from the API result
	 *
	 * @param String $name Throttle set name monthly|hourly
	 * @return Array 	   Throttle set or empty array
	 */
	protected function get_throttle_set( $name ) {
		$throttle_set = array();

		$filtered = array_filter( $this->response['results']['user_throttles'], function( $a ) use ( $name ) {
			return $a['name'] === $name;
		});

		if ( count( $filtered ) === 1 ) {
			$throttle_set = array_shift( $filtered );
		}

		return $throttle_set;
	}

	/**
	 * See if the user is throttled from making requests
	 *
	 * @return boolean
	 */
	public function is_throttled() {
		return $this->hourly['consumed_percentage'] >= 1 || $this->monthly['consumed_percentage'] >= 1;
	}
}
