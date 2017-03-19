<?php

use Taghound_Media_Tagger\Clarifai\Api\Client;

/**
 * Can the tool be enabled?
 *
 * @return bool
 */
function tmt_can_be_enabled() {
	$can_be_enabled = false;
	$client_id = get_option( TMT_SETTING_PREFIX . 'client_id' );
	$client_secret = get_option( TMT_SETTING_PREFIX . 'client_secret' );

	if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
		$can_be_enabled = true;
	}

	return $can_be_enabled;
}

/**
 * Is the tool enabled in the settings?
 *
 * @return boolean
 */
function tmt_is_enabled() {
	$is_enabled = false;
	$enabled_setting = get_option( TMT_SETTING_PREFIX . 'enabled' );

	if ( tmt_can_be_enabled() && $enabled_setting ) {
		$is_enabled = true;
	}

	return $is_enabled;
}

/**
 * Is the user's site behind a firewall or on a dev environment?
 *
 * @return boolean
 */
function tmt_is_upload_only() {
	return ! ! get_option( TMT_SETTING_PREFIX . 'upload_only' );
}

/**
 * Gets an API client
 *
 * @return Client  Clarifai API client
 */
function tmt_get_cf_client() {
	return new Client( array(
		'client_id' => get_option( TMT_SETTING_PREFIX . 'client_id' ),
		'client_secret' => get_option( TMT_SETTING_PREFIX . 'client_secret' ),
	));
}

/**
 * Returns the image path or URL based on the upload_only setting
 *
 * @param  int $post_id    WP Post ID
 *
 * @return string
 */
function tmt_get_image_path_or_url( $post_id ) {
	if ( tmt_is_upload_only() ) {
		$image_path_or_url = get_attached_file( $post_id );
	} else {
		$attachment = wp_get_attachment_image_src( $post_id, 'large' );
		$image_path_or_url = $attachment[0];
	}

	return $image_path_or_url;
}

/**
 * Get the tag taxonomy
 *
 * @return string
 */
function tmt_get_tag_taxonomy() {
	return apply_filters( 'tmt_tag_taxonomy', TMT_TAG_SLUG );
}

/**
 * Check if the user is using an alternate taxonomy
 *
 * @return bool
 */
function tmt_using_alternate_taxonomy() {
	return tmt_get_tag_taxonomy() != TMT_TAG_SLUG;
}
