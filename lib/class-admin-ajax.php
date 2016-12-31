<?php

namespace Taghound_Media_Tagger\Admin;

use \Taghound_Media_Tagger\Tagger_Service;
use \Taghound_Media_Tagger\Bulk_Tagger_Service;

class Ajax {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_tmt_bulk_tag', function() {
			$response = array();
			$bulk_tagger = new Bulk_Tagger_Service( tmt_get_cf_client() );
			$results = $bulk_tagger->init();

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
