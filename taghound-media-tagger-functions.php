<?php

use Taghound_Media_Tagger\Clarifai\Api\Client;

/**
 * Can the tool be enabled?
 *
 * @return bool
 */
function tmt_can_be_enabled() {
	return ! empty( get_option( TMT_SETTING_PREFIX . 'api_key' ) );
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
	return new Client( get_option( TMT_SETTING_PREFIX . 'api_key' ) );
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
