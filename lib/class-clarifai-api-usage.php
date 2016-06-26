<?php
namespace Taghound_Media_Tagger\Clarifai\API;

class Usage {
	/**
	 * Hourly usage
	 * @var array
	 */
	public $hourly = array();

	/**
	 * Monthly usage
	 * @var array
	 */
	public $montly = array();

	/**
	 * The original API response
	 * @var array
	 */
	protected $response = array();

	/**
	 * Example API response:
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
	public function __construct( Array $response ) {
		$this->response = $response;

		$this->hourly = $response['results']['user_throttles'][0];
		$this->monthly = $response['results']['user_throttles'][1];
	}

	/**
	 * See if the user is throttled from making requests
	 * @return boolean
	 */
	public function is_throttled() {
		return $this->hourly['consumed_percentage'] >= 1 || $this->monthly['consumed_percentage'] >= 1;
	}
}
