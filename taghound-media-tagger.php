<?php
/**
 * Plugin Name: Taghound Media Tagger
 * Version: 1.0
 * Description: Automatically adds tags to new images using the Clarifai API.
 * Author: Joshua P. Larson
 * Author URI: http://jplhomer.org
 * Plugin URI: http://jplhomer.org/projects/taghound-media-tagger/
 * Text Domain: taghound-media-tagger
 * Domain Path: /languages
 * @package taghound-media-tagger
 */

namespace Taghound_Media_Tagger;

class Taghound_Media_Tagger {
	protected static $_instance = null;

	protected static $valid_attachment_mime_types = array( 'image/jpeg', 'image/png', 'image/gif' );

	public static function instance() {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		define('TMT_SETTING_PREFIX', 'tmt_');
		define('TMT_TOKEN_SETTING', 'tmt_clarifai_token');
		define('TMT_POST_META_KEY', 'tmt_clarifai_data');
		define('TMT_TAG_SLUG', 'tmt_tag');

		include 'taghound-media-tagger-functions.php';
		include 'lib/class-clarifai-api.php';
		include 'taxonomies/tmt_tag.php';

		if ( is_admin() ) {
			include_once 'lib/class-admin-settings.php';
			include_once ABSPATH . '/wp-admin/includes/meta-boxes.php';
			add_filter( 'attachment_fields_to_edit', array( $this, 'edit_attachment' ), 10, 2 );
			add_filter( 'ajax_query_attachments_args', array( $this, 'handle_attachment_search' ), 10, 1 );
			add_filter( 'attachment_fields_to_save', array( $this, 'save_attachment' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', function( $hook ) {
			$pages = array('post.php', 'upload.php');

			if ( ! in_array( $hook, $pages ) ) {
				return;
			}

			wp_enqueue_script( 'tmt-tags-box', plugin_dir_url( __FILE__ ) . '/assets/js/tmt-tags-box.js', array('jquery', 'suggest') );
			wp_localize_script( 'tmt-tags-box', 'tagsBoxL10n', array(
				'tagDelimiter' => _x( ',', 'tag delimiter' ),
			));
			wp_enqueue_script( 'tmt-admin', plugin_dir_url( __FILE__ ) . '/assets/js/tmt-admin.js', array('jquery', 'underscore') );

			wp_enqueue_style( 'tmt-admin', plugin_dir_url( __FILE__ ) . '/assets/css/tmt-admin.css' );
		});

		if ( TMT_is_enabled() ) {
			add_filter( 'add_attachment', array( $this, 'handle_add_attachment' ), 10, 1 );
		}
	}

	/**
	 * Insert a custom tag editor into the media editor view
	 * @param  array $form_fields  Form fields for editing attachments
	 * @param  object $post        The post/attachment
	 * @return array               Modified form fields
	 */
	public function edit_attachment( $form_fields, $post ) {
		$post_id = $post->ID;

		// Only show this field if it's on the upload/media gallery page
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && $screen->base !== 'async-upload' ) {
			return $form_fields;
		}

		// Prepare arguments for the post categories meta box
		$args = array(
			'args' => array(
				'taxonomy' => TMT_TAG_SLUG,
			),
		);

		ob_start();

		echo '<label>Image Tags</label>';
		post_tags_meta_box( $post, $args );

		$tag_editor = ob_get_clean();

		$html = '';
		$html .= '</td></tr><tr class="tmt-tag-row"><td colspan="2">';
		$html .= $tag_editor;
		$html .= '</td></tr>';

		$form_fields['tmt-tag-editor'] = array(
			'label' => 'Tags',
			'input' => 'html',
			'html' => $html,
		);

		// Remove the default tag editor
		unset( $form_fields[ TMT_TAG_SLUG ] );

		return $form_fields;
	}

	/**
	 * Gets the Clarifai API client for usage
	 * @return Clarifai_API Clarifai API Client
	 */
	public function get_cf_client() {
		$cf = new Clarifai_API( array(
			'client_id' => get_option( TMT_SETTING_PREFIX . 'client_id' ),
			'client_secret' => get_option( TMT_SETTING_PREFIX . 'client_secret' ),
		));

		return $cf;
	}

