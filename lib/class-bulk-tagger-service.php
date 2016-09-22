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

	public static function untagged_images_count() {
		$args = array(
			'post_type' => 'attachment',
			'meta_query' => array(
				array(
					'key' => TMT_POST_META_KEY,
					'value' => '',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		$untagged_images = get_posts( $args );

		return count( $untagged_images );
	}
}

Bulk_Tagger_Service::instance();
