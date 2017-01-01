<?php

use Taghound_Media_Tagger\Clarifai\API\Usage;

class UsageTest extends WP_UnitTestCase {
	/**
	 * Example of an unthrottled response
	 *
	 * @var array
	 */
	protected $no_throttle = array(
		'results' => array(
			'user_throttles' => array(
				array(
					'name' => 'monthly',
					'consumed' => 0,
					'consumed_percentage' => 0,
					'limit' => 5000,
					'units' => 'per month',
					'wait' => 42,
				),
				array(
					'name' => 'hourly',
					'consumed' => 0,
					'consumed_percentage' => 0,
					'limit' => 1000,
					'units' => 'per hour',
					'wait' => 42,
				),
			),
		),
	);

	/**
	 * Example where user hits monthly throttle
	 *
	 * @var array
	 */
	protected $monthly_throttle = array(
		'results' => array(
			'user_throttles' => array(
				array(
					'name' => 'hourly',
					'consumed' => 0,
					'consumed_percentage' => 0,
					'limit' => 1000,
					'units' => 'per hour',
					'wait' => 42,
				),
				array(
					'name' => 'monthly',
					'consumed' => 5000,
					'consumed_percentage' => 1,
					'limit' => 5000,
					'units' => 'per month',
					'wait' => 42,
				),
			),
		),
	);

	/**
	 * Example where user hits hourly throttle
	 *
	 * @var array
	 */
	protected $hourly_throttle = array(
		'results' => array(
			'user_throttles' => array(
				array(
					'name' => 'hourly',
					'consumed' => 1000,
					'consumed_percentage' => 1,
					'limit' => 1000,
					'units' => 'per hour',
					'wait' => 42,
				),
				array(
					'name' => 'monthly',
					'consumed' => 1000,
					'consumed_percentage' => 0.2,
					'limit' => 5000,
					'units' => 'per month',
					'wait' => 42,
				),
			),
		),
	);

	function test_know_when_throttled() {
		$no_usage = new Usage( $this->no_throttle );
		$this->assertFalse( $no_usage->is_throttled(), 'No throttles' );

		$monthly_usage = new Usage( $this->monthly_throttle );
		$this->assertTrue( $monthly_usage->is_throttled(), 'Monthly throttle' );

		$hourly_usage = new Usage( $this->hourly_throttle );
		$this->assertTrue( $hourly_usage->is_throttled(), 'Hourly throttle' );
	}

	function test_usage_assigned_properly() {
		$usage = new Usage( $this->no_throttle );
		$this->assertEquals( 'monthly', $usage->monthly['name'] );
		$this->assertEquals( 'hourly', $usage->hourly['name'] );

		$other_usage = new Usage( $this->hourly_throttle );
		$this->assertEquals( 'monthly', $other_usage->monthly['name'] );
		$this->assertEquals( 'hourly', $other_usage->hourly['name'] );
	}
}