	/**
	 * Handle the wordpress add_attachment filter
	 * @param int $post_id  WP Post ID for attachment
	 * @param object $cf    (optional) Pass in the API class. Used only for testing.
	 */
	public function handle_add_attachment( $post_id, $cf = null ) {
		// Validate
		if ( ! $this->validate_attachment_for_tagging( $post_id ) ) {
			return $post_id;
		}

		$image_path = get_attached_file( $post_id );

		if ( is_null( $cf ) ) {
			$cf = $this->get_cf_client();
		}

		$tags = $cf->get_tags_for_image( $image_path );

		if ( !$tags ) {
			return $post_id;
		}

		// Store the terms as tags
		wp_set_object_terms( $post_id, $tags['tags'], TMT_TAG_SLUG );

		// Store tag data along with the image
		update_post_meta( $post_id, TMT_POST_META_KEY, $tags );

		return $post_id;
	}

	/**
	 * Handle the admin search for attachments in the media gallery
	 * @param  array $query  Original query
	 * @return array         Modified query
	 */
	function handle_attachment_search( $query ) {
		if ( !empty( $query['s'] ) ) {
			// Get tag terms matching the search
			$term_results = get_terms( array(
				'taxonomy' => TMT_TAG_SLUG,
				'name__like' => $query['s'],
				'fields' => 'ids',
				'hide_empty' => false,
			));

			if ( !empty( $term_results ) ) {
				// Run the query as-is to get keyword matches
				$keyword_results = new \WP_Query( $query );
				$keyword_post_ids = array_map( function($p) {
					return $p->ID;
				}, $keyword_results->posts );

				// Run the query again without a keyword search but with a tax query
				$tag_query = $query;
				unset($tag_query['s']);
				$tag_query['tax_query'] = array(
					array(
						'taxonomy' => TMT_TAG_SLUG,
						'terms' => $term_results,
					)
				);
				$tag_results = new \WP_Query( $tag_query );
				$tag_post_ids = array_map( function($p) {
					return $p->ID;
				}, $tag_results->posts );

				// Combine the two
				$post_ids = array_merge( (array) $keyword_post_ids, (array) $tag_post_ids);
				array_unique( $post_ids );

				// Send back a dummy query with just explicit post IDs
				unset($query['s']);
				$query['post__in'] = $post_ids;
			}
		}

		return $query;
	}

	/**
	 * Save tags when an attachment is saved. We do this custom because we use a
	 * custom UI for tags (not a simple text box)
	 * @param  object $post            WP Attachment Post
	 * @param  array $attachment_data  Attachment-specific data (unused)
	 * @return void
	 */
	public function save_attachment( $post, $attachment_data ) {
		$post_id = $post['ID'];
		$slug = TMT_TAG_SLUG;

		// Grab the terms from the original request
		$terms = $_POST[ 'tax_input' ][ TMT_TAG_SLUG ];

		// Make them integers intead of strings
		// $terms = array_map( function($a) {
		// 	return (int) $a;
		// }, $terms);
		$comma = _x( ',', 'tag delimiter' );
		$terms = explode( $comma, $terms );

		// @TODO Get tags currently saved for this attachment
		// @TODO Compare two lists and find which tags wre added/removed
		// @TODO See which of the removed tags were originally from Clarifai
		// @TODO Send feedback to Clarifai for this attachment's document ID

		// Update the tags on this attachment
		wp_set_object_terms( $post_id, array_filter( $terms ), TMT_TAG_SLUG );

		if ( ! ( defined('DOING_AJAX') && DOING_AJAX ) ) {
			// Send post data back to the edit_post() function without our data (since we already saved it)
			// for non-AJAX calls
			unset( $post[ 'tax_input' ][ TMT_TAG_SLUG ] );
		}

		return $post;
	}

	/**
	 * Validate an attachment for tagging
	 * @param  int $post_id WP Post ID
	 * @return bool
	 */
	public static function validate_attachment_for_tagging( $post_id ) {
		$attachment = get_post( $post_id );

		return in_array( $attachment->post_mime_type, self::$valid_attachment_mime_types );
	}
}

Taghound_Media_Tagger::instance();
