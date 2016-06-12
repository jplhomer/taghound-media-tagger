<?php

function image_tag_init() {
	register_taxonomy( 'image_tag', array( 'attachment' ), array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Tags', 'image-auto-tagger' ),
			'singular_name'              => _x( 'Tag', 'taxonomy general name', 'image-auto-tagger' ),
			'search_items'               => __( 'Search Tags', 'image-auto-tagger' ),
			'popular_items'              => __( 'Popular Tags', 'image-auto-tagger' ),
			'all_items'                  => __( 'All Tags', 'image-auto-tagger' ),
			'parent_item'                => __( 'Parent Tag', 'image-auto-tagger' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'image-auto-tagger' ),
			'edit_item'                  => __( 'Edit Tag', 'image-auto-tagger' ),
			'update_item'                => __( 'Update Tag', 'image-auto-tagger' ),
			'add_new_item'               => __( 'New Tag', 'image-auto-tagger' ),
			'new_item_name'              => __( 'New Tag', 'image-auto-tagger' ),
			'separate_items_with_commas' => __( 'Tags separated by comma', 'image-auto-tagger' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'image-auto-tagger' ),
			'choose_from_most_used'      => __( 'Choose from the most used Tags', 'image-auto-tagger' ),
			'not_found'                  => __( 'No Tags found.', 'image-auto-tagger' ),
			'menu_name'                  => __( 'Tags', 'image-auto-tagger' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'image_tag',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'image_tag_init' );
