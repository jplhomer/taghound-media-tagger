<?php
/**
 * Plugin Name: Taghound Media Tagger
 * Version: 1.2.0
 * Description: Automatically adds tags to new images using the Clarifai API.
 * Author: Joshua P. Larson
 * Author URI: http://jplhomer.org
 * Plugin URI: http://jplhomer.org/projects/taghound-media-tagger/
 * Text Domain: taghound-media-tagger
 * Domain Path: /languages
 *
 * @package taghound-media-tagger
 */

namespace Taghound_Media_Tagger;

/**
 * InitializE plugin class
 */
class Taghound_Media_Tagger {
	/**
	 * Singleton container
	 *
	 * @var self
	 */
	protected static $_instance = null;

	/**
	 * Get the instance of the singleton
	 *
	 * @return self  The singleton
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Construct the plugin
	 */
	public function __construct() {
		if ( version_compare( PHP_VERSION, '5.5', '<' ) ) {
		    add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'Taghound Media Tagger requires PHP 5.5 to function properly. Please upgrade PHP or deactivate Taghound Media Tagger.', 'taghound-media-tagger' ) . "</p></div>';" ) );
		    return;
		}

		define( 'TMT_SETTING_PREFIX', 'tmt_' );
		define( 'TMT_TOKEN_SETTING', TMT_SETTING_PREFIX . 'clarifai_token' );
		define( 'TMT_POST_META_KEY', TMT_SETTING_PREFIX . 'clarifai_data' );
		define( 'TMT_TAG_SLUG', 'tmt_tag' );

		include 'taghound-media-tagger-functions.php';
		include 'lib/class-clarifai-api.php';
		include 'lib/class-clarifai-api-usage.php';
		include 'lib/class-valid-image-specification.php';
		include 'lib/class-tagger-service.php';
		include 'lib/class-bulk-tagger-service.php';
		include 'taxonomies/tmt-tag.php';

		if ( is_admin() ) {
			include_once 'lib/class-admin-settings.php';
			include_once 'lib/class-admin-ajax.php';
			include_once ABSPATH . '/wp-admin/includes/meta-boxes.php';
			add_filter( 'attachment_fields_to_edit', array( $this, 'edit_attachment' ), 10, 2 );
			add_filter( 'ajax_query_attachments_args', array( $this, 'handle_attachment_search' ), 10, 1 );
			add_filter( 'attachment_fields_to_save', array( $this, 'save_attachment' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', function( $hook ) {
			global $wp_version;

			$pages = array( 'post.php', 'upload.php', 'settings_page_taghound-settings' );

			if ( ! in_array( $hook, $pages ) ) {
				return;
			}

			$tag_box_script_name = 'tmt-tags-box';
			$tag_box_dependencies = array( 'jquery', 'suggest', 'tags-suggest' );

			if ( version_compare( $wp_version, '4.7', '<' ) ) {
				$tag_box_script_name .= '-deprecated';
				unset( $tag_box_dependencies[2] );
			}

			wp_enqueue_script(
				'tmt-tags-box',
				plugin_dir_url( __FILE__ ) . '/assets/js/' . $tag_box_script_name . '.js',
				$tag_box_dependencies,
				filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/' . $tag_box_script_name . '.js' )
			);

			wp_localize_script( 'tmt-tags-box', 'tagsBoxL10n', array(
				'tagDelimiter' => _x( ',', 'tag delimiter' ),
			));

			wp_enqueue_script(
				'tmt-admin',
				plugin_dir_url( __FILE__ ) . '/assets/js/tmt-admin.js',
				array( 'jquery', 'underscore' ),
				filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/tmt-admin.js' )
			);

			wp_enqueue_style( 'tmt-admin', plugin_dir_url( __FILE__ ) . '/assets/css/tmt-admin.css' );
		});

		if ( TMT_is_enabled() ) {
			add_filter( 'add_attachment', array( $this, 'handle_add_attachment' ), 10, 1 );
		}
	}

	/**
	 * Insert a custom tag editor into the media editor view
	 *
	 * @param  array  $form_fields  Form fields for editing attachments
	 * @param  object $post        The post/attachment
	 * @return array               Modified form fields
	 */
	public function edit_attachment( $form_fields, $post ) {
		$post_id = $post->ID;

		// Only show this field if it's on the upload/media gallery page.
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && 'async-upload' !== $screen->base  ) {
			return $form_fields;
		}

		// Only show if the user hasn't set a custom taxonomy.
		if ( tmt_using_alternate_taxonomy() ) {
			return $form_fields;
		}

		// Prepare arguments for the post categories meta box.
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

		// Remove the default tag editor.
		unset( $form_fields[ TMT_TAG_SLUG ] );

		return $form_fields;
	}

	/**
	 * Handle the wordpress add_attachment filter
	 *
	 * @param int $post_id  WP Post ID for attachment
	 */
	public function handle_add_attachment( $post_id ) {
		if ( ! $this->validate_attachment_for_tagging( $post_id ) ) {
			return $post_id;
		}

		$image_path_or_url = tmt_get_image_path_or_url( $post_id );
		$tagger = new Tagger_Service( tmt_get_cf_client() );
		$tagger->tag_single_image( $image_path_or_url, $post_id );

		return $post_id;
	}

	/**
	 * Handle the admin search for attachments in the media gallery
	 *
	 * @param  array $query  Original query
	 * @return array         Modified query
	 */
	function handle_attachment_search( $query ) {
		// Do not intercept search if using custom taxonomy.
		if ( tmt_using_alternate_taxonomy() ) {
			return $query;
		}

		if ( ! empty( $query['s'] ) ) {
			// Get tag terms matching the search.
			$term_results = get_terms( array(
				'taxonomy' => TMT_TAG_SLUG,
				'name__like' => $query['s'],
				'fields' => 'ids',
				'hide_empty' => false,
			));

			if ( ! empty( $term_results ) ) {
				// Run the query as-is to get keyword matches.
				$keyword_results = new \WP_Query( $query );
				$keyword_post_ids = array_map( function( $p ) {
					return $p->ID;
				}, $keyword_results->posts );

				// Run the query again without a keyword search but with a tax query.
				$tag_query = $query;
				unset( $tag_query['s'] );
				$tag_query['tax_query'] = array(
					array(
						'taxonomy' => TMT_TAG_SLUG,
						'terms' => $term_results,
					),
				);
				$tag_results = new \WP_Query( $tag_query );
				$tag_post_ids = array_map( function( $p ) {
					return $p->ID;
				}, $tag_results->posts );

				// Combine the two.
				$post_ids = array_merge( (array) $keyword_post_ids, (array) $tag_post_ids );
				array_unique( $post_ids );

				// Send back a dummy query with just explicit post IDs.
				unset( $query['s'] );
				$query['post__in'] = $post_ids;
			}
		}

		return $query;
	}

	/**
	 * Save tags when an attachment is saved. We do this custom because we use a
	 * custom UI for tags (not a simple text box)
	 *
	 * @param  object $post            WP Attachment Post
	 * @param  array  $attachment_data  Attachment-specific data (unused)
	 * @return WP_Post the Post attachment
	 */
	public function save_attachment( $post, $attachment_data ) {
		$post_id = $post['ID'];
		$slug = TMT_TAG_SLUG;

		// Grab the terms from the original request.
		$terms = $_POST['tax_input'][ TMT_TAG_SLUG ];

		// Make them integers intead of strings.
		$comma = _x( ',', 'tag delimiter' );
		$terms = explode( $comma, $terms );

		// @TODO Get tags currently saved for this attachment
		// @TODO Compare two lists and find which tags wre added/removed
		// @TODO See which of the removed tags were originally from Clarifai
		// @TODO Send feedback to Clarifai for this attachment's document ID
		// Update the tags on this attachment
		wp_set_object_terms( $post_id, array_filter( $terms ), TMT_TAG_SLUG );

		if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			// Send post data back to the edit_post() function without our data (since we already saved it)
			// for non-AJAX calls.
			unset( $post['tax_input'][ TMT_TAG_SLUG ] );
		}

		return $post;
	}

	/**
	 * Validate an attachment for tagging
	 *
	 * @param  int $post_id WP Post ID
	 * @return bool
	 */
	public function validate_attachment_for_tagging( $post_id ) {
		$attachment = get_post( $post_id );
		$valid_images = new Valid_Image_Specification;

		return $valid_images->is_satisfied_by( $attachment );
	}
}

Taghound_Media_Tagger::instance();
