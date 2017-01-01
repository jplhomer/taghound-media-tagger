<?php
namespace Taghound_Media_Tagger\Admin;

use \Taghound_Media_Tagger\Tagger_Service;
use \Taghound_Media_Tagger\Bulk_Tagger_Service;

/**
 * Handle AJAX calls to the admin
 */
class Ajax {
	/**
	 * Singleton container
	 *
	 * @var Ajax
	 */
	protected static $_instance = null;

	/**
	 * Singleton instantiator
	 *
	 * @return Ajax
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Construct function
	 */
	public function __construct() {
		add_action( 'wp_ajax_tmt_bulk_tag', function() {
			$response = array();
			$bulk_tagger = new Bulk_Tagger_Service( tmt_get_cf_client() );

			$args = array();
			$whitelisted_keys = array( 'tagged', 'skip' );
			foreach ( $whitelisted_keys as $key ) {
				if ( ! empty( $_POST[ $key ] ) ) {
					$args[ $key ] = $_POST[ $key ];
				}
			}

			$results = $bulk_tagger->init( $args );

			$response['results'] = $results;

			if ( $results['error'] ) {
				wp_send_json_error( $response );
			} else {
				wp_send_json_success( $response );
			}
		});
	}
}

Ajax::instance();
