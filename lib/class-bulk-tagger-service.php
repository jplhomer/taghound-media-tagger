<?php

namespace Taghound_Media_Tagger;

class Bulk_Tagger_Service {
	protected static $_instance = null;

	public static function instance() {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Bulk_Tagger_Service::instance();
