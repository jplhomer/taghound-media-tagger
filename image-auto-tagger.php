<?php
/**
 * Plugin Name: Image Auto Tagger
 * Version: 0.1-alpha
 * Description: Automatically adds tags to new images using the Clarifai API.
 * Author: Joshua P. Larson
 * Author URI: http://jplhomer.org
 * Plugin URI: http://jplhomer.org
 * Text Domain: image-auto-tagger
 * Domain Path: /languages
 * @package Image-auto-tagger
 */

namespace Image_Auto_Tagger;

include 'lib/class-clarifai-api.php';

define('IAT_TOKEN_SETTING', 'iat_clarifai_token');

// include 'taxonomies/image_tag.php';

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

function parse_api_tag_results( $results ) {

}
