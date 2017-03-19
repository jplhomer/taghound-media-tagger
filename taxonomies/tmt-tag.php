<?php
namespace Taghound_Media_Tagger;

/**
 * Initialize taxonomy registration
 */
function tmt_tag_init() {
	register_taxonomy( TMT_TAG_SLUG, array( 'attachment' ), array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => false,
		'show_ui'           => ! tmt_using_alternate_taxonomy(),
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts',
		),
		'labels'            => array(
			'name'                       => __( 'Tags', 'taghound-media-tagger' ),
			'singular_name'              => _x( 'Tag', 'taxonomy general name', 'taghound-media-tagger' ),
			'search_items'               => __( 'Search Tags', 'taghound-media-tagger' ),
			'popular_items'              => __( 'Popular Tags', 'taghound-media-tagger' ),
			'all_items'                  => __( 'All Tags', 'taghound-media-tagger' ),
			'parent_item'                => __( 'Parent Tag', 'taghound-media-tagger' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'taghound-media-tagger' ),
			'edit_item'                  => __( 'Edit Tag', 'taghound-media-tagger' ),
			'update_item'                => __( 'Update Tag', 'taghound-media-tagger' ),
			'add_new_item'               => __( 'New Tag', 'taghound-media-tagger' ),
			'new_item_name'              => __( 'New Tag', 'taghound-media-tagger' ),
			'separate_items_with_commas' => __( 'Tags separated by comma', 'taghound-media-tagger' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'taghound-media-tagger' ),
			'choose_from_most_used'      => __( 'Choose from the most used Tags', 'taghound-media-tagger' ),
			'not_found'                  => __( 'No Tags found.', 'taghound-media-tagger' ),
			'menu_name'                  => __( 'Tags', 'taghound-media-tagger' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'TMT_tag',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'Taghound_Media_Tagger\TMT_tag_init' );
