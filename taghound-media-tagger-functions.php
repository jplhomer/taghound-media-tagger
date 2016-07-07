<?php

/**
 * Can the tool be enabled?
 * @return bool
 */
function tmt_can_be_enabled() {
	$can_be_enabled = false;
	$client_id = get_option(TMT_SETTING_PREFIX . 'client_id');
	$client_secret = get_option(TMT_SETTING_PREFIX . 'client_secret');

	if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
		$can_be_enabled = true;
	}

	return $can_be_enabled;
}

/**
 * Is the tool enabled in the settings?
 * @return boolean
 */
function tmt_is_enabled() {
	$is_enabled = false;
	$enabled_setting = get_option(TMT_SETTING_PREFIX . 'enabled');

	if ( tmt_can_be_enabled() && $enabled_setting ) {
		$is_enabled = true;
	}

	return $is_enabled;
}

/**
 * Is the user's site behind a firewall or on a dev environment?
 * @return boolean
 */
function tmt_is_upload_only() {
	return !!get_option(TMT_SETTING_PREFIX . 'upload_only');
}
