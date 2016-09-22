<?php

namespace Taghound_Media_Tagger\Admin;

use \Taghound_Media_Tagger\Bulk_Tagger_Service;

class Ajax {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_tmt_bulk_tag', function() {
			$response = array();
			$response['message'] = Bulk_Tagger_Service::init();

			wp_send_json_success( $response );
		});
	}
}

Ajax::instance();
