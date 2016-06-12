<?php
/**
 * Plugin Name: Image Auto Description
 * Version: 0.1-alpha
 * Description: Automatically adds a description to new images using the Clarifai API.
 * Author: Joshua P. Larson
 * Author URI: http://jplhomer.org
 * Plugin URI: http://jplhomer.org
 * Text Domain: image-auto-description
 * Domain Path: /languages
 * @package Image-auto-description
 */

include 'taxonomies/image_tag.php';

add_filter('add_attachment', function( $post_id ) {
	// Get the image path
	$image_path = get_attached_file( $post_id );

	$tags = get_tags_from_api( $image_path );

	if ( !$tags ) {
		return;
	}

	$tag_text = implode( $tags, ", " );

	$post_data = array(
		'ID' => $post_id,
		'post_content' => $tag_text,
	);

	wp_update_post( $post_data );
});

function get_tags_from_api( $local_file_path ) {
	$post = array(
		'encoded_data' => new CURLFile( $local_file_path ),
	);

	$headers = array(
		'Authorization: Bearer <TOKEN>',
	);

	$target_url = 'https://api.clarifai.com/v1/tag/';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($ch);
	curl_close ($ch);

	return parse_api_tag_results( $result );
}

function parse_api_tag_results( $results ) {
	$results = json_decode( $results, true );

	if ( $results['status_code'] != 'OK' ) {
		return false;
	}

	$tags = $results['results'][0]['result']['tag']['classes'];

	return $tags;
}
